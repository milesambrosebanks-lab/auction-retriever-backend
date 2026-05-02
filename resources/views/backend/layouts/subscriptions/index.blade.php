@extends('backend.app', ['title' => 'Subscriptions'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet"/>
@endpush

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Subscriptions</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin/dashboard') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Subscriptions</li>
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
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Stripe ID</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Trial Ends</th>
                                        <th>Ends At</th>
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
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var table = $('#datatable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        ajax: {
            url  : '{{ route('admin.subscriptions.index') }}',
            type : 'GET',
            cache: false,
        },
        language: {
            processing: `<div class="text-center">
                <img src="{{ asset('default/loader.gif') }}" style="width:50px;">
            </div>`
        },
        columns: [
            { data: 'DT_RowIndex',     name: 'DT_RowIndex',   orderable: false, searchable: false },
            { data: 'user_col',        name: 'user_col',      orderable: false },
            { data: 'type_col',        name: 'type',          orderable: true },
            { data: 'stripe_id_col',   name: 'stripe_id',     orderable: false },
            { data: 'status_col',      name: 'stripe_status', orderable: true },
            { data: 'price_col',       name: 'stripe_price',  orderable: false },
            { data: 'trial_ends_col',  name: 'trial_ends_at', orderable: true },
            { data: 'ends_col',        name: 'ends_at',       orderable: true },
            { data: 'action',          name: 'action',        orderable: false, searchable: false,
              className: 'text-center' },
        ],
        order: [[0, 'desc']],
        dom: "<'row mb-3'<'col-md-6'l><'col-md-6 text-end'f>>" +
             "<'row'<'col-md-12'tr>>" +
             "<'row mt-3'<'col-md-5'i><'col-md-7'p>>",
    });

    // Pause / Resume / Cancel confirm
    window.confirmAction = function(url, action) {
        const messages = {
            pause  : 'Are you sure you want to pause this subscription?',
            resume : 'Are you sure you want to resume this subscription?',
            cancel : 'Are you sure you want to cancel this subscription? User will be active until period end.',
        };
        const icons = {
            pause  : 'warning',
            resume : 'question',
            cancel : 'warning',
        };

        Swal.fire({
            title             : 'Confirm ' + action.charAt(0).toUpperCase() + action.slice(1),
            text              : messages[action],
            icon              : icons[action],
            showCancelButton  : true,
            confirmButtonText : 'Yes, ' + action + ' it!',
            cancelButtonText  : 'No, keep it',
            confirmButtonColor: action === 'cancel' ? '#dc3545' : '#ffc107',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                type   : 'POST',
                url    : url,
                success: function (resp) {
                    toastr.success(resp.message);
                    table.ajax.reload(null, false);
                },
                error  : function (xhr) {
                    toastr.error(xhr.responseJSON?.message ?? 'Action failed!');
                }
            });
        });
    };
});
</script>
@endpush
