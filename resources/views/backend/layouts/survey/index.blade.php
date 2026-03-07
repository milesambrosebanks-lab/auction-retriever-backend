@extends('backend.app', ['title' => 'Survey'])

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
                        <h1 class="page-title">Survey</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Survey</a></li>
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
                                <h3 class="card-title mb-0">List</h3>
                                <div class="card-options ms-auto">
                                    <a href="{{ route('admin.survey.create') }}" class="btn btn-primary btn-sm">Add</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th class="bg-transparent border-bottom-0 wp-15">ID</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Title</th>
                                                <th class="bg-transparent border-bottom-0">Status</th>
                                                <th class="bg-transparent border-bottom-0">Action</th>
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
    <!-- CONTAINER CLOSED -->
@endsection



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>




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
                        url: "{{ route('admin.survey.index') }}",
                        type: "GET",
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'title',
                            name: 'title',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'dt-center text-center'
                        },
                    ],
                    dom: "<'row justify-content-between table-topbar'<'col-md-6'l><'col-md-6'f>>Btip",


                    buttons: [{
                            extend: 'copy',
                            text: 'Copy',
                            className: 'btn btn-secondary btn-sm'
                        },
                        {
                            extend: 'csv',
                            text: 'CSV',
                            className: 'btn btn-info btn-sm'
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            className: 'btn btn-success btn-sm'
                        },
                        {
                            extend: 'pdf',
                            text: 'PDF',
                            className: 'btn btn-danger btn-sm'
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            className: 'btn btn-primary btn-sm'
                        }
                    ]

                });
            }
        });

        // Status Change Confirm Alert
        function showStatusChangeAlert(id) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to update the status?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    statusChange(id);
                }
            });
        }

        // Status Change
        function statusChange(id) {
            NProgress.start();
            let url = "{{ route('admin.survey.status', ':id') }}";
            $.ajax({
                type: "GET",
                url: url.replace(':id', id),
                success: function(resp) {
                    NProgress.done();
                    toastr.success(resp.message);
                    $('#datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this record?',
                text: 'If you delete this, it will be gone forever.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        }

        // Delete Button
        function deleteItem(id) {
            NProgress.start();
            let url = "{{ route('admin.survey.destroy', ':id') }}";
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "DELETE",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    NProgress.done();
                    toastr.success(resp.message);
                    $('#datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }

        //edit
        function goToEdit(id) {
            let url = "{{ route('admin.survey.edit', ':id') }}";
            window.location.href = url.replace(':id', id);
        }

        function goToOpen(id) {
            let url = "{{ route('admin.survey.show', ':id') }}";
            window.location.href = url.replace(':id', id);
        }
    </script>
@endpush
