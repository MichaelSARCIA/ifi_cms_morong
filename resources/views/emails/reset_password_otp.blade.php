@extends('emails.main_layout')
@section('title', 'Reset Your Password')

@section('content')
    <h1 style="color: #1a202c; font-size: 24px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.5px;">
        Reset Your Password
    </h1>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 32px 0;">
        We received a request to reset the password for your account. Please use the following 6-digit verification code to proceed. This code is valid for <strong>15 minutes</strong>.
    </p>

    <!-- OTP Box -->
    <div style="background-color: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 32px; text-align: center; margin-bottom: 32px;">
        <span style="color: #2563eb; font-size: 42px; font-weight: 900; letter-spacing: 12px; font-family: 'Courier New', Courier, monospace;">
            {{ $otp }}
        </span>
    </div>

    <p style="color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 24px 0;">
        For security reasons, <strong>do not share this code</strong> with anyone. If you did not request a password reset, you can safely ignore this email.
    </p>

    <p style="color: #64748b; font-size: 15px; margin: 40px 0 0 0;">
        Regards,<br>
        <span style="color: #1a202c; font-weight: 700;">The {{ $settings['system_short_name'] ?? 'IFI CMS' }} Team</span>
    </p>
@endsection