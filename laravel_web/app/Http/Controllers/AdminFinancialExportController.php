<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFinancialExportController extends Controller
{
    /**
     * Export itemized financial statement as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = PaymentTransaction::with(['user', 'vehicle']);

        if ($request->filled('vertical')) {
            $query->where('service_vertical', $request->input('vertical'));
        }

        if ($request->filled('payout_status')) {
            $query->where('payout_status', $request->input('payout_status'));
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'financial_statement_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            
            // CSV Header Row
            fputcsv($handle, [
                'Transaction Date',
                'Ref ID',
                'Client Name',
                'Vehicle',
                'Service Vertical',
                'Gross Amount (GHS)',
                'Platform Fee (GHS)',
                'App Maintenance Fee (2.5% GHS)',
                'Gateway Fee (GHS)',
                'Net Owner Payout (GHS)',
                'Payment Status',
                'Payout Status',
                'Escrow Status',
            ]);

            foreach ($transactions as $txn) {
                fputcsv($handle, [
                    $txn->created_at ? $txn->created_at->format('Y-m-d H:i:s') : '',
                    $txn->transaction_ref,
                    $txn->user ? $txn->user->name : 'N/A',
                    $txn->vehicle ? "{$txn->vehicle->make} {$txn->vehicle->model} ({$txn->vehicle->license_plate})" : 'N/A',
                    $txn->service_vertical ?? 'RIDE_HAILING',
                    number_format((float) ($txn->gross_amount ?? $txn->amount), 2),
                    number_format((float) ($txn->platform_fee ?? 0), 2),
                    number_format((float) ($txn->maintenance_fee ?? 0), 2),
                    number_format((float) ($txn->gateway_fee ?? 0), 2),
                    number_format((float) ($txn->net_payout ?? 0), 2),
                    strtoupper($txn->status),
                    strtoupper($txn->payout_status ?? 'PENDING'),
                    strtoupper($txn->escrow_status ?? 'NONE'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
