@extends('backend.app', ['title' => 'Show Survey'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Survey</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Survey</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Show</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">{{ Str::limit($survey->title, 50) }}</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $survey->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>End Date</th>
                                    <td>{{ $survey->end_date ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{!! $survey->description ?? 'N/A' !!}</td>
                                </tr>
                                <tr>
                                    <th>Opinions</th>
                                    <td>
                                        @if($survey->opinions->count() > 0)
                                        <ol class="list-group list-group-numbered">
                                            @foreach ($survey->opinions as $opinion)
                                            <li class="list-group-item d-flex">
                                                <span class="flex-grow-1">{{ $opinion->opinion ?? 'N/A' }}</span>
                                            </li>
                                            @endforeach
                                        </ol>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $survey->created_at ? $survey->created_at : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $survey->updated_at ? $survey->updated_at : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Action</th>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm(`{{ $survey->id }}`)">Delete</button>
                                        <button class="btn btn-sm btn-primary" onclick="goToEdit(`{{ $survey->id }}`)">Edit</button>
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
                window.location.href = "{{ route('admin.survey.index') }}";
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
</script>
@endpush