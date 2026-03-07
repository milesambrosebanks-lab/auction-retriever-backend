@php
$url = 'admin.cms.'.$name.'.'.$section;
@endphp

@extends('backend.app', ['title' => ucfirst($name ?? '') . ' - ' . ucfirst($section ?? '')])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush


@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">


            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">CMS : {{ ucfirst($name ?? '') }} Page {{ ucfirst($section ?? '') }} Section.</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">CMS</li>
                        <li class="breadcrumb-item">{{ ucfirst($name ?? '') }}</li>
                        <li class="breadcrumb-item">{{ ucfirst($section ?? '') }}</li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">


                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form class="form form-horizontal" method="POST" action="{{ route($url.'.content') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title" class="form-label">Title:</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" placeholder="Enter here title" id="title" value="{{ $data->title ?? '' }}">
                                            @error('title')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="sub_title" class="form-label">Sub Title:</label>
                                            <input type="text" class="form-control @error('sub_title') is-invalid @enderror" name="sub_title" placeholder="Enter here title" id="sub_title" value="{{ $data->sub_title ?? '' }}">
                                            @error('sub_title')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description_title" class="form-label">Description Title:</label>
                                            <input type="text" class="form-control @error('description_title') is-invalid @enderror" name="description_title" placeholder="Enter here Description Title" id="description_title" value="{{ $data->description_title ?? '' }}">
                                            @error('description_title')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description" class="form-label">Description:</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" placeholder="Enter here description" rows="3">{{ $data->description ?? '' }}</textarea>
                                            @error('description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="bg" class="form-label">Image:</label>
                                            <input type="file" class="dropify @error('bg') is-invalid @enderror" name="bg"
                                                id="bg"
                                                data-default-file="{{ isset($data->bg) ? asset($data->bg) : '' }}">
                                            @error('bg')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12 text-center">
                                        <button class="submit btn btn-primary" type="submit">Submit</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ROW-4 END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection



@push('scripts')
@endpush