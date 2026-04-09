@extends('emails.layout')
@section('content')
  <p>Hi {{ $name }},</p>
  <p>Welcome to {{ config('app.name') }}! Your account has been successfully created.</p>
  <p>You can now browse auction listings, save your favorites, and get notified about upcoming auctions.</p>
  <a href="{{ config('app.url') }}/login" class="btn">Go to dashboard</a>
  <p>If you have any questions, feel free to reach out to our support team.</p>
@endsection
