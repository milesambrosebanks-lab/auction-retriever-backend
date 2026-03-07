@extends('backend.app', ['title' => 'Subscriber'])

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
                        <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}"><i
                                        class="fe fe-home me-2 fs-14"></i>Home</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Subscriber</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Index</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-4 -->
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <div class="card product-sales-main">
                            <div class="card-header border-bottom">
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                        <i class="fa-solid fa-plus me-1"></i> Section Info</button>
                                    {{-- <button type="button" class="btn btn-danger"><a href="#">Import</a></button>
                                <button type="button" class="btn btn-warning"><a href="#">Export</a></button> --}}
                                </div>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th class="bg-transparent border-bottom-0 wp-15">ID</th>
                                                <th class="bg-transparent border-bottom-0">Email</th>
                                                <th class="bg-transparent border-bottom-0">Subscriptions</th>
                                                <th class="bg-transparent border-bottom-0 wp-20">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div><!-- COL END -->
                </div>
                <!-- ROW-4 END -->

            </div>
        </div>
    </div>

    {{-- modal --}}
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-3">

                <!-- Modal Header -->
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold" id="categoryModalLabel">
                        <i class="fa-solid fa-folder-plus me-2 text-secondary"></i>
                        {{ isset($sectioninfo) ? 'Edit Section info' : 'Add Section info' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <form method="POST"
                    action="{{ route('admin.cms.home.section_info.store', ['page' => $page, 'section' => $section]) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body px-4 py-3">

                        <!-- Category Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Section Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title"
                                placeholder="Enter section title" value="{{ $sectioninfo->title ?? old('title') }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Section Sub-title</label>
                            <input type="text" class="form-control @error('sub_title') is-invalid @enderror"
                                name="sub_title" placeholder="Enter sub title"
                                value="{{ $sectioninfo->sub_title ?? old('sub_title') }}">
                            @error('sub_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="mb-3">
                        <div class="col-md-12 {{ in_array('image', $components) ? '' : 'd-none' }}">
                            <x-form.file name="image" label="Image" :file="$sectioninfo->image ?? ''">
                                <p class="textTransform">Image Size Less than 5MB and Image Type must be jpeg,jpg,png.</p>
                            </x-form.file>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light px-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Save
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection



@push('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });
            if (!$.fn.DataTable.isDataTable('#datatable')) {
                let dTable = $('#datatable').DataTable({
                    order: [],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    processing: true,
                    responsive: true,
                    serverSide: true,

                    language: {
                        processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                        </div>`
                    },

                    scroller: {
                        loadingIndicator: false
                    },
                    pagingType: "full_numbers",
                    dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                    ajax: {
                        url: "{{ route('admin.pdf_request.index') }}",
                        type: "GET",
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'email',
                            name: 'email',
                            orderable: true,
                            searchable: true
                        },
                           {
                            data: 'is_subcribe',
                            name: 'is_subcribe',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'dt-center text-center'
                        },
                    ],
                });
            }
        });

        //edit
        function goToOpen(id) {
            let url = "{{ route('admin.pdf_request.show', ':id') }}";
            window.location.href = url.replace(':id', id);
        }
    </script>
@endpush
