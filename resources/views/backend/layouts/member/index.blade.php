@extends('backend.app', ['title' => 'Member'])

@push('styles')
    @include('backend.layouts.member._style')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                        <h1 class="page-title">Member</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Member</a></li>
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
                                    <a href="{{ route('admin.member.import.create') }}" class="btn btn-secondary mx-2 btn-sm">Upload Member</a>
                                    <a href="{{ route('admin.member.create') }}" class="btn btn-primary btn-sm">Add</a>
                                    <button type="button" id="print-selected" class="btn btn-info ms-2 btn-sm">🖨️ All Card
                                        Print
                                    </button>
                                </div>

                            </div>


                            <div class="card-body">

                                <div class="row mb-4">

                                    <div class="col-md-2">
                                        <label for="id_number">ID </label>
                                        <input type="text" id="id_number" class="form-control" placeholder="ID Number">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="registered_by">Registered By </label>
                                        <input type="text" id="registered_by" class="form-control"
                                            placeholder="Registed by">
                                    </div>


                                    <div class="col-md-2">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" class="form-control" placeholder="First Name">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" class="form-control" placeholder="Last Name">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="dob">DOB</label>
                                        <input type="text" id="dob" class="form-control" placeholder="DOB">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="phone">Phone</label>
                                        <input type="text" id="phone" class="form-control" placeholder="Phone">
                                    </div>

                                    <div class="col-md-2 mt-2">
                                        <label for="email">Email</label>
                                        <input type="text" id="email" class="form-control" placeholder="Email">
                                    </div>


                                    <div class="col-md-2 mt-2">
                                        <label for="gender">Gender</label>
                                        <select id="gender" class="form-select">
                                            <option value="">All</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            {{-- <option value="other">Other</option> --}}
                                        </select>
                                    </div>


                                    <div class="col-md-2 mt-2">
                                        <label for="country">Country</label>
                                        <input type="text" id="country" class="form-control" placeholder="Country">
                                    </div>

                                    <div class="col-md-2 mt-2">
                                        <label for="county">County</label>
                                        <input type="text" id="county" class="form-control" placeholder="County">
                                    </div>

                                    <div class="col-md-2 mt-2">
                                        <label for="district">District No</label>
                                        <input type="text" id="district" class="form-control" placeholder="District No">
                                    </div>

                                    <div class="col-md-2 mt-2">
                                        <label for="address">Address</label>
                                        <input type="text" id="address" class="form-control" placeholder="Address">
                                    </div>

                                    <div class="col-md-2 mt-2">
                                        <label for="state">State</label>
                                        <input type="text" id="state" class="form-control" placeholder="State">
                                    </div>

                                    <div class="col-md-2 mt-2">
                                        <label for="zip">ZIP</label>
                                        <input type="text" id="zip" class="form-control" placeholder="ZIP">
                                    </div>

                                    {{-- From --}}
                                    <div class="col-md-2 mt-2">
                                        <label for="date_of_issue_from">Date of issue (From)</label>
                                        <input type="text" id="date_of_issue_from" class="form-control"
                                            placeholder="dd/mm/yyyy">
                                    </div>

                                    {{-- To --}}
                                    <div class="col-md-2 mt-2">
                                        <label for="date_of_issue_to">Date of issue (To)</label>
                                        <input type="text" id="date_of_issue_to" class="form-control"
                                            placeholder="dd/mm/yyyy">
                                    </div>


                                    <div class="col-md-2 mt-2">
                                        <label class="text-danger" for="print_status ">Print Status</label>
                                        <select id="print_status" class="form-select">
                                            <option value="">All</option>
                                            <option value="1">Printed</option>
                                            <option value="0">Not Printed</option>
                                        </select>
                                    </div>


                                </div>


                                <div class="row mb-5">
                                    <div class="col-md-3 d-flex align-items-end mt-4">

                                        <button id="reset" class="btn btn-secondary">Reset</button>
                                        <button id="export" class="ms-3 btn btn-success">Export CSV/Excel</button>
                                    </div>
                                </div>

                                <div class="">
                                    <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="select-all-users">
                                                </th>
                                                <th class="bg-transparent border-bottom-0 wp-15">ID</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Registered By</th>

                                                <th class="bg-transparent border-bottom-0 wp-15">First Name</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Last Name</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">DOB</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Phone</th>
                                                {{-- <th class="bg-transparent border-bottom-0 wp-15">Email </th> --}}
                                                <th class="bg-transparent border-bottom-0 wp-15">Gender</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Country </th>
                                                <th class="bg-transparent border-bottom-0 wp-15">County </th>
                                                <th class="bg-transparent border-bottom-0 wp-15">District No </th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Address </th>
                                                <th class="bg-transparent border-bottom-0 wp-15">State </th>
                                                <th class="bg-transparent border-bottom-0 wp-15">ZIP </th>

                                                <th class="bg-transparent border-bottom-0 wp-15">Date Of Issue</th>
                                                <th class="bg-transparent border-bottom-0 wp-15">Print Status</th>



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
    <!-- DataTables and Button Scripts -->
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>

    <script>
        // ✅ Global Array to store selected IDs
        let selectedIds = [];
        let dTable;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            if (!$.fn.DataTable.isDataTable('#datatable')) {
                dTable = $('#datatable').DataTable({
                    order: [
                        [1, 'desc']
                    ], // Default order by id_number DESC
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, 1000],
                        [10, 25, 50, 100, "All"]
                    ],
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    language: {
                        processing: `<div class="text-center">
                    <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                </div>`
                    },
                    scroller: {
                        loadingIndicator: false
                    },
                    pagingType: "full_numbers",
                    dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>Btipr",
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    ajax: {
                        url: "{{ route('admin.member.index', ['type' => $type]) }}",
                        type: "GET",

                        data: function(d) {
                            d.id_number = $('#id_number').val(); // live filter
                            d.registered_by = $('#registered_by').val(); // live filter
                            d.first_name = $('#first_name').val(); // live filter
                            d.last_name = $('#last_name').val(); // live filter
                            d.dob = $('#dob').val(); // live filter
                            d.phone = $('#phone').val(); // live filter
                            d.email = $('#email').val(); // live filter
                            d.gender = $('#gender').val(); // live filter
                            d.country = $('#country').val(); // live filter
                            d.county = $('#county').val(); // live filter
                            d.district = $('#district').val(); // live filter
                            d.address = $('#address').val(); // live filter
                            d.state = $('#state').val(); // live filter
                            d.zip = $('#zip').val(); // live filter
                            d.date_of_issue_from = $('#date_of_issue_from').val(); // live filter
                            d.date_of_issue_to = $('#date_of_issue_to').val(); // live filter
                            d.print_status = $('#print_status').val(); // live filter
                        }
                    },
                    columns: [{
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'id_number',
                            name: 'id_number',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'registered_by',
                            name: 'registered_by',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'first_name',
                            name: 'first_name',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'last_name',
                            name: 'last_name',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'dob',
                            name: 'dob',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'phone',
                            name: 'phone',
                            orderable: true,
                            searchable: true
                        },




                        {
                            data: 'gender',
                            name: 'gender',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'country',
                            name: 'country',
                            orderable: true,
                            searchable: true,
                            render: function(data, type, row) {
                                return data ? data : row.country_of_residence;
                            }
                        },
                        {
                            data: 'county',
                            name: 'county',
                            orderable: true,
                            searchable: true

                        },

                        {
                            data: 'district',
                            name: 'district',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'address',
                            name: 'address',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'state',
                            name: 'state',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'postal_code',
                            name: 'postal_code',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'date_of_issue',
                            name: 'date_of_issue',
                            orderable: true,
                            searchable: true
                        },

                        {
                            data: 'is_card_printed',
                            name: 'is_card_printed',
                            orderable: true,
                            searchable: true,

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
                            extend: 'csv',
                            text: 'Export CSV',
                            className: 'buttons-csv',
                            exportOptions: {
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Export Excel',
                            className: 'buttons-excel',
                            exportOptions: {
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            }
                        }
                    ]
                });
            }
        });

        flatpickr("#date_of_issue_from", {
            dateFormat: "d/m/Y",
            allowInput: true
        });

        flatpickr("#date_of_issue_to", {
            dateFormat: "d/m/Y",
            allowInput: true
        });




        // Export buttons
        $('#export').on('click', function() {
            dTable.button('.buttons-csv').trigger(); // CSV
            dTable.button('.buttons-excel').trigger(); // Excel
        });

        // Live filtering for first_name and last_name
        $('#id_number,#registered_by, #first_name, #last_name,#dob, #email, #phone, #district, #country,#address,  #county, #state, #zip')
            .on('keyup', function() {
                $('#datatable').DataTable().draw();
            });


        // Live filter for gender dropdown
        $('#gender,#print_status, #date_of_issue_from, #date_of_issue_to').on('change', function() {
            $('#datatable').DataTable().draw();
        });

        // Reset filters
        $('#reset').on('click', function() {
            $('#id_number').val('');
            $('#registered_by').val('');
            $('#first_name, #last_name').val('');
            $('#gender').val('');
            $('#email').val('');
            $('#phone').val('');
            $('#country').val('');
            $('#county').val('');
            $('#district').val('');
            $('#state').val('');
            $('#zip').val('');
            $('#date_of_issue_from').val('');
            $('#date_of_issue_to').val('');
            $('#print_status').val('');

            $('#datatable').DataTable().draw();
        });





        function updatePrintStatus(id, status) {
            let actionText = status == 1 ? "mark as Printed" : "mark as Not Printed";

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${actionText}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('admin.member.updatePrintStatus', ':id') }}";
                    $.ajax({
                        url: url.replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            print_status: status
                        },
                        success: function(resp) {
                            toastr.success(resp.message);
                            $('#datatable').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            toastr.error("Something went wrong!");
                        }
                    });
                }
            });
        }






        // When checkbox state changes (individual)
        $(document).on('change', '.user-checkbox', function() {
            let id = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
            } else {
                selectedIds = selectedIds.filter(item => item !== id);
            }
        });

        // Select All checkbox
        $('#select-all-users').on('click', function() {
            let isChecked = this.checked;
            $('.user-checkbox').each(function() {
                $(this).prop('checked', isChecked).trigger('change');
            });
        });

        // Restore checked state after table reload
        $('#datatable').on('draw.dt', function() {
            $('.user-checkbox').each(function() {
                let id = $(this).val();
                if (selectedIds.includes(id)) {
                    $(this).prop('checked', true);
                }
            });
        });

        // ✅ Print selected
        $('#print-selected').on('click', function() {
            if (selectedIds.length === 0) {
                alert('Please select at least one user to print.');
                return;
            }

            let printUrl = `{{ route('admin.manage.member') }}?id_numbers=${selectedIds.join(',')}`;
            window.open(printUrl, '_blank');
        });

        // edit
        function goToEdit(id) {
            let url = "{{ route('admin.member.edit', ':id') }}";
            window.location.href = url.replace(':id', id);
        }

        function goToShow(id) {
            let url = "{{ route('admin.users.show', ':id') }}";
            window.location.href = url.replace(':id', id);
        }

        function goToPrint(slug) {
            let url = "{{ route('admin.users.card.print', ':slug') }}";
            window.location.href = url.replace(':slug', slug);
        }

        // delete confirm
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

        // delete item
        function deleteItem(id) {
            NProgress.start();
            let url = "{{ route('admin.member.destroy', ':id') }}";
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
    </script>
@endpush
