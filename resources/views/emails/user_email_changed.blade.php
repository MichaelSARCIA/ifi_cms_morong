@extends('emails.main_layout')
@section('title', 'Account Security Alert')

@section('content')
    <h1 style="color: #1a202c; font-size: 24px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.5px; line-height: 1.2;">
        Security Notification: Email Updated
    </h1>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0;">
        Hello {{ $user->name }},<br><br>
        This is an automated security alert to inform you that the email address associated with your **{{ $settings['system_short_name'] ?? 'IFI CMS' }}** account has been changed.
    </p>

    <!-- Details Card -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 32px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                    <span style="display: block; font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">Previous Email</span>
                    <span style="display: block; font-size: 16px; color: #64748b; font-weight: 500; text-decoration: line-through;">{{ $oldEmail }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 12px;">
                    <span style="display: block; font-size: 11px; color: #2563eb; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">New Email Address</span>
                    <span style="display: block; font-size: 17px; color: #1e293b; font-weight: 700;">{{ $newEmail }}</span>
                </td>
            </tr>
        </table>
    </div>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0;">
        If you authorized this change, you can safely ignore this email. You should now use your <strong>new email address</strong> to log in to the system.
    </p>

    <div style="background-color: #fff7ed; border: 1px solid #ffedd5; border-radius: 12px; padding: 16px; margin-bottom: 32px;">
        <p style="color: #9a3412; font-size: 14px; line-height: 1.5; margin: 0;">
            <strong>Important:</strong> If you did <u>not</u> authorize this change, please contact your system administrator immediately to secure your account.
        </p>
    </div>

    <!-- Button -->
    <div style="text-align: center;">
        <a href="{{ config('app.url') }}" target="_blank" style="background-color: #2563eb; color: #ffffff; display: inline-block; padding: 16px 40px; border-radius: 12px; font-size: 16px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
            Log In to CMS
        </a>
    </div>

    <p style="color: #64748b; font-size: 15px; margin: 48px 0 0 0;">
        Regards,<br>
        <span style="color: #1a202c; font-weight: 700;">{{ $settings['system_short_name'] ?? 'IFI CMS' }} Security Team</span>
    </p>
@endsection
