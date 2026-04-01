@php
    $settings = \App\Models\SystemSetting::pluck('value', 'key')->toArray();
    $churchName = $settings['church_name'] ?? 'IGLESIA FILIPINA INDEPENDIENTE';
    $parishName = $settings['parish_name'] ?? 'Parokya ng San Geronimo';
    $parishAddress = $settings['church_address'] ?? 'Morong, Rizal';
    $dioceseName = $settings['diocese_name'] ?? 'Diocese of Rizal and Pampanga';
    $priestName = $settings['church_priest'] ?? '___________________________';
    
    $rawServiceType = $service->service_type ?? 'Service';
    $serviceType = strtolower($rawServiceType);
    $serviceTypeName = ucwords($rawServiceType);

    $isBaptism = str_contains($serviceType, 'baptism');
    $isWedding = str_contains($serviceType, 'wedding') || str_contains($serviceType, 'matrimony') || str_contains($serviceType, 'marriage');
    $isConfirmation = str_contains($serviceType, 'confirmation');
    $isBurial = str_contains($serviceType, 'burial') || str_contains($serviceType, 'funeral');
    $isWake = str_contains($serviceType, 'wake');

    $title = $serviceTypeName . ' Certificate';
    if($isBaptism) $title = 'Baptismal Certificate';
    elseif($isWedding) $title = 'Marriage Certificate';
    elseif($isBurial) $title = 'Christian Burial Certificate';

    $fullName = strtoupper($service->applicant_name);
    
    if($isWedding) {
        $customData = $service->filtered_custom_data;
        $groomName = $fullName;
        $brideName = strtoupper(trim(
            ($customData['bride_first_name'] ?? $customData['brides_first_name'] ?? $customData['Bride First Name'] ?? '') . ' ' .
            ($customData['bride_middle_name'] ?? $customData['brides_middle_name'] ?? $customData['Bride Middle Name'] ?? '') . ' ' .
            ($customData['bride_last_name'] ?? $customData['brides_last_name'] ?? $customData['Bride Last Name'] ?? '')
        ));
        if(!$brideName) $brideName = strtoupper($customData['bride_name'] ?? $customData['brides_name'] ?? $customData["Bride's Name"] ?? '___________________________');
        $recipientName = $groomName . ' & ' . $brideName;
    } else {
        $recipientName = $fullName;
    }

    $receivedText = 'Received the ' . $serviceTypeName;
    if($isBaptism) $receivedText = 'Received the Sacrament of Baptism';
    elseif($isWedding) $receivedText = 'Received the Holy Matrimony';
    elseif($isConfirmation) $receivedText = 'Received the Sacrament of Confirmation';
    elseif($isBurial) $receivedText = 'Received the Rites of Christian Burial';
    elseif($isWake) $receivedText = 'Received the Wake Service';

    $witnessType = $serviceTypeName;
    if($isBaptism) $witnessType = 'Baptism';
    elseif($isWedding) $witnessType = 'Marriage';
    elseif($isBurial) $witnessType = 'Christian Burial';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;1,400&family=Poppins:wght@400;500;600;700&display=swap');
        
        @page { size: A4 portrait; margin: 40px; }
        body { 
            font-family: 'Poppins', sans-serif; 
            color: #1f2937; /* Gray-800 */
            margin: 0; 
            padding: 0; 
        }
        
        /* Themed Borders */
        .border { position: absolute; top: 0; left: 0; bottom: 0; right: 0; border: 3px solid #3B82F6; z-index: -2; }
        .inner-border { position: absolute; top: 6px; left: 6px; bottom: 6px; right: 6px; border: 1px solid #93C5FD; z-index: -1; }
        
        /* Watermark */
        .watermark { position: absolute; top: 30%; left: 15%; width: 70%; opacity: 0.05; z-index: -3; }
        
        .header-table { width: 100%; margin-top: 20px; margin-bottom: 20px; }
        .logo-cell { width: 100px; text-align: center; vertical-align: top; }
        .logo { width: 90px; height: auto; }
        .header-text-cell { text-align: center; vertical-align: top; }
        
        .church-title { 
            font-family: 'Merriweather', serif; 
            font-size: 18pt; 
            font-weight: bold; 
            color: #111827; /* Black */
            margin-bottom: 4px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .diocese { 
            font-size: 11pt; 
            font-weight: 600; 
            margin-bottom: 12px; 
            color: #374151; /* Gray-700 */
            text-transform: uppercase;
        }
        
        .parish-line { 
            border-bottom: 1px solid #d1d5db; 
            display: inline-block; 
            width: 70%; 
            font-weight: 600; 
            font-size: 12pt; 
            margin-top: 4px; 
            color: #111827;
        }
        .label { 
            font-size: 8pt; 
            color: #6b7280; 
            margin-bottom: 8px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        
        .cert-title { 
            font-family: 'Merriweather', serif; 
            font-size: 30pt; 
            font-weight: bold; 
            color: #3B82F6; /* Primary */
            margin: 30px 0 10px 0; 
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
        }
        .preamble { 
            font-family: 'Merriweather', serif;
            font-size: 11pt; 
            font-style: italic; 
            text-align: center; 
            color: #4b5563; 
            margin-bottom: 40px; 
        }
        
        .body-text { 
            text-align: center; 
            font-size: 11pt; 
            margin-bottom: 15px; 
            color: #6b7280; 
            letter-spacing: 1px;
            font-weight: 500;
        }
        
        .text-center { text-align: center; }

        .recipient { 
            font-family: 'Merriweather', serif;
            font-size: 20pt; 
            font-weight: bold; 
            color: #111827;
            border-bottom: 2px solid #3B82F6; 
            display: inline-block; 
            padding: 0 30px; 
            margin: 5px 0 15px 0; 
        }
        
        .received { 
            font-size: 16pt; 
            font-weight: 600; 
            color: #374151;
            margin: 20px 0; 
            text-align: center; 
        }
        
        .date-line { 
            text-align: center; 
            font-size: 11pt; 
            margin-top: 40px; 
            line-height: 2.2; 
        }
        
        .witness-container { padding: 0 20px; }
        .witness { 
            text-align: center; 
            font-size: 11pt; 
            margin-top: 35px; 
            line-height: 2.2;
        }
        .underline { 
            border-bottom: 1px solid #1f2937; 
            display: inline-block; 
            padding: 0 15px; 
            font-weight: 600; 
            color: #111827;
        }
        
        .footer { 
            position: absolute; 
            bottom: 40px; 
            left: 40px; 
            right: 40px; 
        }
        .ledger { 
            float: left; 
            width: 45%; 
            padding-top: 15px;
        }
        .ledger-item { 
            margin-bottom: 6px; 
            font-size: 10pt; 
            color: #4b5563;
        }
        .ledger-label { 
            display: inline-block; 
            width: 80px; 
            font-weight: 500;
        }
        .ledger-value { 
            border-bottom: 1px solid #d1d5db; 
            display: inline-block; 
            width: 140px; 
        }
        
        .signature { 
            float: right; 
            width: 45%; 
            text-align: center; 
            margin-top: 5px; 
        }
        .priest-name { 
            font-family: 'Merriweather', serif;
            font-weight: bold; 
            font-size: 13pt; 
            color: #111827;
            border-bottom: 1px solid #1f2937; 
            display: inline-block; 
            width: 100%; 
            padding-bottom: 4px; 
        }
        .priest-label { 
            font-size: 10pt; 
            color: #6b7280; 
            margin-top: 6px; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="border"></div>
    <div class="inner-border"></div>
    <img src="{{ public_path('assets/img/logo.png') }}" class="watermark">
    
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('assets/img/logo.png') }}" class="logo">
            </td>
            <td class="header-text-cell">
                <div class="church-title">{{ $churchName }}</div>
                <div class="diocese">{{ $dioceseName }}</div>
                
                <div class="parish-line">{{ $parishName }}</div><br>
                <div class="label">Parish/Mission</div>
                
                <div class="parish-line">{{ $parishAddress }}</div><br>
                <div class="label">Address</div>
            </td>
            <td class="logo-cell">
                <!-- Empty for spacing balance -->
            </td>
        </tr>
    </table>

    <div class="cert-title">{{ $title }}</div>
    <div class="preamble">In the name of the Father and of the Son and of the Holy Spirit. Amen.</div>

    <div class="body-text">THIS IS TO CERTIFY THAT</div>
    <div class="text-center">
        <div class="recipient">{{ $recipientName }}</div>
    </div>
    
    <div class="received">{{ $receivedText }}</div>

    <div class="date-line">
        On this <span class="underline">{{ $service->scheduled_date->format('jS') }}</span> day of 
        <span class="underline">{{ $service->scheduled_date->format('F') }}</span> in the year 
        <span class="underline">{{ $service->scheduled_date->format('Y') }}</span> in this Parish.
    </div>

    <div class="witness-container">
        <div class="witness">
            IN WITNESS THEREOF, I do sign this Certificate of {{ $witnessType }} this 
            <span class="underline">{{ $certificate->issued_at->format('jS') }}</span> day of 
            <span class="underline">{{ $certificate->issued_at->format('F') }}</span> <span class="underline">{{ $certificate->issued_at->format('Y') }}</span>.
        </div>
    </div>

    <div class="footer">
        <div class="ledger">
            <div class="ledger-item"><span class="ledger-label">Book No.</span><span class="ledger-value"></span></div>
            <div class="ledger-item"><span class="ledger-label">Page No.</span><span class="ledger-value"></span></div>
            <div class="ledger-item"><span class="ledger-label">Series of</span><span class="ledger-value"></span></div>
            <div class="ledger-item"><span class="ledger-label">Purpose:</span><span class="ledger-value"></span></div>
        </div>
        
        <div class="signature">
            <div class="priest-name">{{ $priestName }}</div>
            <div class="priest-label">Parish Priest</div>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>