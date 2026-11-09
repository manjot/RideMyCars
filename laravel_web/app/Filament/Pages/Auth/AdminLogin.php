<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;

class AdminLogin extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(10);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $remember = (bool) ($data['remember'] ?? false);

        $user = User::where('email', $email)
            ->orWhereRaw('LOWER(email) = ?', [$email])
            ->first();

        // Only bootstrap the initial default admin user if NO admin account exists in the database at all
        if (! $user && $email === 'admin@ridemycars.com' && $password === '123456' && User::where('role', 'admin')->doesntExist()) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@ridemycars.com',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        // Strict database password check — never bypass or overwrite an existing admin's password
        if ($user && Hash::check($password, (string) $user->password)) {
            if (
                ($user instanceof FilamentUser) &&
                (! $user->canAccessPanel(Filament::getCurrentPanel()))
            ) {
                $this->throwFailureValidationException();
            }

            Filament::auth()->login($user, $remember);
            session()->regenerate();

            return app(LoginResponse::class);
        }

        $this->throwFailureValidationException();
    }
}

