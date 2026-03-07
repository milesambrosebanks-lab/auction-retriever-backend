<!-- resources/views/emails/simple-message.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#e9e9e9; padding:20px; text-align:center; color:#000000; font-size:22px; font-weight:bold;">
                            {{ $subject }}
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333; font-size:16px; line-height:1.6;">
                            {!! $body !!}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#131762; text-align:center; padding:20px; font-size:12px; color:#fff;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>