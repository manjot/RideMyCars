<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Ride;
use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReceiptController extends Controller
{
    /**
     * Resolve a Receipt model from various identifiers:
     * - verification_token (e.g. 32-char hex string)
     * - receipt_number (e.g. CRN26092400001, RNT26092400002)
     * - numeric receipt ID (e.g. 1, 2)
     * - digital_receipt_code (e.g. REC-XXXXXXXX, RNT-XXXX)
     * - booking codes (e.g. RIDE-12, RNT-12, DEL-12, CHF-12)
     * - numeric booking/ride ID (e.g. if passed /receipts/12)
     */
    public static function resolveReceipt(string $token): ?Receipt
    {
        $token = trim($token);
        if (empty($token)) {
            return null;
        }

        // 1. Direct search by verification_token, receipt_number
        $receipt = Receipt::with(['user', 'driver', 'ride', 'driverBooking', 'packageDelivery'])
            ->where('verification_token', $token)
            ->orWhere('receipt_number', $token)
            ->first();

        if ($receipt) {
            return $receipt;
        }

        // 2. Direct search by numeric receipt primary ID
        if (is_numeric($token)) {
            $receipt = Receipt::with(['user', 'driver', 'ride', 'driverBooking', 'packageDelivery'])->find((int)$token);
            if ($receipt) {
                return $receipt;
            }
        }

        // 3. Search by Ride digital_receipt_code (e.g. REC-XXXXXXXX, RNT-XXXX)
        $ride = Ride::where('digital_receipt_code', $token)->first();
        if ($ride) {
            return ReceiptService::generateReceiptForRide($ride, false);
        }

        // 4. Search by booking codes:
        // RIDE-123 or RNT-123
        if (preg_match('/^(?:RIDE|RNT|RENT)-(\d+)$/i', $token, $m)) {
            $ride = Ride::find((int)$m[1]);
            if ($ride) {
                return ReceiptService::generateReceiptForRide($ride, false);
            }
        }

        // DEL-123 or delivery_code
        if (preg_match('/^DEL-(\d+)$/i', $token, $m)) {
            $del = PackageDelivery::find((int)$m[1]);
            if ($del) {
                return ReceiptService::generateReceiptForPackageDelivery($del, false);
            }
        }
        $del = PackageDelivery::where('delivery_code', $token)->first();
        if ($del) {
            return ReceiptService::generateReceiptForPackageDelivery($del, false);
        }

        // CHF-123 or booking_code
        if (preg_match('/^(?:CHF|BK)-(\d+)$/i', $token, $m)) {
            $bk = DriverBooking::find((int)$m[1]);
            if ($bk) {
                return ReceiptService::generateReceiptForDriverBooking($bk, false);
            }
        }
        $bk = DriverBooking::where('booking_code', $token)->first();
        if ($bk) {
            return ReceiptService::generateReceiptForDriverBooking($bk, false);
        }

        // 5. CRN / RNT date-stamped receipt numbers (e.g. CRN26100700012)
        if (preg_match('/^(?:CRN|RNT)\d{6}(\d{5})$/i', $token, $m)) {
            $rideId = (int)ltrim($m[1], '0');
            $ride = Ride::find($rideId);
            if ($ride) {
                return ReceiptService::generateReceiptForRide($ride, false);
            }
        }

        // 6. If token is numeric, try Ride, DriverBooking, or PackageDelivery by ID
        if (is_numeric($token)) {
            $numId = (int)$token;
            $ride = Ride::find($numId);
            if ($ride) {
                return ReceiptService::generateReceiptForRide($ride, false);
            }
            $bk = DriverBooking::find($numId);
            if ($bk) {
                return ReceiptService::generateReceiptForDriverBooking($bk, false);
            }
            $del = PackageDelivery::find($numId);
            if ($del) {
                return ReceiptService::generateReceiptForPackageDelivery($del, false);
            }
        }

        return null;
    }

    /**
     * Display the receipt online.
     */
    public function show(string $token)
    {
        $receipt = static::resolveReceipt($token);

        if (!$receipt) {
            abort(404, 'Digital receipt could not be found.');
        }

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
        $receipt = static::resolveReceipt($token);

        if (!$receipt) {
            abort(404, 'Digital receipt could not be found.');
        }

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
        $receipt = static::resolveReceipt((string)$id);

        if (!$receipt) {
            return response()->json(['success' => false, 'message' => 'Receipt not found.'], 404);
        }

        // Allow owner, driver, or admin to access
        if ($user && $user->id !== $receipt->user_id && $user->id !== $receipt->driver_id && $user->role !== 'admin') {
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
