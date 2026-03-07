@extends('backend.app', ['title' => 'Update News'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">News</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">News</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ Str::limit($news->title, 50) }}</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST" action="{{ route('admin.news.update', $news->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title" class="form-label">Title:</label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" placeholder="Enter here title" id="title" value="{{ $news->title ?? '' }}">
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
                                                <input type="datetime-local" class="form-control @error('created_at') is-invalid @enderror" name="created_at" placeholder="Enter here date" id="created_at" value="{{ $news->created_at ?? '' }}">
                                                @error('created_at')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="contentContainer">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="content" class="form-label">Content:</label>
                                                <textarea class="summernote form-control @error('content') is-invalid @enderror" name="content" id="description" placeholder="Enter here content" rows="3">{{ $news->content ?? '' }}</textarea>
                                                @error('content')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="linkContainer">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="link" class="form-label">Link:</label>
                                                <input type="text" class="form-control @error('link') is-invalid @enderror" name="link" placeholder="Enter here Link" id="Link" value="{{ $news->link ?? '' }}">
                                                @error('link')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="type" class="form-label">Type:</label>
                                                <select class="form-control @error('type') is-invalid @enderror" name="type" id="type">
                                                    @foreach(['live' => 'Live', 'article' => 'Article'] as $key => $value)
                                                        <option value="{{ $key }}" {{ $news->type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                                @error('type')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                <input type="file" data-default-file="{{ $news->thumbnail ? url($news->thumbnail) : url('default/logo.png') }}" class="dropify form-control @error('thumbnail') is-invalid @enderror" name="thumbnail" id="thumbnail">
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
<script>
    $(document).ready(function() {
        $('#type').on('change', function() {
            if ($(this).val() == 'live') {
                $('#linkContainer').show();
            } else {
                $('#linkContainer').hide();
            }
        });

        if ($('#type').val() == 'live') {
            $('#linkContainer').show();
        } else {
            $('#linkContainer').hide();
        }
    })
</script>
@endpush
