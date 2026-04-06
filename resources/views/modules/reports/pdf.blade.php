<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportTitle ?? 'Report' }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #000; margin: 0; padding: 20px; font-size: 12px; }
        .church-title { font-family: 'Georgia', serif; font-size: 22px; font-weight: bold; text-transform: uppercase; margin: 0 0 2px 0; color: #000; }
        .diocese { font-size: 12px; font-weight: bold; color: #222; margin-bottom: 2px; }
        .parish-title { margin: 2px 0; font-size: 14px; color: #111; font-weight: bold; }
        .address-line { margin: 2px 0; font-size: 11px; color: #333; }
        .contact-line { margin: 2px 0; font-size: 11px; color: #555; }
        .report-title-section { text-align: center; margin-bottom: 30px; }
        .report-title { font-size: 16px; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin: 0; }
        .period { font-size: 12px; font-weight: bold; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { border-bottom: 1px solid #000; padding: 8px 4px; text-align: left; font-weight: bold; }
        td { border-bottom: 1px solid #ccc; padding: 8px 4px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .text-gray { color: #666; }
        .uppercase { text-transform: uppercase; }
        .footer { margin-top: 50px; text-align: right; font-size: 11px; }
    </style>
</head>
<body>

    <div style="margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px;">
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
            <tr>
                <td style="width: 90px; border: none; padding: 0; vertical-align: middle;">
                    @if(isset($logo) && $logo)
                        <img src="{{ public_path('uploads/' . $logo) }}" style="width: 85px; height: auto; display: block;">
                    @else
                        <img src="{{ public_path('assets/img/logo.png') }}" style="width: 85px; height: auto; display: block;">
                    @endif
                </td>
                <td style="border: none; padding: 0; vertical-align: middle; text-align: center;">
                    <div class="church-title">{{ $churchName }}</div>
                    <div class="diocese">{{ $dioceseName ?? 'Diocese of Rizal and Pampanga' }}</div>
                    <div class="parish-title">{{ $parishName }}</div>
                    <div class="address-line">{{ $address }}</div>
                    <div class="contact-line">
                        @if($contact)Contact: {{ $contact }}@endif
                        @if($contact && $parishEmail) | @endif
                        @if($parishEmail)Email: {{ $parishEmail }}@endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title-section">
        <p class="report-title">{{ $reportTitle }}</p>
        <p class="period">Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
    </div>

    @if($category === 'applicants')
        @if(isset($applicantList) && count($applicantList) > 0)
            <table style="font-size: 9px; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 18%;">Name of Recipient</th>
                        <th style="width: 14%;">Date of Birth & Age</th>
                        <th style="width: 14%;">Place of Birth</th>
                        <th style="width: 22%;">Parents' Names</th>
                        <th style="width: 16%;">Address</th>
                        <th style="width: 12%;">Service / Sacrament</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicantList as $applicant)
                    <tr>
                        <td style="text-align:center;"><strong>{{ $loop->iteration }}</strong></td>
                        <td><strong>{{ $applicant->recipient_name_formal }}</strong></td>
                        <td>{{ $applicant->recipient_dob_age }}</td>
                        <td>{{ $applicant->recipient_pob }}</td>
                        <td style="white-space: pre-line;">{{ $applicant->recipient_parents }}</td>
                        <td>{{ $applicant->recipient_address }}</td>
                        <td>{{ $applicant->service_type }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="italic text-center text-gray">No recipients found in this period.</p>
        @endif
    @endif

    @if($category === 'services')
        @if(count($serviceRequestsList) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 20%;">Date</th>
                        <th style="width: 25%;">Service Type</th>
                        <th style="width: 25%;">Requested By</th>
                        <th style="width: 25%;">Subject / Beneficiary</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($serviceRequestsList as $req)
                    <tr>
                        <td class="font-bold border-bottom" style="text-align: center;">{{ $loop->iteration }}.</td>
                        <td>{{ \Carbon\Carbon::parse($req->request_date)->format('F d, Y') }}</td>
                        <td>{{ $req->service_type }}</td>
                        <td class="font-bold">{{ $req->applicant_name }}</td>
                        <td class="font-bold">{{ $req->subject_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="italic text-center text-gray">No services requested in this period.</p>
        @endif
    @endif

    @if($category === 'collections')
        @if(count($collections) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 25%;">Type</th>
                        <th style="width: 35%;">Source/Event</th>
                        <th style="width: 20%;" class="text-right">Amount (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($collections as $col)
                    <tr>
                        <td class="font-bold">{{ $loop->iteration }}.</td>
                        <td>{{ \Carbon\Carbon::parse($col->date_received)->format('F d, Y') }}</td>
                        <td style="color: #444;">{{ $col->type }}</td>
                        <td class="font-bold">
                            {{ $col->remarks }}
                            @if(isset($col->notes) && $col->notes)
                                <br><span style="font-weight:normal; font-style:italic; font-size:10px; color:#555;">{{ $col->notes }}</span>
                            @endif
                        </td>
                        <td class="text-right">PHP {{ number_format($col->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="italic text-center text-gray">No collections recorded in this period.</p>
        @endif
    @endif

    @if($category === 'donations')
        @if(count($donations) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 25%;">Donor Name</th>
                        <th style="width: 25%;">Fund / Purpose</th>
                        <th style="width: 15%;">Mode of Payment</th>
                        <th style="width: 15%;" class="text-right">Amount (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donations as $don)
                    <tr>
                        <td class="font-bold">{{ $loop->iteration }}.</td>
                        <td>{{ \Carbon\Carbon::parse($don->date_received)->format('F d, Y') }}</td>
                        <td class="font-bold">{{ $don->donor_name ?? 'Anonymous' }}</td>
                        <td>{{ $don->type ?? '—' }}</td>
                        <td>{{ $don->payment_method ?? '—' }}</td>
                        <td class="text-right">PHP {{ number_format($don->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="italic text-center text-gray">No donations received in this period.</p>
        @endif
    @endif

    @if($category === 'fees')
        @if(count($serviceFees) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 20%;">Date Paid</th>
                        <th style="width: 30%;">Service Type</th>
                        <th style="width: 25%;">Payor</th>
                        <th style="width: 20%;" class="text-right">Amount (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($serviceFees as $fee)
                    <tr>
                        <td class="font-bold">{{ $loop->iteration }}.</td>
                        <td>{{ \Carbon\Carbon::parse($fee->paid_at)->format('F d, Y') }}</td>
                        <td>{{ $fee->service_type }}</td>
                        <td class="font-bold">{{ \App\Models\ServiceRequest::formatValue($fee->payor_name) }}</td>
                        <td class="text-right">PHP {{ number_format($fee->amount_paid, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="italic text-center text-gray">No service fees processed in this period.</p>
        @endif
    @endif

    <div class="footer">
        <p>Printed By: <strong>{{ auth()->user()->name ?? 'System Admin' }}</strong></p>
        <p>Date Printed: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

</body>
</html>