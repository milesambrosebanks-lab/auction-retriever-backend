<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ config('app.name') }}</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
    .header { background: #1a1a2e; padding: 24px 32px; }
    .header h1 { color: #ffffff; margin: 0; font-size: 20px; }
    .body { padding: 32px; color: #333333; font-size: 15px; line-height: 1.6; }
    .otp-box { background: #f0f4ff; border: 1px dashed #4a6cf7; border-radius: 8px; text-align: center; padding: 20px; margin: 24px 0; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #1a1a2e; }
    .btn { display: inline-block; background: #4a6cf7; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-size: 15px; margin: 16px 0; }
    .footer { background: #f8f8f8; padding: 16px 32px; font-size: 12px; color: #999999; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header"><h1>{{ config('app.name') }}</h1></div>
    <div class="body">@yield('content')</div>
    <div class="footer">
    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
    If you did not request this email, please ignore it.
    </div>
</div>
</body>
</html>
