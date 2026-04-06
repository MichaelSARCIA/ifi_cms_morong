<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        /* Responsive styles */
        @media only screen and (max-width: 620px) {
            .container {
                width: 100% !important;
                padding: 10px !important;
            }
            .content-td {
                padding: 24px 16px !important;
            }
            .logo-img {
                max-width: 80px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f7fa; width: 100%;">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <!-- Main Wrapper -->
                <table class="container" border="0" cellspacing="0" cellpadding="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 32px 0 32px;">
                            @if(isset($settings['church_logo']) && $settings['church_logo'])
                                @php
                                    $church_logo = $settings['church_logo'];
                                    $logo_cid = null;
                                    
                                    // Robust path detection (checks public, public_html, and relative paths)
                                    $paths = [
                                        public_path('uploads/' . $church_logo),
                                        base_path('public_html/uploads/' . $church_logo),
                                        base_path('public/uploads/' . $church_logo),
                                        $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $church_logo,
                                    ];
                                    
                                    foreach ($paths as $path) {
                                        if (file_exists($path)) {
                                            $logo_cid = $message->embed($path);
                                            break;
                                        }
                                    }
                                @endphp

                                @if($logo_cid)
                                    <img src="{{ $logo_cid }}" alt="Church Logo" class="logo-img" style="width: 100px; height: auto; margin-bottom: 24px;">
                                @else
                                    <img src="{{ config('app.url') . '/uploads/' . $church_logo }}" alt="Church Logo" class="logo-img" style="width: 100px; height: auto; margin-bottom: 24px;">
                                @endif
                            @endif
                            <h2 style="color: #1a202c; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px; line-height: 1.2;">
                                {{ $settings['church_name'] ?? 'Iglesia Filipina Independiente' }}
                            </h2>
                            <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0; font-weight: 500;">
                                {{ $settings['parish_name'] ?? 'Parokya ng San Geronimo' }}
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-td" style="padding: 40px 48px;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 48px 48px 48px;">
                            <div style="border-top: 1px solid #f1f5f9; padding-top: 32px; text-align: center;">
                                <p style="color: #94a3b8; font-size: 13px; line-height: 1.6; margin: 0;">
                                    <strong>{{ $settings['parish_name'] ?? 'Parokya ng San Geronimo' }}</strong><br>
                                    {{ $settings['parish_address'] ?? '' }}<br>
                                    {{ $settings['parish_email'] ?? '' }}
                                </p>
                                <div style="margin-top: 24px;">
                                    <p style="color: #cbd5e1; font-size: 12px; margin: 0;">
                                        &copy; {{ date('Y') }} {{ str_replace(["\r", "\n"], ' ', $settings['system_name'] ?? 'Iglesia Filipina Independiente') }}, All rights reserved.
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <!-- Sub-footer -->
                <p style="color: #94a3b8; font-size: 11px; margin-top: 24px; text-align: center;">
                    This is an automated message. Please do not reply to this email.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
