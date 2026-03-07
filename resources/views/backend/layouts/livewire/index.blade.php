@extends('backend.app', ['title' => 'Post'])

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
                    <h1 class="page-title">Users</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">
                <div class="col-12 col-sm-12">
                    <livewire:crud />
                </div><!-- COL END -->
            </div>
            <!-- ROW-4 END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
@endpush