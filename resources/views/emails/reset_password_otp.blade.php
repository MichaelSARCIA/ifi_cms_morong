<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password OTP</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f9fafb; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f9fafb; width: 100%;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table border="0" cellspacing="0" cellpadding="0"
                    style="max-w-md; width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #2563eb; padding: 30px;">
                            <!-- Primary Color: #2563eb (Blue-600) to match the app theme -->
                            <h2
                                style="color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                                {{ $global_settings['system_name'] ?? 'IFI CMS' }}
                            </h2>
                            <p style="color: #bfdbfe; font-size: 14px; margin: 5px 0 0 0;">
                                {{ $global_settings['parish_name'] ?? 'Parokya ng San Geronimo' }}
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h1 style="color: #1f2937; font-size: 20px; font-weight: 700; margin: 0 0 15px 0;">Reset
                                Your Password</h1>

                            <p style="color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
                                We received a request to reset the password for your account. Please use the following
                                6-digit verification code to proceed. This code is valid for <strong>15
                                    minutes</strong>.
                            </p>

                            <!-- OTP Box -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <div
                                            style="background-color: #eff6ff; border: 2px dashed #93c5fd; border-radius: 12px; padding: 20px; display: inline-block;">
                                            <span
                                                style="color: #1e40af; font-size: 36px; font-weight: 800; letter-spacing: 8px;">
                                                {{ $otp }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
                                For security reasons, do not share this code with anyone. If you did not request a
                                password reset, you can safely ignore this email.
                            </p>

                            <p style="color: #6b7280; font-size: 14px; margin: 30px 0 0 0;">
                                Regards,<br>
                                <strong>The {{ $global_settings['system_short_name'] ?? 'IFI CMS' }} Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="background-color: #f3f4f6; padding: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                &copy; {{ date('Y') }}
                                {{ $global_settings['parish_name'] ?? 'Parokya ng San Geronimo' }}. All rights
                                reserved.<br>
                                This is an automated message, please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>