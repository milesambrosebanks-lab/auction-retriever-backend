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
                                            <label for="image" class="form-label">Side Image:</label>
                                            <input type="file" class="dropify @error('image') is-invalid @enderror" name="image"
                                                id="image"
                                                data-default-file="{{ isset($data->image) ? asset($data->image) : '' }}">
                                            @error('image')
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