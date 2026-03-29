@php
    $settings = \App\Models\SystemSetting::pluck('value', 'key')->toArray();
    $churchName = $settings['church_name'] ?? 'IGLESIA FILIPINA INDEPENDIENTE';
    $parishName = $settings['parish_name'] ?? 'Parokya ng San Geronimo';
    $parishAddress = $settings['church_address'] ?? 'Morong, Rizal';
    $dioceseName = $settings['diocese_name'] ?? 'Diocese of Rizal and Pampanga';
    $email = $settings['parish_email'] ?? 'sangeronimo.ifi@gmail.com';
    $contact = $settings['church_contact'] ?? '(02) 1234-5678';
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Official Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .logo-cell { width: 80px; text-align: left; }
        .header-text-cell { text-align: center; }

        .header img.logo {
            width: 70px;
            height: auto;
        }

        .church-title {
            font-family: 'Georgia', serif;
            margin: 0 0 5px 0;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }

        .diocese {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .parish-line {
            margin: 3px 0;
            font-size: 12px;
            color: #444;
        }

        .contact-line {
            margin: 3px 0;
            font-size: 11px;
            color: #666;
        }

        .receipt-number {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000;
        }

        table {
            width: 100%;
            margin: 20px 0;
        }

        .info-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .info-table td:first-child {
            font-weight: bold;
            width: 180px;
            color: #555;
        }

        .amount-box {
            background: #f0f9ff;
            border: 2px solid #1e40af;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .amount-box .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .amount-box .amount {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .signature {
            margin-top: 60px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin: 0 auto;
            padding-top: 5px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(200, 200, 200, 0.1);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="watermark">PAID</div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('assets/img/logo.png') }}" class="logo" alt="Logo">
                </td>
                <td class="header-text-cell">
                    <div class="church-title">{{ $churchName }}</div>
                    <div class="diocese">{{ $dioceseName }}</div>
                    <div class="parish-line">{{ $parishName }}</div>
                    <div class="parish-line">{{ $parishAddress }}</div>
                    <div class="contact-line">Contact: {{ $contact }} | Email: {{ $email }}</div>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>
    </div>

    <h2 style="text-align: center; color: #000; margin: 20px 0;">OFFICIAL RECEIPT</h2>

    <div class="receipt-number">
        Receipt No: {{ $payment->receipt_number }}
    </div>

    <table class="info-table">
        <tr>
            <td>Transaction No:</td>
            <td>TXN-{{ str_pad($payment->service_request_id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>Received From:</td>
            <td>{{ $payment->serviceRequest->applicant_name }}</td>
        </tr>
        <tr>
            <td>Service Type:</td>
            <td>{{ $payment->serviceRequest->service_type }}</td>
        </tr>
        <tr>
            <td>Scheduled Date:</td>
            <td>
                {{ $payment->serviceRequest->scheduled_date ? $payment->serviceRequest->scheduled_date->format('F d, Y') : 'TBD' }}
                @if($payment->serviceRequest->scheduled_time)
                    at {{ $payment->serviceRequest->scheduled_time }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Payment Method:</td>
            <td>{{ $payment->payment_method }}</td>
        </tr>
        @if($payment->reference_number)
            <tr>
                <td>Reference Number:</td>
                <td>{{ $payment->reference_number }}</td>
            </tr>
        @endif
        <tr>
            <td>Amount Paid:</td>
            <td style="font-weight: bold; font-size: 14px;">₱{{ number_format($payment->amount, 2) }}</td>
        </tr>
        @if($payment->payment_method === 'Cash' && $payment->amount_tendered)
            <tr>
                <td>Amount Tendered:</td>
                <td>₱{{ number_format($payment->amount_tendered, 2) }}</td>
            </tr>
            <tr>
                <td>Change:</td>
                <td>₱{{ number_format($payment->amount_tendered - $payment->amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td>Date Paid:</td>
            <td>{{ $payment->paid_at->format('F d, Y g:i A') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p style="margin: 10px 0;">This is an official receipt for payment received.</p>
        <p style="margin: 5px 0; font-size: 10px; color: #666;">Processed by: {{ $payment->processor->name }}</p>
        <p style="margin: 5px 0; font-size: 10px; color: #666;">Generated on: {{ now()->format('F d, Y g:i A') }}</p>
    </div>

    <div class="signature">
        <div class="signature-line">
            <strong>Authorized Signature</strong>
        </div>
    </div>
</body>

</html>