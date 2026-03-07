@extends('backend.app', ['title' => 'Update Country'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Country</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Update</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST" action="{{ route('admin.country.update', $country->id) }}">
                                @csrf
                                <div class="row mb-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="name" class="form-label">Name:</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Enter here name" id="name" value="{{ $country->name ?? '' }}">
                                                @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="code" class="form-label">Code:</label>
                                                <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" placeholder="Enter here code" id="code" value="{{ $country->code ?? '' }}">
                                                @error('code')
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
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 with tags functionality
        $('#category').select2({
            placeholder: "Select or type a new category",
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
                $('#category option').each(function() {
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
        @if(old('category'))
        var oldValue = "{{ old('category') }}";
        // Check if old value exists in current options
        var existsInOptions = false;
        $('#category option').each(function() {
            if ($(this).val() === oldValue) {
                existsInOptions = true;
                return false;
            }
        });

        // If old value doesn't exist in options, add it as a new option
        if (!existsInOptions && oldValue !== '') {
            var newOption = new Option(oldValue, oldValue, true, true);
            $('#category').append(newOption);
        }

        $('#category').val(oldValue).trigger('change');
        @endif
    });
</script>
@endpush