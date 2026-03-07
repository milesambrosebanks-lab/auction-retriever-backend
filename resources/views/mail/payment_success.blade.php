
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#C8A24A; padding:20px; text-align:center; color:#fff; font-size:22px; font-weight:bold;">
                            Payment Successful
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px; color:#333; font-size:16px; line-height:1.6;">
                            <p>Hi {{ $name }},</p>

                            <p>Your payment for your recent purchase has been successfully processed.</p>

                            <div style="background-color:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:5px; margin:20px 0;">
                                <h4 style="margin-top:0; color:#131762;">Transaction Details:</h4>
                                <ul style="list-style:none; padding:0; margin:0;">
                                    <li><strong>Transaction ID:</strong> {{ $transaction_id }}</li>
                                    <li><strong>Amount Paid:</strong> {{ $amount }}</li>
                                    <li><strong>Date:</strong> {{ $date }}</li>
                                </ul>
                            </div>

                            <p>Thank you for shopping with us!</p>

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