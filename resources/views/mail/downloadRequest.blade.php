<!DOCTYPE html>
<html>
<body style="margin:0; padding:0; background-color:#f4f6f9;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f6f9; padding:30px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:10px; overflow:hidden;">

    <!-- Header -->
    <tr>
        <td align="center" style="background:#ffff; padding:40px 20px;">
            <h1 style="color:#131762; margin:0; font-family:Arial, sans-serif; font-size:24px;">
                Your Download is Ready!
            </h1>
        </td>
    </tr>

    <!-- Content -->
    <tr>
        <td style="padding:30px; font-family:Arial, sans-serif; color:#333333;">

            <p style="font-size:16px; margin-top:0;">
                Hello {{ $userName ?? 'there' }},
            </p>

            <p style="font-size:15px;">
                Your requested PDF is ready. Click the button below to download.
            </p>

            <!-- File Box -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" 
                   style="background:#f1f5f9; border-radius:8px; padding:15px; margin:20px 0;">
                <tr>
                    <td style="font-size:14px; font-weight:bold;">
                        {{ $pdfTitle ?? 'download.pdf' }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:13px; color:#666;">
                        {{ $fileSize ?? '2.5 MB' }} • PDF
                    </td>
                </tr>
            </table>

            <!-- Button -->
            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:25px auto;">
                <tr>
                    <td align="center" bgcolor="#131762" 
                        style="padding:14px 30px; border-radius:30px;">
                        <a href="{{ $downloadLink ?? '#' }}" 
                           target="_blank"
                           style="color:#ffffff; font-size:16px; 
                                  font-weight:bold; text-decoration:none; 
                                  font-family:Arial, sans-serif;">
                            Download PDF Now
                        </a>
                    </td>
                </tr>
            </table>

            <p style="font-size:13px; color:#888; text-align:center;">
                Link expires in {{ $expiryHours ?? '24' }} hours
            </p>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td align="center" style="background:#f1f5f9; padding:20px; font-family:Arial, sans-serif;">
            <p style="font-size:12px; color:#777; margin:0;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>