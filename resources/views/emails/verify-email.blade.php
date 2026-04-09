@extends('emails.layout')
@section('content')
<p>Hi {{ $name }},</p>
<p>We got a request to reset your password. Click below to set a new one and get back into your account.</p>
<p style="text-align:center;margin:32px 0;">
    <a href="{{ $verificationUrl }}" class="btn">Verify</a>
</p>
<p style="font-size:13px;color:#999;text-align:center;">
    This link expires in 24 hours.<br>
    If this wasn’t you, you can ignore this email.
</p>
@endsection
