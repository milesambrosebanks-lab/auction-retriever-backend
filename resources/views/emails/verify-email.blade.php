@extends('emails.layout')
@section('content')
  <p>Hi {{ $name }},</p>
  <p>Welcome to MilesBanks! Please verify your email address by clicking the button below.</p>
  <p style="text-align:center;margin:32px 0;">
    <a href="{{ $verificationUrl }}" class="btn">Verify my email</a>
  </p>
  <p style="font-size:13px;color:#999;text-align:center;">
    This link expires in 24 hours.<br>
    If you did not create an account, please ignore this email.
  </p>
@endsection
