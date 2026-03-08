@extends('backend.app', ['title' => 'Show Post'])

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
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Order</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Show</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Show</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <th>Thumbnail</th>
                                        <td>
                                            <a href="{{ asset($order->product->thumbnail ?? 'default/logo.png') }}"
                                                target="_blank"><img
                                                    src="{{ asset($order->product->thumbnail ?? 'default/logo.png') }}"
                                                    alt="" width="50" height="50" class="img-fluid"></a>
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                    <th>Images</th>
                                    <td>
                                        @if ($post->images)
                                        @foreach ($post->images as $image)
                                        <a href="{{ asset($image->path ?? 'default/logo.png') }}" target="_blank"><img src="{{ asset($image->path ?? 'default/logo.png') }}" class="img-fluid" alt="post image" width="50" height="50"></a>
                                        @endforeach
                                        @endif
                                    </td>
                                </tr> --}}
                                    <tr>
                                        <th>Title</th>
                                        <td>{{ $order->product->title ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td>{{ $order->product->slug ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Customer</th>
                                        <td>{{ $order->customer->first_name }}</td>
                                        {{-- <td><a href="{{ route('admin.users.show', $order->customer_id) }}">{{ $order->customer->first_name }}</a></td> --}}
                                    </tr>

                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $order->created_at ? $order->created_at : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ $order->updated_at ? $order->updated_at : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Action</th>
                                        <td>
                                            <button class="btn btn-sm btn-danger"
                                                onclick="showDeleteConfirm(`{{ $order->id }}`)">Delete</button>
                                            <button class="btn btn-sm btn-primary"
                                                onclick="goToEdit(`{{ $order->id }}`)">Edit</button>
                                        </td>
                                    </tr>
                                </table>
                                <table class="table table-bordered table-striped mt-4">
                                    <thead>
                                        <tr>
                                            <th colspan="4" class="text-center">Customer Information</th>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Billing Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @php
                                            $customer = $order->customer ?? null;
                                            $address =
                                                $customer && $customer->address
                                                    ? json_decode($customer->address, true)
                                                    : [];
                                        @endphp

                                        <tr>
                                            <td>{{ $customer->first_name ?? 'N/A' }}</td>
                                            <td>{{ $customer->email ?? 'N/A' }}</td>
                                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                                            <td>
                                                @if (!empty($address) && is_array($address))
                                                    @foreach ($address as $key => $item)
                                                        @if (!empty($item))
                                                            {{ ucwords(str_replace('_', ' ', $key)) . ': ' . $item }}
                                                            <br><br>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>

                                    </tbody>
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
            let url = "{{ route('admin.post.destroy', ':id') }}";
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
                    window.location.href = "{{ route('admin.post.index') }}";
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }

        //edit
        function goToEdit(id) {
            let url = "{{ route('admin.post.edit', ':id') }}";
            window.location.href = url.replace(':id', id);
        }
    </script>
@endpush
