@extends('backend.app', ['title' => 'Create Member'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Create Member</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Member</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">New Member</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body border-0">

                                {{-- ✅ Success Message --}}
                                @if (session('t-success'))
                                    <div class="alert alert-success">
                                        {{ session('t-success') }}
                                    </div>
                                @endif

                                {{-- ❌ Error Message --}}
                                @if (session('t-error'))
                                    <div class="alert alert-danger">
                                        {{ session('t-error') }}
                                    </div>
                                @endif

                                {{-- ❌ Validation Errors --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('admin.member.import') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="file" class="form-label">Choose CSV File</label>
                                        <input type="file" name="file" id="file" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload & Import</button>
                                </form>








                            </div>

                            {{-- upload avatars --}}

                            <div class="card-body border-0 mt-4">
                                <form action="{{ route('admin.member.uploadAvatar') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="avatar_zip" class="form-label">Upload Avatars</label>
                                        <input type="file" name="avatar[]" id="avatar" multiple  class="form-control"
                                            required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload Avatars</button>
                                </form>




                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
