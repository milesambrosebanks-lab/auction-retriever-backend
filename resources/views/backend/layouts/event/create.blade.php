@extends('backend.app', ['title' => 'Create Event'])

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Event</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Event</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Create Event</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST" action="{{ route('admin.event.store') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title" class="form-label">Title:</label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" placeholder="Enter here title" id="title" value="{{ old('title') ?? '' }}">
                                                @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="created_at" class="form-label">Created Date:</label>
                                                <input type="datetime-local" class="form-control @error('created_at') is-invalid @enderror" name="created_at" placeholder="Enter here date" id="created_at" value="{{ old('created_at') ?? '' }}">
                                                @error('created_at')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="description" class="form-label">Description:</label>
                                                <textarea class="summernote form-control @error('description') is-invalid @enderror" name="description" id="description" placeholder="Enter here description" rows="3">{{ old('description') ?? '' }}</textarea>
                                                @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                <input type="file" class="dropify form-control @error('thumbnail') is-invalid @enderror" name="thumbnail" id="thumbnail">
                                                @error('thumbnail')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title" class="form-label">Title:</label>
                                                <input type="text"
                                                    class="form-control @error('title') is-invalid @enderror"
                                                    name="title" id="title" placeholder="Enter title"
                                                    value="{{ old('title') }}">
                                                @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="sub_title" class="form-label">Sub Title:</label>
                                                <input type="text"
                                                    class="form-control @error('sub_title') is-invalid @enderror"
                                                    name="sub_title" id="sub_title" placeholder="Enter sub title"
                                                    value="{{ old('sub_title') }}">
                                                @error('sub_title')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="start_time" class="form-label">Start Time:</label>
                                                <input type="datetime-local"
                                                    class="form-control @error('start_time') is-invalid @enderror"
                                                    name="start_time" id="start_time" value="{{ old('start_time') }}">
                                                @error('start_time')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="end_time" class="form-label">End Time:</label>
                                                <input type="datetime-local"
                                                    class="form-control @error('end_time') is-invalid @enderror"
                                                    name="end_time" id="end_time" value="{{ old('end_time') }}">
                                                @error('end_time')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone" class="form-label">Phone:</label>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    name="phone" id="phone" placeholder="Enter phone"
                                                    value="{{ old('phone') }}">
                                                @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="organizer" class="form-label">Organizer:</label>
                                                <input type="text"
                                                    class="form-control @error('organizer') is-invalid @enderror"
                                                    name="organizer" id="organizer" placeholder="Enter organizer"
                                                    value="{{ old('organizer') }}">
                                                @error('organizer')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="venue" class="form-label">Venue:</label>
                                                <input type="text"
                                                    class="form-control @error('venue') is-invalid @enderror"
                                                    name="venue" id="venue" placeholder="Enter venue"
                                                    value="{{ old('venue') }}">
                                                @error('venue')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                <input type="file"
                                                    class="dropify form-control @error('thumbnail') is-invalid @enderror"
                                                    name="thumbnail" id="thumbnail">
                                                @error('thumbnail')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="description" class="form-label">Description:</label>
                                                <textarea class="summernote form-control @error('description') is-invalid @enderror" name="description"
                                                    id="description" rows="4">{{ old('description') }}</textarea>
                                                @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="speakers" class="form-label">Speakers:</label>
                                                <select class="form-select @error('speakers') is-invalid @enderror"
                                                    name="speakers[]" id="speakers" multiple>
                                                    @foreach ($leaders as $leader)
                                                    <option value="{{ $leader->id }}"
                                                        {{ in_array($leader->id, old('speakers', [])) ? 'selected' : '' }}>
                                                        {{ $leader->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('speakers')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                @error('speakers.*')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="registration_enabled" class="form-label">Registration Enabled:</label>
                                                <select class="form-select @error('registration_enabled') is-invalid @enderror"
                                                    name="registration_enabled" id="registration_enabled">
                                                    <option value="yes" {{ old('registration_enabled') == 'yes' ? 'selected' : '' }}>Yes</option>
                                                    <option value="no" {{ old('registration_enabled') == 'no' ? 'selected' : '' }}>No</option>
                                                </select>
                                                @error('registration_enabled')
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#speakers').select2({
            placeholder: "Select speakers",
            width: '100%'
        });
    });
    flatpickr("#start_time", {
        enableTime: true,
        dateFormat: "Y-m-d H:i:S" // Matches Laravel validation format
    });
</script>
@endpush