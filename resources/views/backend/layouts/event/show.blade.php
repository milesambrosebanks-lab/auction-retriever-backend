@extends('backend.app', ['title' => 'Show Event'])

@section('content')

    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Event</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Event</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Show</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">{{ Str::limit($event->title, 50) }}</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th>Title</th>
                                        <td>{{ $event->title ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Sub Title</th>
                                        <td>{{ $event->sub_title ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Slug</th>
                                        <td>{{ $event->slug ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Start Time</th>
                                        <td>{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('M j, Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>End Time</th>
                                        <td>{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('M j, Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $event->phone ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Organizer</th>
                                        <td>{{ $event->organizer ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Venue</th>
                                        <td>{{ $event->venue ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Thumbnail</th>
                                        <td>
                                            <a href="{{ asset($event->thumbnail ?? 'default/logo.png') }}"
                                                target="_blank"><img
                                                    src="{{ asset($event->thumbnail ?? 'default/logo.png') }}"
                                                    alt="" width="100" height="100" class="img-fluid"></a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Description</th>
                                        <td>{!! $event->description ?? 'N/A' !!}</td>
                                    </tr>

                                    <tr>
                                        <th>Speakers</th>
                                        <td>
                                            @if ($event->speakers && $event->speakers->count())
                                                <ul class="list-unstyled mb-0">
                                                    @foreach ($event->speakers as $speaker)
                                                        <li>{{ $speaker->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $event->created_at ? $event->created_at->format('M j, Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ $event->updated_at ? $event->updated_at->format('M j, Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Action</th>
                                        <td>
                                            <button class="btn btn-sm btn-danger"
                                                onclick="showDeleteConfirm(`{{ $event->id }}`)">Delete</button>
                                            <button class="btn btn-sm btn-primary"
                                                onclick="goToEdit(`{{ $event->id }}`)">Edit</button>
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
            let url = "{{ route('admin.event.destroy', ':id') }}";
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
                    window.location.href = "{{ route('admin.event.index') }}";
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }

        //edit
        function goToEdit(id) {
            let url = "{{ route('admin.event.edit', ':id') }}";
            window.location.href = url.replace(':id', id);
        }
    </script>
@endpush
