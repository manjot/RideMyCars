<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt #{{ $receipt->receipt_number }} — RideMyCars</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .brand-accent {
            color: #f59e0b;
        }
        .brand-sub {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .hero-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            padding: 15px;
            margin-bottom: 15px;
        }
        .hero-total {
            font-size: 32px;
            font-weight: 900;
            color: #111827;
            margin: 4px 0;
        }
        .hero-crn {
            font-size: 11px;
            font-weight: bold;
            color: #6b7280;
            font-family: monospace;
            letter-spacing: 0.5px;
        }
        .hero-greeting {
            font-size: 12px;
            color: #4b5563;
            margin-top: 6px;
        }
        .columns-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .col-left {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
            border-right: 1px solid #e5e7eb;
        }
        .col-right {
            width: 50%;
            vertical-align: top;
            padding-left: 12px;
        }
        .section-header {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .info-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
        }
        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            margin-top: 2px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-type {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
        .route-step {
            margin-bottom: 8px;
            font-size: 10px;
        }
        .dot-green {
            color: #10b981;
            font-weight: bold;
        }
        .dot-red {
            color: #ef4444;
            font-weight: bold;
        }
        .bill-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bill-table td {
            padding: 4px 0;
            font-size: 11px;
        }
        .bill-table .b-label {
            color: #6b7280;
            text-align: left;
        }
        .bill-table .b-val {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }
        .bill-discount {
            color: #10b981 !important;
            font-weight: bold !important;
        }
        .bill-total-row td {
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
            padding: 6px 0;
            font-weight: 900;
            font-size: 13px;
            color: #111827;
        }
        .payment-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 15px;
        }
        .qr-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
        }
        .footer-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            font-size: 9px;
            color: #6b7280;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                @php
                    $logoPath = public_path('images/logo.png');
                    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
                @endphp
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" alt="RideMyCars" style="height: 44px; width: auto; max-width: 200px; display: block; margin-bottom: 4px;" />
                @else
                    <div class="brand-title"><span class="brand-accent">Ride</span>MyCars</div>
                @endif
                <div class="brand-sub">Official Booking Receipt & Tax Invoice</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <span class="badge badge-type">{{ $receipt->type_label }}</span>
                <span class="badge badge-success">{{ strtoupper($receipt->payment_status) }}</span><br>
                <span style="font-size: 10px; color: #6b7280; display: inline-block; margin-top: 4px;">Date: {{ $receipt->created_at->format('d M Y, h:i A') }}</span>
            </td>
        </tr>
    </table>

    <!-- Hero / Big Price Banner -->
    <div class="hero-box">
        <div class="hero-total">{{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}</div>
        <div class="hero-crn">CRN / Receipt No: {{ $receipt->receipt_number }}</div>
        <div class="hero-greeting">
            Thanks for choosing RideMyCars, <strong>{{ $snapshot['customer_name'] ?? ($receipt->user->name ?? 'Valued Customer') }}</strong>!
        </div>
    </div>

    <!-- Main 2-Column Content -->
    <table class="columns-table">
        <tr>
            <!-- Left Column: Details -->
            <td class="col-left">
                <div class="section-header">{{ $receipt->type_label }} Details</div>

                @if(!empty($snapshot['driver_name']))
                    <div class="info-card">
                        <div class="info-label">
                            @if($receipt->booking_type === 'delivery') Courier Partner
                            @elseif($receipt->booking_type === 'driver_booking') Private Chauffeur
                            @else Assigned Driver
                            @endif
                        </div>
                        <div class="info-value">{{ $snapshot['driver_name'] }}</div>
                        @if(!empty($snapshot['driver_phone']))
                            <div style="font-size: 9px; color: #6b7280;">Contact: {{ $snapshot['driver_phone'] }}</div>
                        @endif
                    </div>
                @endif

                @if(!empty($snapshot['vehicle_title']))
                    <div class="info-card">
                        <div class="info-label">Assigned Vehicle</div>
                        <div class="info-value">{{ $snapshot['vehicle_title'] }}</div>
                        @if(!empty($snapshot['vehicle_plate']))
                            <div style="font-size: 9px; color: #6b7280;">License Plate: {{ $snapshot['vehicle_plate'] }}</div>
                        @endif
                    </div>
                @endif

                @if(!empty($snapshot['distance_km']) || !empty($snapshot['duration_minutes']))
                    <div class="info-card">
                        <div class="info-label">Trip Metrics</div>
                        <div class="info-value">
                            @if(!empty($snapshot['distance_km'])) {{ $snapshot['distance_km'] }} km @endif
                            @if(!empty($snapshot['distance_km']) && !empty($snapshot['duration_minutes'])) &bull; @endif
                            @if(!empty($snapshot['duration_minutes'])) {{ $snapshot['duration_minutes'] }} mins @endif
                        </div>
                    </div>
                @endif

                <div class="info-card">
                    <div class="info-label">Itinerary & Locations</div>
                    <div style="margin-top: 6px;">
                        <div class="route-step">
                            <span class="dot-green">&#9679; PICKUP:</span><br>
                            <span style="color: #374151;">{{ $snapshot['pickup_location'] ?? 'N/A' }}</span>
                            @if(!empty($snapshot['pickup_time']))
                                <br><span style="font-size: 9px; color: #9ca3af;">Time: {{ $snapshot['pickup_time'] }}</span>
                            @endif
                        </div>

                        <div class="route-step" style="margin-bottom: 0;">
                            <span class="dot-red">&#9679; DROPOFF:</span><br>
                            <span style="color: #374151;">{{ $snapshot['dropoff_location'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                @if(!empty($snapshot['package_category']))
                    <div class="info-card">
                        <div class="info-label">Package Specifications</div>
                        <div class="info-value">{{ $snapshot['package_category'] }} ({{ $snapshot['package_size'] ?? 'Standard' }})</div>
                        @if(!empty($snapshot['package_weight_kg']))
                            <div style="font-size: 9px; color: #6b7280;">Weight: {{ $snapshot['package_weight_kg'] }} kg</div>
                        @endif
                    </div>
                @endif
            </td>

            <!-- Right Column: Financial Bill Details -->
            <td class="col-right">
                <div class="section-header">Bill Details</div>

                <table class="bill-table">
                    <tr>
                        <td class="b-label">Base / Service Fare</td>
                        <td class="b-val">{{ $receipt->currency }} {{ number_format($receipt->subtotal, 2) }}</td>
                    </tr>

                    @if($receipt->discount_amount > 0)
                        <tr>
                            <td class="b-label">Special Discount</td>
                            <td class="b-val bill-discount">-{{ $receipt->currency }} {{ number_format($receipt->discount_amount, 2) }}</td>
                        </tr>
                    @endif

                    @if(!empty($snapshot['extras_fee']) && floatval($snapshot['extras_fee']) > 0)
                        <tr>
                            <td class="b-label">Selected Extras</td>
                            <td class="b-val">{{ $receipt->currency }} {{ number_format(floatval($snapshot['extras_fee']), 2) }}</td>
                        </tr>
                    @endif

                    @if(!empty($snapshot['protection_fee']) && floatval($snapshot['protection_fee']) > 0)
                        <tr>
                            <td class="b-label">Protection Coverage</td>
                            <td class="b-val">{{ $receipt->currency }} {{ number_format(floatval($snapshot['protection_fee']), 2) }}</td>
                        </tr>
                    @endif

                    @if($receipt->fee_amount > 0)
                        <tr>
                            <td class="b-label">Platform / Convenience Fee</td>
                            <td class="b-val">{{ $receipt->currency }} {{ number_format($receipt->fee_amount, 2) }}</td>
                        </tr>
                    @endif

                    @if($receipt->tax_amount > 0)
                        <tr>
                            <td class="b-label">Taxes & Levies (GST / VAT)</td>
                            <td class="b-val">{{ $receipt->currency }} {{ number_format($receipt->tax_amount, 2) }}</td>
                        </tr>
                    @endif

                    <tr class="bill-total-row">
                        <td class="b-label" style="font-weight: 900; color: #111827;">Total Amount Paid</td>
                        <td class="b-val" style="font-weight: 900; color: #111827;">{{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}</td>
                    </tr>
                </table>

                <div style="font-size: 9px; color: #9ca3af; margin-top: 10px; line-height: 1.3;">
                    * This document is issued at the passenger's / customer's request for reference and official reimbursement documentation.
                </div>

                <!-- Payment Summary Box -->
                <div class="payment-box" style="margin-top: 12px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-weight: bold; font-size: 11px;">
                                Paid by: {{ ucfirst($receipt->payment_method) }}
                            </td>
                            <td style="text-align: right; font-weight: 900; font-size: 12px; color: #10b981;">
                                {{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Digital Verification -->
                <div class="info-card">
                    <div class="info-label">Digital Verification</div>
                    <div style="font-size: 10px; font-family: monospace; color: #374151; word-break: break-all; margin-top: 2px;">
                        Token: {{ $receipt->verification_token }}
                    </div>
                    <div style="font-size: 8px; color: #9ca3af; margin-top: 2px;">
                        Verify online at: {{ $receipt->view_url }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer & Grievance Box -->
    <div class="footer-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 65%;">
                    <strong>In case of any complaint or grievance against this receipt:</strong><br>
                    Grievance Officer, {{ $snapshot['company_name'] ?? 'RideMyCars Inc.' }}<br>
                    {{ $snapshot['company_address'] ?? 'Corporate Headquarters' }}<br>
                    Email: {{ $snapshot['company_email'] ?? 'support@ridemycars.com' }} | Phone: {{ $snapshot['company_phone'] ?? '+1 (800) 555-RIDE' }}<br>
                    GSTIN / Tax ID: {{ $snapshot['company_gst_vat'] ?? 'N/A' }}
                </td>
                <td style="vertical-align: top; text-align: right; width: 35%;">
                    <strong>Booking ID:</strong> {{ $receipt->booking_id }}<br>
                    <strong>Customer ID:</strong> #{{ $receipt->user_id }}<br>
                    <span style="font-size: 8px; color: #9ca3af;">Computer generated document.</span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
