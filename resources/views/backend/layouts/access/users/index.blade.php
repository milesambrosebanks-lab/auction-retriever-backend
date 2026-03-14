{{-- resources/views/backend/layouts/access/users/index.blade.php --}}

@extends('backend.app', ['title' => 'Users'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- Page Header --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Users</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin/dashboard') }}">
                                    <i class="fe fe-home me-2 fs-14"></i>Home
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card product-sales-main">

                            {{-- Filters --}}
                            <div class="card-header border-bottom">
                                <div class="row g-2 align-items-end w-100">
                                    <div class="col-md-2">
                                        <label class="form-label mb-1 small">Status</label>
                                        <select id="filterStatus" class="form-select form-select-sm">
                                            <option value="">All Status</option>
                                            <option value="trialing">Trial</option>
                                            <option value="active">Active</option>
                                            <option value="canceled">Cancelled</option>
                                            <option value="expired">Past Due / Expired</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1 small">Plan</label>
                                        <select id="filterPlan" class="form-select form-select-sm">
                                            <option value="">All Plans</option>
                                            @foreach ($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1 small">From Date</label>
                                        <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label mb-1 small">To Date</label>
                                        <input type="date" id="filterDateTo" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <button id="applyFilter" class="btn btn-primary btn-sm w-100">
                                            <i class="fa fa-filter me-1"></i> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <button id="resetFilter" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fa fa-times me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <table class="table table-bordered text-nowrap border-bottom" id="users-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Status</th>
                                            <th>Plan</th>
                                            <th>Trial Ends</th>
                                            <th>Sub Ends</th>
                                            <th>Last Login</th>
                                            <th>Joined</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
        $(function() {

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('admin.users.index') }}',
                    type: 'GET',
                    data: function(d) {
                        d.status = $('#filterStatus').val();
                        d.plan = $('#filterPlan').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                    },
                    cache: false,
                },
                language: {
                    processing: `<div class="text-center">
                <img src="{{ asset('default/loader.gif') }}" style="width:50px;">
            </div>`,
                    lengthMenu: "_MENU_",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name_col',
                        name: 'name',
                        orderable: true
                    },
                    {
                        data: 'status_badge',
                        name: 'subscription_status',
                        orderable: true
                    },
                    {
                        data: 'plan_col',
                        name: 'plan_col',
                        orderable: false
                    },
                    {
                        data: 'trial_ends',
                        name: 'trial_ends_at',
                        orderable: true
                    },
                    {
                        data: 'sub_ends',
                        name: 'subscription_ends_at',
                        orderable: true
                    },
                    {
                        data: 'last_login',
                        name: 'last_activity_at',
                        orderable: true
                    },
                    {
                        data: 'created',
                        name: 'created_at',
                        orderable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                order: [
                    [7, 'desc']
                ],
                lengthMenu: [10, 25, 50, 100],
                dom: "<'row mb-3'<'col-md-6'l><'col-md-6 text-end'f>>" +
                    // "<'row mb-2'<'col-md-12'B>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
                buttons: [{
                        extend: 'csv',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fa fa-download me-1"></i> CSV',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] // action column বাদ
                        }
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-info btn-sm',
                        text: '<i class="fa fa-file-excel me-1"></i> Excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fa fa-print me-1"></i> Print',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                ],
            });

            // Filter apply
            $('#applyFilter').on('click', function() {
                table.ajax.reload();
            });

            // Filter reset
            $('#resetFilter').on('click', function() {
                $('#filterStatus, #filterPlan').val('');
                $('#filterDateFrom, #filterDateTo').val('');
                table.ajax.reload();
            });

        });
    </script>
@endpush
