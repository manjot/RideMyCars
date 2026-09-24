<?php

namespace App\Services;

use App\Mail\BookingReceiptMail;
use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\Receipt;
use App\Models\Ride;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptService
{
    /**
     * Generate or retrieve receipt for a Ride or Rental.
     */
    public static function generateReceiptForRide(Ride $ride, bool $sendEmail = true): Receipt
    {
        // Check if receipt already exists
        if ($ride->receipt_id) {
            $existing = Receipt::find($ride->receipt_id);
            if ($existing) {
                if ($sendEmail && $existing->email_status !== 'sent') {
                    static::sendReceiptEmail($existing);
                }
                return $existing;
            }
        }

        $existing = Receipt::where('booking_type', $ride->ride_type === 'rental' || $ride->vehicle_id !== null ? 'rental' : 'ride')
            ->where('booking_id', $ride->id)
            ->first();

        if ($existing) {
            $ride->update(['receipt_id' => $existing->id]);
            if ($sendEmail && $existing->email_status !== 'sent') {
                static::sendReceiptEmail($existing);
            }
            return $existing;
        }

        $isRental = $ride->ride_type === 'rental' || $ride->vehicle_id !== null || str_starts_with((string)$ride->vehicle_type, 'RENTAL_');
        $bookingType = $isRental ? 'rental' : 'ride';

        $totalAmount = (float)($ride->total_amount ?? $ride->fare ?? 0);
        $taxAmount = 0.00;
        $feeAmount = 0.00;
        $discountAmount = 0.00;
        $subtotal = $totalAmount;

        // If subtotal + tax breakdown exists, compute
        if ($totalAmount > 0) {
            $subtotal = round($totalAmount / 1.05, 2);
            $taxAmount = round($totalAmount - $subtotal, 2);
        }

        $rider = $ride->rider;
        $driver = $ride->driver;
        $vehicle = $ride->vehicle;

        $prefix = $isRental ? 'RNT' : 'CRN';
        $receiptNumber = $prefix . date('ymd') . str_pad((string)$ride->id, 5, '0', STR_PAD_LEFT);

        $vehicleTitle = 'Standard Vehicle';
        if ($vehicle) {
            $vehicleTitle = "{$vehicle->year} {$vehicle->make} {$vehicle->model}";
        } elseif (!empty($ride->vehicle_type)) {
            $vehicleTitle = preg_replace('/^RENTAL_\d+_/', '', (string)$ride->vehicle_type);
        }

        $snapshot = [
            'booking_id' => $ride->id,
            'booking_type' => $bookingType,
            'customer_name' => $ride->passenger_name ?: ($rider ? $rider->name : 'Valued Passenger'),
            'customer_phone' => $ride->passenger_phone ?: ($rider ? $rider->phone : null),
            'customer_email' => $rider ? $rider->email : null,
            'driver_name' => $driver ? $driver->name : ($isRental ? 'Self-Drive Vehicle' : 'Assigned Driver'),
            'driver_phone' => $driver ? $driver->phone : null,
            'vehicle_title' => $vehicleTitle,
            'vehicle_plate' => $vehicle ? $vehicle->license_plate : null,
            'pickup_location' => $ride->pickup_location ?: 'Pickup Location',
            'dropoff_location' => $ride->dropoff_location ?: 'Dropoff Location',
            'pickup_date' => $ride->pickup_date ? $ride->pickup_date->format('Y-m-d') : null,
            'pickup_time' => $ride->pickup_time,
            'distance_km' => $ride->distance_km ? (float)$ride->distance_km : null,
            'duration_minutes' => $ride->duration_minutes ? (int)$ride->duration_minutes : null,
            'extras_fee' => (float)($ride->extras_fee ?? 0),
            'protection_fee' => (float)($ride->protection_fee ?? 0),
            'fuel_policy' => $ride->fuel_policy ?? null,
            'completed_at' => $ride->completed_at ? $ride->completed_at->toIso8601String() : now()->toIso8601String(),
        ] + static::getCompanySnapshot();

        $currency = 'USD';
        if ($rider && !empty($rider->country)) {
            $country = strtolower($rider->country);
            if (in_array($country, ['ghana', 'gh'])) $currency = 'GHS';
            elseif (in_array($country, ['india', 'in'])) $currency = 'INR';
            elseif (in_array($country, ['uk', 'gbr', 'united kingdom'])) $currency = 'GBP';
            elseif (in_array($country, ['eu', 'germany', 'france', 'spain'])) $currency = 'EUR';
        }

        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'booking_type' => $bookingType,
            'booking_id' => $ride->id,
            'user_id' => $ride->rider_id ?: ($rider ? $rider->id : 1),
            'driver_id' => $ride->driver_id,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'fee_amount' => $feeAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $ride->payment_method ?: 'Cash',
            'payment_status' => $ride->payment_status ?: 'paid',
            'verification_token' => Str::random(32),
            'snapshot_data' => $snapshot,
        ]);

        $ride->update(['receipt_id' => $receipt->id]);

        // Generate and store PDF
        static::generatePdf($receipt);

        if ($sendEmail) {
            static::sendReceiptEmail($receipt);
        }

        return $receipt;
    }

    /**
     * Generate or retrieve receipt for a DriverBooking (Chauffeur).
     */
    public static function generateReceiptForDriverBooking(DriverBooking $booking, bool $sendEmail = true): Receipt
    {
        if ($booking->receipt_id) {
            $existing = Receipt::find($booking->receipt_id);
            if ($existing) {
                if ($sendEmail && $existing->email_status !== 'sent') {
                    static::sendReceiptEmail($existing);
                }
                return $existing;
            }
        }

        $existing = Receipt::where('booking_type', 'driver_booking')
            ->where('booking_id', $booking->id)
            ->first();

        if ($existing) {
            $booking->update(['receipt_id' => $existing->id]);
            if ($sendEmail && $existing->email_status !== 'sent') {
                static::sendReceiptEmail($existing);
            }
            return $existing;
        }

        $totalAmount = (float)($booking->final_fare ?: $booking->total_price ?: 0);
        $subtotal = (float)($booking->subtotal ?: ($totalAmount > 0 ? round($totalAmount / 1.05, 2) : 0));
        $taxAmount = (float)($booking->tax ?: ($totalAmount - $subtotal));
        $feeAmount = (float)($booking->service_fee ?: 0);

        $client = $booking->client;
        $driver = $booking->driver;
        $vehicle = $booking->vehicle;

        $receiptNumber = 'CHF' . date('ymd') . str_pad((string)$booking->id, 5, '0', STR_PAD_LEFT);

        $vehicleTitle = $booking->car_make_model ?: ($vehicle ? "{$vehicle->year} {$vehicle->make} {$vehicle->model}" : 'Executive Chauffeur Vehicle');

        $snapshot = [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'booking_type' => 'driver_booking',
            'customer_name' => $client ? $client->name : 'Valued Client',
            'customer_phone' => $client ? $client->phone : null,
            'customer_email' => $client ? $client->email : null,
            'driver_name' => $driver ? $driver->name : 'Private Executive Chauffeur',
            'driver_phone' => $driver ? $driver->phone : null,
            'vehicle_title' => $vehicleTitle,
            'vehicle_plate' => $booking->registration_number ?: ($vehicle ? $vehicle->license_plate : null),
            'pickup_location' => $booking->pickup_location ?: 'Pickup Location',
            'dropoff_location' => $booking->dropoff_location ?: 'As Directed / Standby',
            'start_date' => $booking->start_date ? $booking->start_date->format('Y-m-d') : null,
            'start_time' => $booking->start_time,
            'duration_type' => $booking->duration_type,
            'duration_count' => $booking->duration_count,
            'distance_km' => $booking->actual_distance_km ? (float)$booking->actual_distance_km : null,
            'duration_minutes' => $booking->actual_duration_minutes ? (int)$booking->actual_duration_minutes : null,
            'escrow_deposit_amount' => (float)($booking->escrow_deposit_amount ?? 0),
            'completed_at' => $booking->completed_at ? $booking->completed_at->toIso8601String() : now()->toIso8601String(),
        ] + static::getCompanySnapshot();

        $currency = $booking->currency ?: 'USD';

        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'booking_type' => 'driver_booking',
            'booking_id' => $booking->id,
            'user_id' => $booking->client_id ?: ($client ? $client->id : 1),
            'driver_id' => $booking->driver_id,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'discount_amount' => 0.00,
            'tax_amount' => $taxAmount,
            'fee_amount' => $feeAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $booking->payment_method ?: 'Credit Card',
            'payment_status' => $booking->payment_status ?: 'paid',
            'verification_token' => Str::random(32),
            'snapshot_data' => $snapshot,
        ]);

        $booking->update(['receipt_id' => $receipt->id]);

        static::generatePdf($receipt);

        if ($sendEmail) {
            static::sendReceiptEmail($receipt);
        }

        return $receipt;
    }

    /**
     * Generate or retrieve receipt for a PackageDelivery.
     */
    public static function generateReceiptForPackageDelivery(PackageDelivery $delivery, bool $sendEmail = true): Receipt
    {
        if ($delivery->receipt_id) {
            $existing = Receipt::find($delivery->receipt_id);
            if ($existing) {
                if ($sendEmail && $existing->email_status !== 'sent') {
                    static::sendReceiptEmail($existing);
                }
                return $existing;
            }
        }

        $existing = Receipt::where('booking_type', 'delivery')
            ->where('booking_id', $delivery->id)
            ->first();

        if ($existing) {
            $delivery->update(['receipt_id' => $existing->id]);
            if ($sendEmail && $existing->email_status !== 'sent') {
                static::sendReceiptEmail($existing);
            }
            return $existing;
        }

        $totalAmount = (float)($delivery->total_price ?: 0);
        $subtotal = (float)($delivery->subtotal ?: ($totalAmount > 0 ? round($totalAmount / 1.05, 2) : 0));
        $taxAmount = (float)($delivery->tax ?: ($totalAmount - $subtotal));
        $feeAmount = (float)($delivery->service_fee ?: 0);

        $customer = $delivery->customer;
        $courier = $delivery->courier;

        $receiptNumber = 'DEL' . date('ymd') . str_pad((string)$delivery->id, 5, '0', STR_PAD_LEFT);

        $snapshot = [
            'booking_id' => $delivery->id,
            'delivery_code' => $delivery->delivery_code,
            'booking_type' => 'delivery',
            'customer_name' => $delivery->sender_name ?: ($customer ? $customer->name : 'Valued Customer'),
            'customer_phone' => $delivery->sender_phone ?: ($customer ? $customer->phone : null),
            'customer_email' => $customer ? $customer->email : null,
            'driver_name' => $courier ? $courier->name : 'Delivery Courier Partner',
            'driver_phone' => $courier ? $courier->phone : null,
            'recipient_name' => $delivery->recipient_name,
            'recipient_phone' => $delivery->recipient_phone,
            'package_category' => $delivery->package_category ?: 'Parcel',
            'package_size' => $delivery->package_size ?: 'Medium',
            'package_weight_kg' => (float)($delivery->package_weight_kg ?? 1.0),
            'pickup_location' => $delivery->pickup_location ?: 'Sender Address',
            'dropoff_location' => $delivery->dropoff_location ?: 'Recipient Address',
            'completed_at' => $delivery->delivered_at ? $delivery->delivered_at->toIso8601String() : now()->toIso8601String(),
        ] + static::getCompanySnapshot();

        $currency = $delivery->currency ?: 'USD';

        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'booking_type' => 'delivery',
            'booking_id' => $delivery->id,
            'user_id' => $delivery->customer_id ?: ($customer ? $customer->id : 1),
            'driver_id' => $delivery->courier_id,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'discount_amount' => 0.00,
            'tax_amount' => $taxAmount,
            'fee_amount' => $feeAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $delivery->payment_method ?: 'Credit Card',
            'payment_status' => $delivery->payment_status ?: 'paid',
            'verification_token' => Str::random(32),
            'snapshot_data' => $snapshot,
        ]);

        $delivery->update(['receipt_id' => $receipt->id]);

        static::generatePdf($receipt);

        if ($sendEmail) {
            static::sendReceiptEmail($receipt);
        }

        return $receipt;
    }

    /**
     * Generate the PDF receipt file and store it.
     * Ensures we do not regenerate if already generated, unless $force is true.
     */
    public static function generatePdf(Receipt $receipt, bool $force = false): string
    {
        // Check if stored PDF already exists
        if (!$force && $receipt->hasPdf()) {
            return $receipt->pdf_path;
        }

        // Relative path inside public storage
        $filename = "receipt_{$receipt->receipt_number}.pdf";
        $relativeDir = "receipts/" . date('Y/m');
        $relativePath = $relativeDir . '/' . $filename;

        // Ensure directories exist in both storage and public
        $storageDir = storage_path('app/public/' . $relativeDir);
        $publicDir = public_path($relativeDir);

        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true, true);
        }

        $snapshot = $receipt->snapshot_data ?? [];

        // Render PDF with DomPDF
        $pdf = Pdf::loadView('pdf.receipt', [
            'receipt' => $receipt,
            'snapshot' => $snapshot,
        ]);

        $pdf->setPaper('a4', 'portrait');

        // Save PDF to storage and public
        $fullStoragePath = storage_path('app/public/' . $relativePath);
        $fullPublicPath = public_path($relativePath);

        $output = $pdf->output();
        file_put_contents($fullStoragePath, $output);
        file_put_contents($fullPublicPath, $output);

        $receipt->update([
            'pdf_path' => $relativePath,
        ]);

        return $relativePath;
    }

    /**
     * Send receipt to customer's registered email address.
     */
    public static function sendReceiptEmail(Receipt $receipt, ?string $toEmail = null): bool
    {
        $targetEmail = $toEmail;

        if (empty($targetEmail)) {
            $targetEmail = $receipt->snapshot_data['customer_email'] ?? null;
        }

        if (empty($targetEmail) && $receipt->user) {
            $targetEmail = $receipt->user->email;
        }

        if (empty($targetEmail)) {
            Log::warning("Cannot send receipt #{$receipt->receipt_number}: No valid recipient email.");
            $receipt->update(['email_status' => 'failed']);
            return false;
        }

        // Ensure PDF is generated before emailing
        static::generatePdf($receipt);

        try {
            Mail::to($targetEmail)->send(new BookingReceiptMail($receipt));

            $receipt->update([
                'sent_to_email' => $targetEmail,
                'emailed_at' => now(),
                'email_status' => 'sent',
            ]);

            ActivityLogService::log(
                'receipt_emailed',
                "Sent receipt #{$receipt->receipt_number} to {$targetEmail}",
                $receipt->user_id
            );

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to email receipt #{$receipt->receipt_number}: " . $e->getMessage());

            $receipt->update([
                'sent_to_email' => $targetEmail,
                'email_status' => 'failed',
            ]);

            return false;
        }
    }

    /**
     * Get Company Details snapshot for legal invoice compliance.
     */
    public static function getCompanySnapshot(): array
    {
        return [
            'company_name' => SettingService::get('company_name', 'RideMyCars Technologies Inc.'),
            'company_address' => SettingService::get('company_address', 'RideMyCars Global Operations, 100 Enterprise Way, Suite 400'),
            'company_email' => SettingService::get('company_email', config('mail.from.address', 'support@ridemycars.com')),
            'company_phone' => SettingService::get('company_phone', '+1 (800) 555-RIDE'),
            'company_gst_vat' => SettingService::get('company_gst_vat', 'GSTIN/VAT: 07AAACR1234F1Z8'),
            'company_website' => SettingService::get('company_website', 'https://ridemycars.com'),
        ];
    }
}
