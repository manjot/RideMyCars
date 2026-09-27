<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserPayoutMethod;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use App\Services\SettingService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletApiController extends Controller
{
    /**
     * Get user wallet balance and withdrawal configuration.
     * GET /api/wallet/balance
     */
    public function getBalance(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $summary = WalletService::getWalletSummary($user);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Submit a new withdrawal request.
     * POST /api/wallet/withdraw
     */
    public function submitWithdrawal(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payout_method' => 'required|string|in:bank_account,momo',
            'payout_details' => 'required|array',
            'save_payout_method' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $withdrawal = WalletService::submitWithdrawal(
                $user,
                (float) $request->input('amount'),
                $request->input('payout_method'),
                $request->input('payout_details'),
                (bool) $request->input('save_payout_method', false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully! Your funds will be processed upon approval.',
                'data' => [
                    'id' => $withdrawal->id,
                    'ref' => $withdrawal->withdrawal_ref,
                    'amount' => $withdrawal->amount,
                    'currency' => $withdrawal->currency,
                    'fee' => $withdrawal->fee,
                    'net_amount' => $withdrawal->net_amount,
                    'payout_method' => $withdrawal->payout_method,
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to submit withdrawal request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List user's withdrawal requests with optional status filter.
     * GET /api/wallet/withdrawals
     */
    public function getWithdrawalHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $query = WalletWithdrawal::where('user_id', $user->id);

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $perPage = min(50, max(5, (int) $request->query('per_page', 15)));
        $withdrawals = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->items(),
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'total' => $withdrawals->total(),
                'per_page' => $withdrawals->perPage(),
            ],
            'summary' => [
                'pending_count' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'pending')->count(),
                'approved_count' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'approved')->count(),
                'rejected_count' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'rejected')->count(),
                'total_withdrawn' => (float) WalletWithdrawal::where('user_id', $user->id)->where('status', 'approved')->sum('amount'),
            ],
        ]);
    }

    /**
     * Get specific withdrawal request details.
     * GET /api/wallet/withdrawals/{id}
     */
    public function getWithdrawalDetail(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $withdrawal = WalletWithdrawal::where('id', $id)
            ->where(function ($q) use ($user) {
                if ($user->role !== 'admin') {
                    $q->where('user_id', $user->id);
                }
            })
            ->first();

        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $withdrawal,
        ]);
    }

    /**
     * Get user saved payout methods.
     * GET /api/wallet/payout-methods
     */
    public function getPayoutMethods(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $methods = UserPayoutMethod::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Save new payout method.
     * POST /api/wallet/payout-methods
     */
    public function savePayoutMethod(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:bank_account,momo',
            'is_default' => 'nullable|boolean',
            'bank_name' => 'required_if:type,bank_account|nullable|string|max:100',
            'account_holder_name' => 'nullable|string|max:120',
            'account_number' => 'required_if:type,bank_account|nullable|string|max:50',
            'routing_code' => 'nullable|string|max:50',
            'branch_name' => 'nullable|string|max:100',
            'momo_network' => 'required_if:type,momo|nullable|string|max:50',
            'momo_phone' => 'required_if:type,momo|nullable|string|max:50',
            'momo_account_name' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $method = WalletService::savePayoutMethod($user, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Payout method saved successfully.',
            'data' => $method,
        ], 201);
    }

    /**
     * Delete saved payout method.
     * DELETE /api/wallet/payout-methods/{id}
     */
    public function deletePayoutMethod(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $deleted = WalletService::deletePayoutMethod($user, (int) $id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Payout method not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout method removed successfully.',
        ]);
    }

    /**
     * Get user wallet transactions history.
     * GET /api/wallet/transactions
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $query = WalletTransaction::where('user_id', $user->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = min(50, max(5, (int) $request->query('per_page', 20)));
        $transactions = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    // ==========================================
    // ADMIN ENDPOINTS
    // ==========================================

    /**
     * Admin: List all withdrawal requests with search & filters.
     * GET /api/admin/withdrawals
     */
    public function adminListWithdrawals(Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin access required.'], 403);
        }

        $query = WalletWithdrawal::with(['user:id,name,email,phone,role,country', 'approver:id,name', 'rejecter:id,name']);

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Country
        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        // Filter: User role / type
        if ($request->filled('role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        // Filter: Date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search: User name, email, phone, or withdrawal ref
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('withdrawal_ref', 'like', "%{$term}%")
                    ->orWhere('transaction_reference', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
            });
        }

        $perPage = min(100, max(5, (int) $request->query('per_page', 20)));
        $withdrawals = $query->latest()->paginate($perPage);

        $counts = [
            'pending' => WalletWithdrawal::where('status', 'pending')->count(),
            'approved' => WalletWithdrawal::where('status', 'approved')->count(),
            'rejected' => WalletWithdrawal::where('status', 'rejected')->count(),
            'total' => WalletWithdrawal::count(),
            'pending_amount' => (float) WalletWithdrawal::where('status', 'pending')->sum('amount'),
            'disbursed_amount' => (float) WalletWithdrawal::where('status', 'approved')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $withdrawals->items(),
            'counts' => $counts,
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'total' => $withdrawals->total(),
            ],
        ]);
    }

    /**
     * Admin: Approve a withdrawal request.
     * POST /api/admin/withdrawals/{id}/approve
     */
    public function adminApprove(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin access required.'], 403);
        }

        $withdrawal = WalletWithdrawal::find($id);
        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'transaction_reference' => 'nullable|string|max:100',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $updated = WalletService::approveWithdrawal(
                $withdrawal,
                $admin,
                $request->input('transaction_reference'),
                $request->input('admin_notes')
            );

            return response()->json([
                'success' => true,
                'message' => "Withdrawal {$updated->withdrawal_ref} approved and wallet balance deducted successfully.",
                'data' => $updated,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Reject a withdrawal request with mandatory reason.
     * POST /api/admin/withdrawals/{id}/reject
     */
    public function adminReject(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin access required.'], 403);
        }

        $withdrawal = WalletWithdrawal::find($id);
        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:3|max:500',
            'admin_notes' => 'nullable|string|max:500',
        ], [
            'rejection_reason.required' => 'A rejection reason is mandatory when declining a withdrawal request.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = WalletService::rejectWithdrawal(
                $withdrawal,
                $admin,
                $request->input('rejection_reason'),
                $request->input('admin_notes')
            );

            return response()->json([
                'success' => true,
                'message' => "Withdrawal {$updated->withdrawal_ref} rejected. User has been notified with the rejection reason.",
                'data' => $updated,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Get withdrawal configuration settings.
     * GET /api/admin/withdrawal-settings
     */
    public function adminGetSettings(Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin access required.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => WalletService::getSettings(),
        ]);
    }

    /**
     * Admin: Update withdrawal configuration settings.
     * POST /api/admin/withdrawal-settings
     */
    public function adminUpdateSettings(Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!$admin || !in_array($admin->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'min_amount' => 'required|numeric|min:1',
            'max_amount' => 'required|numeric|gte:min_amount',
            'fee_type' => 'required|string|in:fixed,percentage',
            'fee_value' => 'required|numeric|min:0',
            'allowed_methods' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        SettingService::set('wallet.withdrawals_enabled', $request->boolean('enabled') ? '1' : '0', 'Wallet Settings', 'text', 'Enable Wallet Withdrawals');
        SettingService::set('wallet.min_withdrawal_amount', (string) $request->input('min_amount'), 'Wallet Settings', 'text', 'Minimum Withdrawal Amount');
        SettingService::set('wallet.max_withdrawal_amount', (string) $request->input('max_amount'), 'Wallet Settings', 'text', 'Maximum Withdrawal Amount');
        SettingService::set('wallet.withdrawal_fee_type', $request->input('fee_type'), 'Wallet Settings', 'text', 'Withdrawal Fee Type (fixed/percentage)');
        SettingService::set('wallet.withdrawal_fee_value', (string) $request->input('fee_value'), 'Wallet Settings', 'text', 'Withdrawal Fee Value');
        SettingService::set('wallet.allowed_payout_methods', implode(',', $request->input('allowed_methods')), 'Wallet Settings', 'text', 'Allowed Payout Methods');

        return response()->json([
            'success' => true,
            'message' => 'Wallet withdrawal settings updated successfully.',
            'data' => WalletService::getSettings(),
        ]);
    }
}
