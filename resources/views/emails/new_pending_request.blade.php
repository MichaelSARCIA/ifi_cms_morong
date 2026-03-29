<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>New Pending Service Request</title>
    <style>
        /* Responsive styles for smaller screens */
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }

            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 16px !important;
            }

            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important;
            }

            table[class=body] .content {
                padding: 0 !important;
            }

            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important;
            }

            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            table[class=body] .btn table {
                width: 100% !important;
            }

            table[class=body] .btn a {
                width: 100% !important;
            }

            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }
    </style>
</head>

<body
    style="background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; font-size: 15px; line-height: 1.6; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body"
        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f3f4f6; width: 100%;">
        <tr>
            <td
                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; vertical-align: top;">
                &nbsp;</td>
            <td class="container"
                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; vertical-align: top; display: block; max-width: 600px; padding: 20px; width: 600px; margin: 0 auto;">
                <div class="content"
                    style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; padding: 10px;">

                    <!-- START CENTERED WHITE CONTAINER -->
                    <table role="presentation" class="main"
                        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); width: 100%;">
                        <tr>
                            <td class="wrapper"
                                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; vertical-align: top; box-sizing: border-box; padding: 32px;">

                                <div style="text-align: center; margin-bottom: 24px;">
                                    <div
                                        style="background-color: #eff6ff; color: #3b82f6; display: inline-block; padding: 12px; border-radius: 12px; margin-bottom: 16px;">
                                        <h2
                                            style="margin: 0; font-size: 20px; font-weight: bold; align-items: center; letter-spacing: -0.5px;">
                                            IFI CMS</h2>
                                    </div>
                                </div>

                                <h2
                                    style="color: #111827; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; line-height: 1.3; margin: 0 0 16px; text-align: center;">
                                    New Pending Service Request</h2>

                                <p
                                    style="color: #4b5563; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; margin: 0 0 16px;">
                                    Dear Priest,</p>
                                <p
                                    style="color: #4b5563; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; margin: 0 0 24px;">
                                    A new pending service application has been submitted. Please review the details below.</p>

                                <!-- Details Card -->
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                    style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f9fafb; border-radius: 12px; border: 1px solid #f3f4f6; width: 100%; margin-bottom: 24px;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                                style="width: 100%;">
                                                <tr>
                                                    <td style="padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                                                        <span
                                                            style="display: block; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; tracking: 0.05em; margin-bottom: 2px;">Service
                                                            Type</span>
                                                        <span
                                                            style="display: block; font-size: 15px; color: #111827; font-weight: 500;">{{ $serviceRequest->service_type }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                        <span
                                                            style="display: block; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; tracking: 0.05em; margin-bottom: 2px;">Requested
                                                            Date</span>
                                                        <span
                                                            style="display: block; font-size: 15px; color: #111827; font-weight: 500;">{{ \Carbon\Carbon::parse($serviceRequest->scheduled_date)->format('F d, Y') }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                        <span
                                                            style="display: block; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; tracking: 0.05em; margin-bottom: 2px;">Requested
                                                            Time</span>
                                                        <span
                                                            style="display: block; font-size: 15px; color: #111827; font-weight: 500;">{{ $serviceRequest->scheduled_time ? date('h:i A', strtotime($serviceRequest->scheduled_time)) : 'N/A' }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                        <span
                                                            style="display: block; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; tracking: 0.05em; margin-bottom: 2px;">Applicant
                                                            Name</span>
                                                        <span
                                                            style="display: block; font-size: 15px; color: #111827; font-weight: 500;">{{ $serviceRequest->first_name }}
                                                            {{ $serviceRequest->last_name }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-top: 12px;">
                                                        <span
                                                            style="display: block; font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; tracking: 0.05em; margin-bottom: 2px;">Contact</span>
                                                        <span
                                                            style="display: block; font-size: 15px; color: #111827; font-weight: 500;">{{ $serviceRequest->contact_number ?? 'N/A' }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <p
                                    style="color: #4b5563; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; margin: 0 0 24px;">
                                    Please log in to the CMS to review the details and either approve the request or
                                    suggest a new schedule.</p>

                                <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                    class="btn btn-primary"
                                    style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; box-sizing: border-box; width: 100%; margin-bottom: 24px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"
                                                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; vertical-align: top; padding-bottom: 15px;">
                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                                    style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                                    <tbody>
                                                        <tr>
                                                            <td
                                                                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; vertical-align: top; border-radius: 8px; text-align: center; background-color: #3b82f6;">
                                                                <a href="{{ config('app.url') }}" target="_blank"
                                                                    style="border: solid 1px #3b82f6; border-radius: 8px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 15px; font-weight: bold; margin: 0; padding: 12px 24px; text-decoration: none; text-transform: capitalize; background-color: #3b82f6; color: #ffffff;">Log
                                                                    In to CMS</a> </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p
                                    style="color: #6b7280; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; margin: 0;">
                                    Thank you,<br>
                                    <strong>IFI CMS Administration</strong>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <!-- END CENTERED WHITE CONTAINER -->

                    <!-- START FOOTER -->
                    <div class="footer" style="clear: both; margin-top: 10px; text-align: center; width: 100%;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                            style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                            <tr>
                                <td class="content-block"
                                    style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; color: #9ca3af; font-size: 12px; text-align: center;">
                                    <span class="apple-link"
                                        style="color: #9ca3af; font-size: 12px; text-align: center;">This is an
                                        automated notification from the IFI CMS system. Please do not reply directly to
                                        this email.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- END FOOTER -->

                </div>
            </td>
            <td
                style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; vertical-align: top;">
                &nbsp;</td>
        </tr>
    </table>
</body>

</html>
