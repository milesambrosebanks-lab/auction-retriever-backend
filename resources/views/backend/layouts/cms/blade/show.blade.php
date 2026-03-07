@php
$url = 'admin.cms.'.$page.'.'.$section;
@endphp

@extends('backend.app', ['title' => $page . ' - ' . $section])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">CMS : {{ ucwords(str_replace('_', ' ', $page ?? '')) }} Page {{ ucwords(str_replace('_', ' ', $section ?? '')) }} Section.</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item">CMS</li>
                        <li class="breadcrumb-item">{{ ucwords(str_replace('_', ' ', $page ?? '')) }}</li>
                        <li class="breadcrumb-item">{{ ucwords(str_replace('_', ' ', $section ?? '')) }}</li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Content {{ ucwords(str_replace('_', ' ', $page ?? '')) }}</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Page</th>
                                    <td>
                                        {{ Str::limit($data->page ?? 'N/A', 20) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Section</th>
                                    <td>
                                        {{ Str::limit($data->section ?? 'N/A', 20) }}
                                    </td>
                                </tr>
                                <tr class="{{ in_array('name', $components) ? '' : 'd-none' }}">
                                    <th>Name</th>
                                    <td>
                                        {{ Str::limit($data->name ?? 'N/A', 20) }}
                                    </td>
                                </tr>
                                <tr class="{{ in_array('slug', $components) ? '' : 'd-none' }}">
                                    <th>Slug</th>
                                    <td>
                                        {{ Str::limit($data->slug ?? 'N/A', 20) }}
                                    </td>
                                </tr>
                                <tr class="{{ in_array('title', $components) ? '' : 'd-none' }}">
                                    <th>Title</th>
                                    <td>
                                        {{ $data->title ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr class="{{ in_array('sub_title', $components) ? '' : 'd-none' }}">
                                    <th>Sub Title</th>
                                    <td>
                                        {{ $data->sub_title ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr class="{{ in_array('sub_description', $components) ? '' : 'd-none' }}">
                                    <th>Description</th>
                                    <td>
                                        {!! $data->sub_description ?? 'N/A' !!}
                                    </td>
                                </tr>
                                 
                                 <tr class="{{ in_array('description', $components) ? '' : 'd-none' }}">
                                    <th>Description</th>
                                    <td>
                                        {!! $data->description ?? 'N/A' !!}
                                    </td>
                                </tr>
                               
                                <tr class="{{ in_array('bg', $components) ? '' : 'd-none' }}">
                                    <th>BG</th>
                                    <td>
                                        <img src="{{ asset(!empty($data->bg) && file_exists(public_path($data->bg)) ? $data->bg : 'default/logo.svg') }}" style="width: 108px; height: 108px" />
                                    </td>
                                </tr>
                                <tr class="{{ in_array('image', $components) ? '' : 'd-none' }}">
                                    <th>Image</th>
                                    <td>
                                        <img src="{{ asset(!empty($data->image) && file_exists(public_path($data->image)) ? $data->image : 'default/logo.svg') }}" style="width: 108px; height: 108px" />
                                    </td>
                                </tr>
                                
                            </table>
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