@extends('backend.app', ['title' => 'Show Transaction'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}"><i
                                        class="fe fe-home me-2 fs-14"></i>Home</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Subscriber</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Show</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card transaction-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Show</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-header border-bottom">
                                {{-- <h3 class="card-title mb-0">{{ Str::limit($subcribe->id, 50) }}</h3> --}}
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
                                    <i class="fa-solid fa-plus me-1"></i> Send Message</button>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th>Subscriber ID</th>
                                        <td>{{ $subcribe->id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $subcribe->email ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Status</th>
                                        <td>{{ $subcribe->status ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $subcribe->created_at ? $subcribe->created_at->format('d M Y H:i:s') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Metadata</th>
                                        <td>
                                            @if ($subcribe->metadata != null)
                                                <pre>{{ json_encode(json_decode($subcribe->metadata), JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Action</th>
                                        <td>
                                            <button onclick="showDeleteConfirm({{ $subcribe->id }})"
                                                class="btn btn-sm btn-danger">Delete</button>

                                        </td>
                                    </tr>
                                </table>


                                <table class="table table-bordered table-striped mt-4">
                                    <thead>
                                        <tr>
                                            <th colspan="7" class="text-center">Message Logs</th>
                                        </tr>
                                        <tr>
                                            {{-- <th>From</th>
                                            <th>To</th> --}}
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subcribe->emailLogs as $log)
                                            <tr>
                                                {{-- <td>{{ $log->from_email ?? 'N/A' }}</td>
                                            <td>{{ $log->to_email ?? 'N/A' }}</td> --}}
                                                <td>{{ $log->subject ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($log->message != null)
                                                        {{ e(strip_tags($log->message), 100) }}
                                                    @endif
                                                </td>
                                                <td style="width: 180px">
                                                    {{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : 'N/A' }}
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary mt-1 view-message-btn"
                                                        data-message="{{ base64_encode($log->message) }}"
                                                        data-bs-toggle="modal" data-bs-target="#messageModal">
                                                        <i class="fa-solid fa-eye me-1"></i> View
                                                    </button>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div><!-- COL END -->
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-3">

                <!-- Modal Header -->
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold" id="emailModalLabel">
                        <i class="fa-solid fa-envelope me-2 text-secondary"></i>
                        Send Email
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <form method="POST" action="{{ route('admin.send.email') }}">
                    @csrf

                    <div class="modal-body px-4 py-3">

                        <!-- Recipient Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Recipient Email</label>
                            <input type="email" class="form-control @error('to_email') is-invalid @enderror"
                                name="to_email" readonly placeholder="Enter recipient email"
                                value="{{ old('to_email', $subcribe->email ?? '') }}">
                            @error('to_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-medium">Subject</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" name="subject"
                                placeholder="Enter email subject" value="{{ old('subject') }}">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label fw-medium">Message</label>
                            <textarea class="form-control summernote @error('message') is-invalid @enderror" name="message" rows="6"
                                placeholder="Type your message">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light px-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-paper-plane me-1"></i>
                            Send
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- Message Modal for view -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="modalMessageContent" style="white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
    <script>
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
            let url = "{{ route('admin.subscriber.destroy', ':id') }}";
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
                    // $('#datatable').DataTable().ajax.reload();
                    setTimeout(function() {
                        window.location.replace("{{ route('admin.subscriber.index') }}");
                    }, 1500)
                    // toastr.success(resp.message, '', {
                    //     onHidden: function() {
                    //         window.location.replace("{{ route('admin.subscriber.index') }}");
                    //     }
                    // });
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }
    </script>
   <script>
document.addEventListener('DOMContentLoaded', function () {

    const messageModal = document.getElementById('messageModal');

    messageModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        const encodedMessage = button.getAttribute('data-message');

        // Decode base64
        const decodedMessage = atob(encodedMessage);

        // Render HTML
        document.getElementById('modalMessageContent').innerHTML = decodedMessage;
    });

});
</script>
@endpush
