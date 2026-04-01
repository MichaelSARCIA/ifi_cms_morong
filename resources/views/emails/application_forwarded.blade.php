@extends('emails.main_layout')
@section('title', 'Service Application Forwarded')

@section('content')
    <h1 style="color: #1a202c; font-size: 24px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.5px; line-height: 1.2;">
        Service Application Review
    </h1>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0;">
        Dear Priest,<br><br>
        A new service application has been forwarded to you for your review and approval.
    </p>

    <!-- Details Card -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 32px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                    <span style="display: block; font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">Service Type</span>
                    <span style="display: block; font-size: 16px; color: #1e293b; font-weight: 700;">{{ $serviceRequest->service_type }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="display: block; font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">Requested Schedule</span>
                    <span style="display: block; font-size: 16px; color: #1e293b; font-weight: 700;">
                        {{ \Carbon\Carbon::parse($serviceRequest->scheduled_date)->format('F d, Y') }} 
                        @if($serviceRequest->scheduled_time)
                            at {{ date('h:i A', strtotime($serviceRequest->scheduled_time)) }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="display: block; font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">Applicant Name</span>
                    <span style="display: block; font-size: 16px; color: #1e293b; font-weight: 700;">{{ $serviceRequest->first_name }} {{ $serviceRequest->last_name }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 12px;">
                    <span style="display: block; font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">Contact</span>
                    <span style="display: block; font-size: 16px; color: #1e293b; font-weight: 700;">{{ $serviceRequest->contact_number ?? 'N/A' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 32px 0;">
        Please log in to the {{ $settings['system_short_name'] ?? 'IFI CMS' }} to review the application and provide your decision.
    </p>

    <!-- Button -->
    <div style="text-align: center;">
        <a href="{{ config('app.url') }}" target="_blank" style="background-color: #2563eb; color: #ffffff; display: inline-block; padding: 16px 40px; border-radius: 12px; font-size: 16px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
            Log In to CMS
        </a>
    </div>

    <p style="color: #64748b; font-size: 15px; margin: 48px 0 0 0;">
        Regards,<br>
        <span style="color: #1a202c; font-weight: 700;">{{ $settings['system_short_name'] ?? 'IFI CMS' }} Administration</span>
    </p>
@endsection