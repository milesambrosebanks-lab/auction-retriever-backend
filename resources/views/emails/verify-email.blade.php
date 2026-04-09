@extends('emails.layout')
@section('content')
<p>Hi {{ $name }},</p>
<p>Welcome to {{ config('app.name') }}! You're almost ready to get started</p>

<p>To complete your registration, please click the button below to verify your email address.</p>
<p style="text-align:center;margin:32px 0;">
    <a href="{{ $verificationUrl }}" class="btn">Verify</a>
</p>
<p style="font-size:13px;color:#999;text-align:center;">
    This link expires in 24 hours.<br>
    If you didn’t sign up, you can safely ignore this email.
</p>
@endsection
