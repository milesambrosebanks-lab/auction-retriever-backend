@extends('emails.layout')
@section('content')
  <p>Hi there,</p>
  @if($type === 'registration')
    <p>Use the OTP below to complete your registration. It expires in <strong>10 minutes</strong>.</p>
  @else
    <p>Use the OTP below to reset your password. It expires in <strong>10 minutes</strong>.</p>
  @endif
  <div class="otp-box">{{ $otp }}</div>
  <p>Do not share this OTP with anyone.</p>
@endsection
