@extends('emails.layout')
@section('content')
  <p>Hi {{ $name }},</p>
  @if($status === 'active')
    <p>Your account has been <strong>activated</strong>. You can now log in and access all features.</p>
    <a href="{{ config('app.url') }}/login" class="btn">Log in now</a>
  @else
    <p>Your account has been <strong>deactivated</strong>. You will not be able to log in until your account is reactivated.</p>
    <p>If you think this is a mistake, please contact our support team.</p>
  @endif
@endsection
