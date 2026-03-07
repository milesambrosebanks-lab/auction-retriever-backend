@extends('backend.app', ['title' => 'Show Policy'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Policy</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Policy</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ Str::limit($police->title, 50) }}</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $police->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Content</th>
                                    <td>{!! $police->content ?? 'N/A' !!}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <a href="{{ asset($police->image ?? 'default/logo.png') }}" target="_blank">
                                            <img src="{{ asset($police->image ?? 'default/logo.png') }}" alt="" width="100" height="100" class="img-fluid">
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>PDF</th>
                                    <td>
                                        <a href="{{ asset($police->pdf_file ?? 'default/logo.png') }}" target="_blank">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $police->created_at ? $police->created_at : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $police->updated_at ? $police->updated_at : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(`{{ $police->id }}`)">Delete</button>
                                        <button class="btn btn-sm btn-primary" onclick="goToEdit(`{{ $police->id }}`)">Edit</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
<script>
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
        let url = "{{ route('admin.party.police.destroy', ':id') }}";
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
                window.location.href = "{{ route('admin.party.police.index') }}";
            },
            error: function(error) {
                NProgress.done();
                toastr.error(error.message);
            }
        });
    }

    //edit
    function goToEdit(id) {
        let url = "{{ route('admin.party.police.edit', ':id') }}";
        window.location.href = url.replace(':id', id);
    }
</script>
@endpush
