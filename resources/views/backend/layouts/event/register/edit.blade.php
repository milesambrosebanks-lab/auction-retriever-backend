@extends('backend.app', ['title' => 'Update Event Register'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Event Register</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Event Register</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ Str::limit($data->name, 50) }}</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST" action="{{ route('admin.event.register.update', $data->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name" class="form-label">User:</label>
                                                <select name="user_id" class="form-control @error('user_id') is-invalid @enderror">
                                                    <option value="">Select User</option>
                                                    @foreach ($users as $user)
                                                    <option value="{{ $user->id ?? 1 }}" {{ $data->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name" class="form-label">Name:</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Enter here name" id="name" value="{{ $data->name ?? '' }}">
                                                @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email" class="form-label">Email:</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter here email" id="email" value="{{ $data->email ?? '' }}">
                                                @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="phone" class="form-label">Phone:</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" placeholder="Enter here phone" id="phone" value="{{ $data->phone ?? '' }}">
                                                @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="occupation" class="form-label">Occupation:</label>
                                                <input type="text" class="form-control @error('occupation') is-invalid @enderror" name="occupation" placeholder="Enter here occupation" id="occupation" value="{{ $data->occupation ?? '' }}">
                                                @error('occupation')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="message" class="form-label">Message:</label>
                                                <textarea class="form-control @error('message') is-invalid @enderror" name="message" id="message" placeholder="Enter here message">{{ $data->message ?? '' }}</textarea>
                                                @error('message')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button class="submit btn btn-primary" type="submit">Submit</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
@endpush