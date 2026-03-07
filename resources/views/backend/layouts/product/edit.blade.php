@extends('backend.app', ['title' => 'Update Product'])
@push('styles')
    <script>
        let index = 1;

        function addVariant() {
            let html = `
        <div class="row variant-row mb-2 align-items-center">
            <div class="col-md-10">
                <input type="text" name="include_item[]" class="form-control"
                       placeholder="Included Item Name">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger w-100"
                        onclick="removeVariant(this)">
                        Remove
                </button>
            </div>
        </div>`;
            document.getElementById('variant-wrapper').insertAdjacentHTML('beforeend', html);
            index++;
        }

        function removeVariant(button) {
            button.closest('.variant-row').remove();
        }
    </script>
@endpush
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
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Product</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Update</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Update</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body border-0">
                                <form class="form form-horizontal" method="POST"
                                    action="{{ route('admin.product.update', $product->id) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="monthly_price" class="form-label">Monthly Price:</label>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control @error('price') is-invalid @enderror"
                                                        name="monthly_price" placeholder="Enter here price"
                                                        id="monthly_price" value="{{ $product->monthly_price ?? '' }}">
                                                    @error('monthly_price')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="one_time_price" class="form-label">One Time Price:</label>
                                                    <input type="number"  step="0.01" min="0"
                                                        class="form-control @error('price') is-invalid @enderror"
                                                        name="one_time_price" placeholder="Enter here one time price"
                                                        id="oneprice" value="{{ $product->one_time_price ?? '' }}">
                                                    @error('one_time_price')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="supply_days" class="form-label">Supply Days:</label>
                                                    <input type="number"
                                                        class="form-control @error('supply_days') is-invalid @enderror"
                                                        name="supply_days" placeholder="Enter here supply days"
                                                        id="supply_days" value="{{ $product->supply_days ?? '' }}">
                                                    @error('supply_days')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="stock" class="form-label">Stock:</label>
                                                    <input type="number"
                                                        class="form-control @error('stock') is-invalid @enderror"
                                                        name="stock" placeholder="Enter here stock" id="stock"
                                                        value="{{ $product->stock ?? '' }}">
                                                    @error('stock')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="name" class="form-label">Name:</label>
                                                    <input type="text"
                                                        class="form-control @error('title') is-invalid @enderror"
                                                        name="name" placeholder="Enter here name" id="name"
                                                        value="{{ $product->name ?? '' }}">
                                                    @error('name')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="title" class="form-label">Title:</label>
                                                    <input type="text"
                                                        class="form-control @error('title') is-invalid @enderror"
                                                        name="title" placeholder="Enter here title" id="title"
                                                        value="{{ $product->title ?? '' }}">
                                                    @error('title')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="short_description" class="form-label">Short
                                                        Description:</label>
                                                    <textarea class="form-control @error('short_description') is-invalid @enderror" name="short_description"
                                                        id="short_description" placeholder="Enter here short description" rows="3">{{ $product->short_description ?? '' }}</textarea>
                                                    @error('short_description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div> --}}

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="description" class="form-label">Description:</label>
                                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description"
                                                        placeholder="Enter here description" rows="3">{{ $product->description ?? '' }}</textarea>
                                                    @error('description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="category_id" class="form-label">Category:</label>
                                                <select class="form-control @error('category_id') is-invalid @enderror" name="category_id" id="category_id">
                                                    <option value="">Select a Category ID</option>
                                                    @if (!empty($categories) && $categories->count() > 0)
                                                    @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                @error('category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div> --}}

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                    <input type="file"
                                                        data-default-file="{{ $product->thumbnail && file_exists(public_path($product->thumbnail)) ? url($product->thumbnail) : url('default/logo.png') }}"
                                                        class="dropify form-control @error('thumbnail') is-invalid @enderror"
                                                        name="thumbnail" id="thumbnail">
                                                    <p class="textTransform">Image Size Less than 5MB and Image Type must
                                                        be
                                                        jpeg,jpg,png.</p>
                                                    @error('thumbnail')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        {{-- addes new --}}
                                        <div id="variant-wrapper">
                                            @foreach ($product->bundleItems as $item)
                                                <div class="row variant-row mb-2 align-items-center">
                                                    <div class="col-md-10">
                                                        <input type="text" name="include_item[{{ $item->id }}]"
                                                            class="form-control" placeholder="Included Item Name"
                                                            value="{{ $item->title }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger w-100"
                                                            onclick="removeVariant(this)">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                        <button type="button" class="btn btn-info mb-3" onclick="addVariant()">+ Add
                                            Included Item</button>

                                        <hr>
                                        <div class="form-group">
                                            <button class="submit btn btn-primary" type="submit">Submit</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#category_id').select2();
        });
    </script>
@endpush
