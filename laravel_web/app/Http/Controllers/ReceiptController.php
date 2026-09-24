<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReceiptController extends Controller
{
    /**
     * Display the receipt online.
     */
    public function show(string $token)
    {
        $receipt = Receipt::with(['user', 'driver', 'ride', 'driverBooking', 'packageDelivery'])
            ->where('verification_token', $token)
            ->orWhere('receipt_number', $token)
            ->firstOrFail();

        return view('receipt', [
            'receipt' => $receipt,
            'snapshot' => $receipt->snapshot_data ?? [],
        ]);
    }

    /**
     * Download the stored PDF receipt.
     */
    public function download(string $token)
    {
        $receipt = Receipt::where('verification_token', $token)
            ->orWhere('receipt_number', $token)
            ->firstOrFail();

        // Ensure PDF exists on disk
        $path = $receipt->getPdfAbsolutePath();

        if (!$path || !file_exists($path)) {
            ReceiptService::generatePdf($receipt, true);
            $path = $receipt->getPdfAbsolutePath();
        }

        if (!$path || !file_exists($path)) {
            abort(404, 'Receipt PDF could not be found or generated.');
        }

        $filename = "Receipt_{$receipt->receipt_number}.pdf";

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Re-send the receipt to the customer's email.
     */
    public function resend(Request $request, $id)
    {
        $receipt = Receipt::findOrFail($id);

        // Security check: only owner or admin can trigger
        $user = Auth::user();
        if (!$user || ($user->id !== $receipt->user_id && $user->role !== 'admin' && $user->role !== 'support')) {
            abort(403, 'Unauthorized to resend this receipt.');
        }

        $targetEmail = $request->input('email', $receipt->snapshot_data['customer_email'] ?? $receipt->user->email ?? null);

        $success = ReceiptService::sendReceiptEmail($receipt, $targetEmail);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? "Receipt sent to {$targetEmail}" : "Failed to send receipt to {$targetEmail}",
            ]);
        }

        return back()->with(
            $success ? 'success' : 'error',
            $success ? "Receipt has been resent to {$targetEmail}." : "Failed to email receipt."
        );
    }

    /**
     * API: List receipts for current user.
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $type = $request->query('type'); // all, ride, rental, driver_booking, delivery
        $query = Receipt::where('user_id', $user->id);

        if (!empty($type) && $type !== 'all') {
            $query->where('booking_type', $type);
        }

        $receipts = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'receipts' => $receipts->map(function ($r) {
                return [
                    'id' => $r->id,
                    'receipt_number' => $r->receipt_number,
                    'booking_type' => $r->booking_type,
                    'type_label' => $r->type_label,
                    'booking_id' => $r->booking_id,
                    'total_amount' => (float)$r->total_amount,
                    'currency' => $r->currency,
                    'payment_method' => $r->payment_method,
                    'payment_status' => $r->payment_status,
                    'created_at' => $r->created_at->toIso8601String(),
                    'view_url' => $r->view_url,
                    'download_url' => $r->download_url,
                    'snapshot' => $r->snapshot_data,
                ];
            }),
            'pagination' => [
                'current_page' => $receipts->currentPage(),
                'last_page' => $receipts->lastPage(),
                'total' => $receipts->total(),
            ]
        ]);
    }

    /**
     * API: Get single receipt.
     */
    public function apiShow(Request $request, $id)
    {
        $user = $request->user();
        $receipt = Receipt::where('id', $id)
            ->orWhere('verification_token', $id)
            ->orWhere('receipt_number', $id)
            ->firstOrFail();

        if ($user && $user->id !== $receipt->user_id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json([
            'success' => true,
            'receipt' => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'booking_type' => $receipt->booking_type,
                'type_label' => $receipt->type_label,
                'booking_id' => $receipt->booking_id,
                'total_amount' => (float)$receipt->total_amount,
                'currency' => $receipt->currency,
                'payment_method' => $receipt->payment_method,
                'payment_status' => $receipt->payment_status,
                'created_at' => $receipt->created_at->toIso8601String(),
                'view_url' => $receipt->view_url,
                'download_url' => $receipt->download_url,
                'snapshot' => $receipt->snapshot_data,
            ]
        ]);
    }
}
