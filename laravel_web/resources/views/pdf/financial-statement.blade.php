<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RideMyCars — Enterprise Financial Statement</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 30px; color: #111; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ea580c; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 900; color: #ea580c; text-transform: uppercase; letter-spacing: -0.5px; }
        .subtitle { font-size: 11px; color: #666; font-weight: bold; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; }
        .stat-item { text-align: left; }
        .stat-label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .stat-value { font-size: 16px; font-weight: 900; color: #0f172a; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #0f172a; color: #fff; text-align: left; padding: 10px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 10px 8px; font-size: 11px; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-failed { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 15px; font-size: 10px; color: #94a3b8; display: flex; justify-content: space-between; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="background: #ea580c; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">
            🖨️ Print / Save as PDF
        </button>
    </div>

    <div class="header">
        <div>
            <div class="logo">RideMyCars</div>
            <div class="subtitle">Enterprise Owner and Platform Financial Statement</div>
        </div>
        <div style="text-align: right;">
            <strong>Statement Date:</strong> {{ date('F d, Y') }}<br>
            <span style="color: #64748b;">Generated at: {{ date('H:i:s T') }}</span>
        </div>
    </div>

    <div class="summary-box">
        <div class="stat-item">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value">{{ count($transactions) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Gross Passenger Fares</div>
            <div class="stat-value">GH₵ {{ number_format($totalGross, 2) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Platform Share</div>
            <div class="stat-value" style="color: #ea580c;">GH₵ {{ number_format($totalPlatform, 2) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Net Fleet Owner Payout</div>
            <div class="stat-value" style="color: #16a34a;">GH₵ {{ number_format($totalNet, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date & Ref</th>
                <th>Participant / Client</th>
                <th>Vehicle</th>
                <th>Vertical</th>
                <th class="text-right">Gross (GHS)</th>
                <th class="text-right">Platform Fee</th>
                <th class="text-right">Maint. Fee</th>
                <th class="text-right">Net Owner (GHS)</th>
                <th>Payout Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr>
                    <td>
                        <strong>{{ $txn->transaction_ref }}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">{{ $txn->created_at ? $txn->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                    </td>
                    <td>{{ $txn->user ? $txn->user->name : 'N/A' }}</td>
                    <td>{{ $txn->vehicle ? ($txn->vehicle->make . ' ' . $txn->vehicle->model) : 'N/A' }}</td>
                    <td><strong>{{ $txn->service_vertical ?? 'RIDE_HAILING' }}</strong></td>
                    <td class="text-right"><strong>{{ number_format((float) ($txn->gross_amount ?? $txn->amount), 2) }}</strong></td>
                    <td class="text-right" style="color: #ea580c;">{{ number_format((float) ($txn->platform_fee ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($txn->maintenance_fee ?? 0), 2) }}</td>
                    <td class="text-right" style="color: #16a34a;"><strong>{{ number_format((float) ($txn->net_payout ?? 0), 2) }}</strong></td>
                    <td>
                        @php
                            $st = strtolower($txn->payout_status ?? 'pending');
                            $badgeClass = match($st) {
                                'completed', 'paid' => 'badge-success',
                                'failed' => 'badge-failed',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ strtoupper($st) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #94a3b8; padding: 20px;">No financial transactions found for the selected filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>RideMyCars Enterprise Logistics & Fleet Management System</div>
        <div>Confidential & Proprietary • Page 1 of 1</div>
    </div>

</body>
</html>
