@extends('backend.app', ['title' => 'Transactions'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet"/>
@endpush

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Transactions</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin/dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Transactions</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered text-nowrap" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Transaction ID</th>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
<script>
$(function () {
    $('#datatable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        ajax: {
            url  : '{{ route('admin.transaction.index') }}',
            type : 'GET',
            cache: false,
        },
        language: {
            processing: `<div class="text-center">
                <img src="{{ asset('default/loader.gif') }}" style="width:50px;">
            </div>`
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex',  orderable: false, searchable: false },
            { data: 'trx_id',      name: 'trx_id' },
            { data: 'user_col',    name: 'user_col',      orderable: false },
            { data: 'amount_col',  name: 'amount',        orderable: true },
            { data: 'status_col',  name: 'status',        orderable: true },
            { data: 'invoice_col', name: 'invoice_col',   orderable: false, searchable: false },
            { data: 'date_col',    name: 'created_at',    orderable: true },
            { data: 'action',      name: 'action',        orderable: false, searchable: false,
              className: 'text-center' },
        ],
        order: [[0, 'desc']],
        dom: "<'row mb-3'<'col-md-6'l><'col-md-6 text-end'f>>" +
             "<'row'<'col-md-12'tr>>" +
             "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
    });
});
</script>
@endpush
