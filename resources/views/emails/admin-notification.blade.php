@extends('emails.layout')
@section('content')
  <p>{{ $message }}</p>
  @if(!empty($data))
    <table style="width:100%;border-collapse:collapse;margin-top:16px;">
      @foreach($data as $key => $value)
        <tr>
          <td style="padding:8px;border:1px solid #eee;color:#666;width:40%;">{{ $key }}</td>
          <td style="padding:8px;border:1px solid #eee;">{{ $value }}</td>
        </tr>
      @endforeach
    </table>
  @endif
@endsection
