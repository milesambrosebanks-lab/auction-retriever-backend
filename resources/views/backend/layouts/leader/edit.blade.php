@extends('backend.app', ['title' => 'Update Leader'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Leader</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Leader</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ Str::limit($leader->title, 50) }}</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST" action="{{ route('admin.leader.update', $leader->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name" class="form-label">Name:</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Enter here name" id="name" value="{{ $leader->name ?? '' }}">
                                                @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="is_leader" class="form-label">Is Leader:</label>
                                                <select class="form-select @error('is_leader') is-invalid @enderror" name="is_leader" id="is_leader">
                                                    <option value="0" {{ $leader->is_leader === 0 ? 'selected' : '' }}>No</option>
                                                    <option value="1" {{ $leader->is_leader === 1 ? 'selected' : '' }}>Yes</option>
                                                </select>
                                                @error('is_leader')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="position" class="form-label">Position:</label>
                                                <select class="form-control select2 @error('position') is-invalid @enderror" name="position" id="position">
                                                    <option value="">Select Position</option>
                                                    @foreach($positions as $position)
                                                    <option value="{{ $position }}" {{ $leader->position == $position ? 'selected' : '' }}>{{ $position }}</option>
                                                    @endforeach
                                                </select>
                                                @error('position')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="content" class="form-label">Content:</label>
                                                <textarea class="summernote form-control @error('content') is-invalid @enderror" name="content" id="description" placeholder="Enter here content" rows="3">{{ $leader->content ?? '' }}</textarea>
                                                @error('content')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                <input type="file" data-default-file="{{ $leader->thumbnail && file_exists(public_path($leader->thumbnail)) ? url($leader->thumbnail) : url('default/logo.png') }}" class="dropify form-control @error('thumbnail') is-invalid @enderror" name="thumbnail" id="thumbnail">
                                                @error('thumbnail')
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
        // Initialize Select2 with tags functionality
        $('#position').select2({
            placeholder: "Select or type a new position",
            allowClear: true,
            width: '100%',
            tags: true,
            createTag: function(params) {
                var term = $.trim(params.term);

                if (term === '') {
                    return null;
                }

                // Check if the term already exists
                var existsInOptions = false;
                $('#position option').each(function() {
                    if ($(this).text().toLowerCase() === term.toLowerCase()) {
                        existsInOptions = true;
                        return false;
                    }
                });

                if (existsInOptions) {
                    return null;
                }

                return {
                    id: term,
                    text: term + ' (new)',
                    newTag: true
                };
            },
            templateResult: function(data) {
                if (data.newTag) {
                    return $('<span><em>Create:</em> <strong>' + data.text.replace(' (new)', '') + '</strong></span>');
                }
                return data.text;
            },
            templateSelection: function(data) {
                if (data.newTag) {
                    return data.text.replace(' (new)', '');
                }
                return data.text;
            }
        });

        // Restore old value if validation fails (including custom values)
        @if(old('position'))
        var oldValue = "{{ old('position') }}";
        // Check if old value exists in current options
        var existsInOptions = false;
        $('#position option').each(function() {
            if ($(this).val() === oldValue) {
                existsInOptions = true;
                return false;
            }
        });

        // If old value doesn't exist in options, add it as a new option
        if (!existsInOptions && oldValue !== '') {
            var newOption = new Option(oldValue, oldValue, true, true);
            $('#position').append(newOption);
        }

        $('#position').val(oldValue).trigger('change');
        @endif
    });
</script>
@endpush