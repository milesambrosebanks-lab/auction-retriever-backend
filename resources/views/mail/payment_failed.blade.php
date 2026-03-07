<!-- resources/views/emails/payment-failed.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Failed</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#F44336; padding:20px; text-align:center; color:#fff; font-size:22px; font-weight:bold;">
                            Payment Failed
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px; color:#333; font-size:16px; line-height:1.6;">
                            <p>Hi {{ $name }},</p>

                            <p>Unfortunately, your payment could not be processed. Please try again or contact our support for assistance.</p>

                            <p><strong>Note:</strong> No transaction was recorded.</p>

                            <!-- Retry Payment Button -->
                            {{-- <div style="text-align:center; margin:25px 0;">
                                <a href="{{ $retry_url }}" style="background-color:#F44336; color:#fff; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold;">
                                    Retry Payment
                                </a>
                            </div> --}}

                            <p>Best regards,<br>
                            <strong>{{ config('app.name') }}</strong></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f4f4f4; text-align:center; padding:20px; font-size:12px; color:#999;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>