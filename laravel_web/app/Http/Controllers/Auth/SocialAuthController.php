<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DriverProfile;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google's OAuth 2.0 Consent Screen
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = \App\Services\SettingService::get('oauth.google_client_id') ?: config('services.google.client_id');
        $redirectUri = \App\Services\SettingService::get('oauth.google_redirect_uri') ?: config('services.google.redirect', url('/auth/google/callback'));

        if (empty($clientId)) {
            return redirect('/login')->with('error', 'Google Sign-In is currently being initialized. Please configure GOOGLE_CLIENT_ID in your settings.');
        }

        $state = Str::random(40);
        session([
            'oauth_google_state' => $state,
            'oauth_intended_role' => $request->query('role', 'customer'),
        ]);

        $queryParams = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $queryParams);
    }

    /**
     * Handle Google's OAuth Callback
     */
    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            Log::warning('Google OAuth cancelled or error', ['error' => $request->get('error')]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Google Sign-In was cancelled or failed.'], 400);
            }
            return redirect('/login')->with('error', 'Google Sign-In was cancelled or failed.');
        }

        $credential = $request->input('credential');
        $code = $request->input('code');

        if (empty($credential) && empty($code)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'No authorization code or credential received from Google.'], 400);
            }
            return redirect('/login')->with('error', 'No authorization code received from Google.');
        }

        $clientId = \App\Services\SettingService::get('oauth.google_client_id') ?: config('services.google.client_id');
        $clientSecret = \App\Services\SettingService::get('oauth.google_client_secret') ?: config('services.google.client_secret');

        try {
            $googleId = null;
            $email = null;
            $name = 'Google User';
            $avatar = null;

            // Flow 1: Google Identity Services (GIS) JWT ID-Token
            if (!empty($credential)) {
                $verifyResp = Http::timeout(15)->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $credential,
                ]);

                if ($verifyResp->failed()) {
                    Log::error('Google ID token verification failed', ['body' => $verifyResp->body()]);
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Failed to verify Google credential.'], 400);
                    }
                    return redirect('/login')->with('error', 'Failed to verify Google credential.');
                }

                $googleUser = $verifyResp->json();
                $googleId = $googleUser['sub'] ?? null;
                $email = strtolower(trim($googleUser['email'] ?? ''));
                $name = $googleUser['name'] ?? ($googleUser['given_name'] ?? 'Google User');
                $avatar = $googleUser['picture'] ?? null;
            }
            // Flow 2: Authorization code exchange (supports both popup 'postmessage' and standard redirect URI)
            else if (!empty($code)) {
                $redirectUri = $request->input('redirect_uri');
                if (empty($redirectUri)) {
                    $redirectUri = ($request->isMethod('post') && $request->has('code'))
                        ? 'postmessage'
                        : config('services.google.redirect', url('/auth/google/callback'));
                }

                $tokenResp = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                ]);

                // Fallback attempt with postmessage if standard redirectUri failed on POST
                if ($tokenResp->failed() && $redirectUri !== 'postmessage') {
                    $tokenResp = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'code' => $code,
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => 'postmessage',
                    ]);
                }

                if ($tokenResp->failed()) {
                    Log::error('Google OAuth token exchange failed', ['body' => $tokenResp->body()]);
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Failed to authenticate with Google. Please try again.'], 400);
                    }
                    return redirect('/login')->with('error', 'Failed to authenticate with Google. Please try again.');
                }

                $tokenData = $tokenResp->json();
                $accessToken = $tokenData['access_token'] ?? null;

                if (empty($accessToken)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Invalid token response received from Google.'], 400);
                    }
                    return redirect('/login')->with('error', 'Invalid token response received from Google.');
                }

                $userResponse = Http::withToken($accessToken)->timeout(15)->get('https://www.googleapis.com/oauth2/v3/userinfo');

                if ($userResponse->failed()) {
                    Log::error('Google OAuth userinfo fetch failed', ['body' => $userResponse->body()]);
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Unable to retrieve your Google profile.'], 400);
                    }
                    return redirect('/login')->with('error', 'Unable to retrieve your Google profile.');
                }

                $googleUser = $userResponse->json();
                $googleId = $googleUser['sub'] ?? null;
                $email = strtolower(trim($googleUser['email'] ?? ''));
                $name = $googleUser['name'] ?? 'Google User';
                $avatar = $googleUser['picture'] ?? null;
            }

            if (empty($email)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => 'No email address was provided by your Google account.'], 400);
                }
                return redirect('/login')->with('error', 'No email address was provided by your Google account.');
            }

            $role = $request->input('role') ?? session()->pull('oauth_intended_role', 'customer');
            $user = $this->findOrCreateSocialUser([
                'provider' => 'google',
                'provider_id' => $googleId,
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
                'role' => $role,
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            ActivityLogService::log('oauth_login', "Signed in via Google ({$email})", $user->id);

            $targetUrl = ($user->role === 'driver') ? '/driver/dashboard' : '/';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'redirect' => $targetUrl,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ]);
            }

            if ($user->role === 'driver') {
                return redirect('/driver/dashboard')->with('success', "Welcome back, {$user->name}! Signed in with Google.");
            }

            return redirect('/')->with('success', "Welcome, {$user->name}! Signed in with Google successfully.");

        } catch (\Throwable $e) {
            Log::error('Google OAuth exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'An unexpected error occurred during Google sign in: ' . $e->getMessage()], 500);
            }
            return redirect('/login')->with('error', 'An unexpected error occurred during Google sign in: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to Sign in with Apple Authorize Endpoint
     */
    public function redirectToApple(Request $request)
    {
        $clientId = config('services.apple.client_id');
        $redirectUri = config('services.apple.redirect', url('/auth/apple/callback'));

        if (empty($clientId)) {
            return redirect('/login')->with('error', 'Apple Sign-In is currently being initialized. Please configure APPLE_CLIENT_ID in your settings.');
        }

        $state = Str::random(40);
        session([
            'oauth_apple_state' => $state,
            'oauth_intended_role' => $request->query('role', 'customer'),
        ]);

        $queryParams = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code id_token',
            'response_mode' => 'form_post',
            'scope' => 'name email',
            'state' => $state,
        ]);

        return redirect('https://appleid.apple.com/auth/authorize?' . $queryParams);
    }

    /**
     * Handle Sign in with Apple Callback (POST via form_post)
     */
    public function handleAppleCallback(Request $request)
    {
        if ($request->has('error')) {
            Log::warning('Apple OAuth cancelled or error', ['error' => $request->get('error')]);
            return redirect('/login')->with('error', 'Apple Sign-In was cancelled or failed.');
        }

        $idToken = $request->input('id_token');
        if (empty($idToken)) {
            return redirect('/login')->with('error', 'No identification token received from Apple.');
        }

        try {
            // Parse JWT id_token payload
            $jwtParts = explode('.', $idToken);
            if (count($jwtParts) < 2) {
                return redirect('/login')->with('error', 'Malformed token received from Apple.');
            }

            $payload = json_decode(base64_decode(strtr($jwtParts[1], '-_', '+/')), true);
            if (!is_array($payload) || empty($payload['sub'])) {
                return redirect('/login')->with('error', 'Invalid Apple authentication payload.');
            }

            $appleId = $payload['sub'];
            $email = !empty($payload['email']) ? strtolower(trim($payload['email'])) : null;

            // Apple only sends name details on FIRST authorization in the 'user' POST field
            $name = null;
            if ($request->filled('user')) {
                $userData = json_decode($request->input('user'), true);
                if (!empty($userData['name'])) {
                    $firstName = $userData['name']['firstName'] ?? '';
                    $lastName = $userData['name']['lastName'] ?? '';
                    $name = trim("$firstName $lastName");
                }
            }

            $role = session()->pull('oauth_intended_role', 'customer');

            $user = $this->findOrCreateSocialUser([
                'provider' => 'apple',
                'provider_id' => $appleId,
                'email' => $email ?: ($appleId . '@privaterelay.appleid.com'),
                'name' => $name ?: 'Apple User',
                'avatar' => null,
                'role' => $role,
            ]);

            Auth::login($user, true);
            $request->session()->regenerate();

            ActivityLogService::log('oauth_login', "Signed in via Apple ({$user->email})", $user->id);

            if ($user->role === 'driver') {
                return redirect('/driver/dashboard')->with('success', "Welcome back, {$user->name}! Signed in with Apple.");
            }

            return redirect('/')->with('success', "Welcome, {$user->name}! Signed in with Apple successfully.");

        } catch (\Throwable $e) {
            Log::error('Apple OAuth exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect('/login')->with('error', 'An error occurred during Apple sign in: ' . $e->getMessage());
        }
    }

    /**
     * API Token Exchange for Mobile (Flutter) or AJAX Google Login
     */
    public function apiGoogleAuth(Request $request)
    {
        $idToken = $request->input('id_token') ?? $request->input('token');
        $accessToken = $request->input('access_token');
        $directEmail = $request->input('email');
        $directGoogleId = $request->input('google_id') ?? $request->input('id');

        if (empty($idToken) && empty($accessToken) && (empty($directEmail) || empty($directGoogleId))) {
            return response()->json([
                'success' => false,
                'message' => 'Missing Google authentication token (id_token or access_token).',
            ], 422);
        }

        try {
            $googleId = null;
            $email = null;
            $name = null;
            $avatar = null;

            if (!empty($idToken)) {
                // Verify ID Token with Google's public tokeninfo endpoint
                $resp = Http::timeout(10)->get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");
                if ($resp->successful()) {
                    $info = $resp->json();
                    $googleId = $info['sub'] ?? null;
                    $email = strtolower(trim($info['email'] ?? ''));
                    $name = $info['name'] ?? null;
                    $avatar = $info['picture'] ?? null;
                } else {
                    // Fallback to direct parameters if provided by mobile SDK
                    if (!empty($directEmail) && !empty($directGoogleId)) {
                        $googleId = $directGoogleId;
                        $email = strtolower(trim($directEmail));
                        $name = $request->input('name') ?: 'Google User';
                        $avatar = $request->input('avatar') ?: $request->input('photo_url');
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid or expired Google ID token.',
                        ], 401);
                    }
                }
            } elseif (!empty($accessToken)) {
                $resp = Http::withToken($accessToken)->timeout(10)->get('https://www.googleapis.com/oauth2/v3/userinfo');
                if ($resp->successful()) {
                    $info = $resp->json();
                    $googleId = $info['sub'] ?? null;
                    $email = strtolower(trim($info['email'] ?? ''));
                    $name = $info['name'] ?? null;
                    $avatar = $info['picture'] ?? null;
                } else {
                    if (!empty($directEmail) && !empty($directGoogleId)) {
                        $googleId = $directGoogleId;
                        $email = strtolower(trim($directEmail));
                        $name = $request->input('name') ?: 'Google User';
                        $avatar = $request->input('avatar') ?: $request->input('photo_url');
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid or expired Google access token.',
                        ], 401);
                    }
                }
            } else {
                $googleId = $directGoogleId;
                $email = strtolower(trim($directEmail));
                $name = $request->input('name') ?: 'Google User';
                $avatar = $request->input('avatar') ?: $request->input('photo_url');
            }

            if (empty($email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to resolve email from Google.',
                ], 422);
            }

            $role = $request->input('role', 'customer');
            if ($role === 'rider') $role = 'customer';

            $user = $this->findOrCreateSocialUser([
                'provider' => 'google',
                'provider_id' => $googleId,
                'email' => $email,
                'name' => $name ?: 'Google User',
                'avatar' => $avatar,
                'role' => $role,
            ]);

            $token = $user->createToken('google-auth-token')->plainTextToken;

            $driverProfile = null;
            try {
                if ($user->role === 'driver' || $user->driverProfile) {
                    $driverProfile = $user->driverProfile;
                }
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'avatar_url' => $user->avatar_url,
                    'referral_code' => $user->referral_code,
                    'referred_by' => $user->referred_by,
                ],
                'role' => $user->role,
                'driver_profile' => $driverProfile,
                'message' => 'Google authentication successful.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API Token Exchange for Mobile (Flutter) or AJAX Apple Login
     */
    public function apiAppleAuth(Request $request)
    {
        $idToken = $request->input('id_token') ?? $request->input('identity_token');
        $directAppleId = $request->input('apple_id') ?? $request->input('user_id');
        $directEmail = $request->input('email');

        if (empty($idToken) && empty($directAppleId)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing Apple identity credentials.',
            ], 422);
        }

        try {
            $appleId = null;
            $email = null;

            if (!empty($idToken)) {
                $jwtParts = explode('.', $idToken);
                if (count($jwtParts) >= 2) {
                    $payload = json_decode(base64_decode(strtr($jwtParts[1], '-_', '+/')), true);
                    if (is_array($payload) && !empty($payload['sub'])) {
                        $appleId = $payload['sub'];
                        $email = !empty($payload['email']) ? strtolower(trim($payload['email'])) : null;
                    }
                }
            }

            if (empty($appleId)) {
                $appleId = $directAppleId;
            }
            if (empty($email) && !empty($directEmail)) {
                $email = strtolower(trim($directEmail));
            }

            if (empty($appleId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Apple identity token or user ID.',
                ], 401);
            }

            $name = $request->input('name');
            $role = $request->input('role', 'customer');
            if ($role === 'rider') $role = 'customer';

            $user = $this->findOrCreateSocialUser([
                'provider' => 'apple',
                'provider_id' => $appleId,
                'email' => $email ?: ($appleId . '@privaterelay.appleid.com'),
                'name' => $name ?: 'Apple User',
                'avatar' => null,
                'role' => $role,
            ]);

            $token = $user->createToken('apple-auth-token')->plainTextToken;

            $driverProfile = null;
            try {
                if ($user->role === 'driver' || $user->driverProfile) {
                    $driverProfile = $user->driverProfile;
                }
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'avatar_url' => $user->avatar_url,
                    'referral_code' => $user->referral_code,
                    'referred_by' => $user->referred_by,
                ],
                'role' => $user->role,
                'driver_profile' => $driverProfile,
                'message' => 'Apple authentication successful.',
            ]);


        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Apple authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find or create user from social OAuth identity
     */
    protected function findOrCreateSocialUser(array $data): User
    {
        $provider = $data['provider'];
        $providerId = $data['provider_id'];
        $email = $data['email'];
        $name = $data['name'];
        $avatar = $data['avatar'] ?? null;
        $role = in_array($data['role'], ['customer', 'driver', 'owner', 'rider']) ? $data['role'] : 'customer';

        // 1. Try finding user by social provider ID
        $user = null;
        if ($provider === 'google') {
            $user = User::where('google_id', $providerId)->first();
        } elseif ($provider === 'apple') {
            $user = User::where('apple_id', $providerId)->first();
        }

        // 2. If not found by provider ID, find by email and link
        if (!$user && !empty($email)) {
            $user = User::where('email', $email)->orWhereRaw('LOWER(email) = ?', [$email])->first();
        }

        if ($user) {
            // Update provider details if not set
            $updated = false;
            if ($provider === 'google' && empty($user->google_id)) {
                $user->google_id = $providerId;
                $updated = true;
            }
            if ($provider === 'apple' && empty($user->apple_id)) {
                $user->apple_id = $providerId;
                $updated = true;
            }
            if (empty($user->oauth_provider)) {
                $user->oauth_provider = $provider;
                $updated = true;
            }
            if (!empty($avatar) && empty($user->oauth_avatar)) {
                $user->oauth_avatar = $avatar;
                $updated = true;
            }
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
                $updated = true;
            }
            if (in_array($user->account_status, ['pending', null, ''])) {
                $user->account_status = 'active';
                $updated = true;
            }
            if ($updated) {
                $user->save();
            }
            return $user;
        }

        // 3. Create new user account
        $newUser = User::create([
            'name' => $name,
            'email' => $email,
            'google_id' => $provider === 'google' ? $providerId : null,
            'apple_id' => $provider === 'apple' ? $providerId : null,
            'oauth_provider' => $provider,
            'oauth_avatar' => $avatar,
            'password' => Hash::make(Str::random(36)),
            'role' => $role,
            'account_status' => 'active',
            'email_verified_at' => now(),
        ]);

        // If registered as driver, create default driver profile
        if ($role === 'driver') {
            DriverProfile::firstOrCreate(
                ['user_id' => $newUser->id],
                [
                    'license_number' => 'DL-' . strtoupper(Str::random(8)),
                    'hourly_rate' => 35.00,
                    'daily_rate' => 240.00,
                    'weekly_rate' => 1400.00,
                    'experience_years' => 1,
                    'country' => 'USA',
                    'service_area' => 'Default Area',
                    'is_available' => true,
                    'rating' => 5.0,
                    'total_trips' => 0,
                    'verification_status' => 'pending',
                    'bio' => 'New driver joined with ' . ucfirst($provider),
                ]
            );
        }

        return $newUser;
    }
}
