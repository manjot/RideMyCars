<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt for {{ $receipt->receipt_number }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f3f4f6; padding: 30px 10px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { padding: 24px 30px 10px 30px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .brand-title { font-size: 24px; font-weight: 900; color: #111827; letter-spacing: -0.5px; text-decoration: none; }
        .brand-accent { color: #f59e0b; }
        .date-text { font-size: 13px; color: #6b7280; font-weight: 500; text-align: left; }
        .hero { text-align: center; padding: 15px 20px 25px 20px; border-bottom: 1px solid #f3f4f6; }
        .total-price { font-size: 40px; font-weight: 900; color: #111827; margin: 8px 0 4px 0; }
        .receipt-code { font-size: 13px; font-weight: 700; color: #6b7280; letter-spacing: 0.5px; font-family: monospace; }
        .greeting { font-size: 14px; color: #4b5563; margin-top: 10px; font-weight: 500; }
        .content-table { width: 100%; border-collapse: collapse; padding: 20px 24px; }
        .section-title { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #111827; padding-bottom: 12px; border-bottom: 2px solid #f3f4f6; margin-bottom: 14px; }
        .details-col { width: 50%; vertical-align: top; padding: 20px 15px; }
        .details-col-left { padding-right: 15px; border-right: 1px solid #f3f4f6; }
        .details-col-right { padding-left: 15px; }
        .driver-card { display: table; width: 100%; margin-bottom: 14px; background: #f9fafb; padding: 10px; border-radius: 10px; }
        .driver-avatar { width: 44px; height: 44px; border-radius: 50%; background: #e5e7eb; text-align: center; line-height: 44px; font-size: 18px; font-weight: bold; color: #374151; display: inline-block; vertical-align: middle; }
        .driver-info { display: inline-block; vertical-align: middle; margin-left: 10px; }
        .driver-name { font-size: 13px; font-weight: 700; color: #111827; }
        .driver-role { font-size: 11px; color: #6b7280; }
        .meta-pill { font-size: 11px; font-weight: 600; color: #4b5563; margin-bottom: 6px; }
        .route-stop { margin-bottom: 12px; font-size: 12px; line-height: 1.4; }
        .route-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .dot-green { background-color: #10b981; }
        .dot-red { background-color: #ef4444; }
        .bill-row { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .bill-label { font-size: 12px; color: #6b7280; text-align: left; padding: 4px 0; }
        .bill-val { font-size: 12px; font-weight: 600; color: #111827; text-align: right; padding: 4px 0; }
        .bill-discount { color: #10b981; font-weight: 700; }
        .bill-total-row { border-top: 2px solid #e5e7eb; border-bottom: 2px solid #e5e7eb; padding: 8px 0; margin-top: 10px; }
        .bill-total-label { font-size: 14px; font-weight: 800; color: #111827; }
        .bill-total-val { font-size: 16px; font-weight: 900; color: #111827; }
        .payment-banner { background-color: #f9fafb; border-radius: 12px; padding: 14px 20px; margin: 10px 24px 20px 24px; border: 1px solid #e5e7eb; }
        .payment-table { width: 100%; border-collapse: collapse; }
        .payment-label { font-size: 13px; font-weight: 700; color: #111827; }
        .payment-amount { font-size: 14px; font-weight: 800; color: #10b981; text-align: right; }
        .actions-section { text-align: center; padding: 15px 24px 25px 24px; }
        .btn-primary { display: inline-block; background-color: #111827; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 13px; margin: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .btn-secondary { display: inline-block; background-color: #f59e0b; color: #111827 !important; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 800; font-size: 13px; margin: 5px; }
        .footer { background-color: #f9fafb; padding: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; line-height: 1.6; text-align: center; }
        .footer a { color: #f59e0b; text-decoration: none; font-weight: 600; }
        .grievance-box { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-top: 16px; font-size: 10px; text-align: left; color: #6b7280; }
        @media only screen and (max-width: 600px) {
            .details-col { width: 100% !important; display: block !important; padding: 10px 0 !important; }
            .details-col-left { border-right: none !important; border-bottom: 1px solid #f3f4f6 !important; }
            .total-price { font-size: 32px !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            
            <!-- Top Bar -->
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td class="date-text">{{ $receipt->created_at->format('d M, Y') }}</td>
                        <td style="text-align: right;">
                            <a href="{{ url('/') }}" class="brand-title">
                                <span class="brand-accent">Ride</span>MyCars
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Price Hero -->
            <div class="hero">
                <div class="total-price">{{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}</div>
                <div class="receipt-code">CRN: {{ $receipt->receipt_number }}</div>
                <div class="greeting">
                    Thanks for choosing RideMyCars, <strong>{{ $snapshot['customer_name'] ?? ($receipt->user->name ?? 'Valued Customer') }}</strong>!
                </div>
            </div>

            <!-- Details Table -->
            <table class="content-table" style="width: 100%;">
                <tr>
                    <!-- Left: Trip / Booking Details -->
                    <td class="details-col details-col-left">
                        <div class="section-title">{{ $receipt->type_label }} Details</div>

                        @if(!empty($snapshot['driver_name']))
                            <div class="driver-card">
                                <div class="driver-avatar">
                                    {{ strtoupper(substr($snapshot['driver_name'], 0, 1)) }}
                                </div>
                                <div class="driver-info">
                                    <div class="driver-name">{{ $snapshot['driver_name'] }}</div>
                                    <div class="driver-role">
                                        @if($receipt->booking_type === 'delivery') Courier Partner
                                        @elseif($receipt->booking_type === 'driver_booking') Private Chauffeur
                                        @else Driver Partner
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($snapshot['vehicle_title']))
                            <div class="meta-pill">
                                🚗 <strong>Vehicle:</strong> {{ $snapshot['vehicle_title'] }}
                                @if(!empty($snapshot['vehicle_plate']))
                                    ({{ $snapshot['vehicle_plate'] }})
                                @endif
                            </div>
                        @endif

                        @if(!empty($snapshot['distance_km']) || !empty($snapshot['duration_minutes']))
                            <div class="meta-pill">
                                ⏱️ 
                                @if(!empty($snapshot['distance_km'])) {{ $snapshot['distance_km'] }} km @endif
                                @if(!empty($snapshot['distance_km']) && !empty($snapshot['duration_minutes'])) &bull; @endif
                                @if(!empty($snapshot['duration_minutes'])) {{ $snapshot['duration_minutes'] }} mins @endif
                            </div>
                        @endif

                        <div style="margin-top: 14px;">
                            <div class="route-stop">
                                <span class="route-dot dot-green"></span>
                                <strong>Pickup:</strong><br>
                                <span style="color: #4b5563;">{{ $snapshot['pickup_location'] ?? 'Pickup Point' }}</span>
                            </div>

                            <div class="route-stop">
                                <span class="route-dot dot-red"></span>
                                <strong>Dropoff:</strong><br>
                                <span style="color: #4b5563;">{{ $snapshot['dropoff_location'] ?? 'Destination' }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- Right: Bill Details -->
                    <td class="details-col details-col-right">
                        <div class="section-title">Bill Details</div>

                        <table style="width: 100%;">
                            <tr>
                                <td class="bill-label">Base / Fare</td>
                                <td class="bill-val">{{ $receipt->currency }} {{ number_format($receipt->subtotal, 2) }}</td>
                            </tr>

                            @if($receipt->discount_amount > 0)
                                <tr>
                                    <td class="bill-label">Discount Applied</td>
                                    <td class="bill-val bill-discount">-{{ $receipt->currency }} {{ number_format($receipt->discount_amount, 2) }}</td>
                                </tr>
                            @endif

                            @if(!empty($snapshot['extras_fee']) && floatval($snapshot['extras_fee']) > 0)
                                <tr>
                                    <td class="bill-label">Selected Extras</td>
                                    <td class="bill-val">{{ $receipt->currency }} {{ number_format(floatval($snapshot['extras_fee']), 2) }}</td>
                                </tr>
                            @endif

                            @if(!empty($snapshot['protection_fee']) && floatval($snapshot['protection_fee']) > 0)
                                <tr>
                                    <td class="bill-label">Protection Coverage</td>
                                    <td class="bill-val">{{ $receipt->currency }} {{ number_format(floatval($snapshot['protection_fee']), 2) }}</td>
                                </tr>
                            @endif

                            @if($receipt->fee_amount > 0)
                                <tr>
                                    <td class="bill-label">Platform / Convenience Fee</td>
                                    <td class="bill-val">{{ $receipt->currency }} {{ number_format($receipt->fee_amount, 2) }}</td>
                                </tr>
                            @endif

                            @if($receipt->tax_amount > 0)
                                <tr>
                                    <td class="bill-label">Taxes & Levies (GST / VAT)</td>
                                    <td class="bill-val">{{ $receipt->currency }} {{ number_format($receipt->tax_amount, 2) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="2" style="padding-top: 10px;">
                                    <table style="width: 100%; border-top: 2px solid #e5e7eb; padding-top: 8px;">
                                        <tr>
                                            <td class="bill-total-label">Total Bill (Paid)</td>
                                            <td class="bill-total-val" style="text-align: right;">{{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="font-size: 11px; color: #9ca3af; margin-top: 14px; line-height: 1.4;">
                            Have queries? Visit <a href="{{ url('/contact') }}" style="color: #f59e0b; font-weight: bold; text-decoration: none;">RideMyCars Support</a> for assistance with this booking.
                        </p>
                    </td>
                </tr>
            </table>

            <!-- Payment Confirmation Bar -->
            <div class="payment-banner">
                <table class="payment-table">
                    <tr>
                        <td class="payment-label">
                            💳 Paid by {{ ucfirst($receipt->payment_method) }}
                            <span style="font-size: 10px; color: #10b981; font-weight: 800; text-transform: uppercase; background: #ecfdf5; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">COMPLETED</span>
                        </td>
                        <td class="payment-amount">{{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="actions-section">
                <a href="{{ $receipt->download_url }}" class="btn-primary">
                    📥 Download PDF Receipt
                </a>
                <a href="{{ $receipt->view_url }}" class="btn-secondary">
                    🌐 View Online Receipt
                </a>
            </div>

            <!-- Footer & Grievance Box -->
            <div class="footer">
                <p style="margin: 0 0 8px 0;">
                    For Terms & Conditions and fare policies, visit our website: <a href="{{ url('/terms') }}">{{ url('/') }}</a>.
                </p>
                <p style="margin: 0 0 12px 0;">
                    Didn't make this booking? <a href="{{ url('/contact') }}">Report it immediately</a>.
                </p>

                <div class="grievance-box">
                    <strong>Corporate & Grievance Officer:</strong><br>
                    {{ $snapshot['company_name'] ?? 'RideMyCars Inc.' }}<br>
                    {{ $snapshot['company_address'] ?? 'Corporate Headquarters & Operations' }}<br>
                    Email: {{ $snapshot['company_email'] ?? 'support@ridemycars.com' }} | Phone: {{ $snapshot['company_phone'] ?? '+1 (800) 555-RIDE' }}<br>
                    GST / VAT Registration: {{ $snapshot['company_gst_vat'] ?? 'N/A' }}
                </div>

                <p style="font-size: 10px; color: #9ca3af; margin-top: 14px;">
                    This is an electronically generated receipt. A secure copy of your official PDF receipt is also attached to this email.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
