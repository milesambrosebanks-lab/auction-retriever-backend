@extends('backend.app', ['title' => 'Create Product'])
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
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Create</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body border-0">
                                <form class="form form-horizontal" method="POST"
                                    action="{{ route('admin.product.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="name" class="form-label">Name:</label>
                                                    <input type="text"
                                                        class="form-control @error('name') is-invalid @enderror"
                                                        name="name" placeholder="Enter here title" id="name"
                                                        value="{{ old('name') ?? '' }}">
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
                                                        name="title" placeholder="Enter here title eg. complete system" id="title"
                                                        value="{{ old('title') ?? '' }}">
                                                    @error('title')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="category" class="form-label">Category:</label>
                                                    <input type="text"
                                                        class="form-control @error('category') is-invalid @enderror"
                                                        name="category_id" placeholder="Enter here category" id="category"
                                                        value="{{ old('category') ?? '' }}">
                                                    @error('title')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="monthly_price" class="form-label">Mounthly price:</label>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control @error('monthly_price') is-invalid @enderror"
                                                        name="monthly_price" placeholder="Enter product monthly_price"
                                                        id="price" value="{{ old('monthly_price') ?? '' }}"
                                                        min="0">
                                                    @error('monthly_price')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="one_time_price" class="form-label">One time price:</label>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control @error('price') is-invalid @enderror"
                                                        name="one_time_price" placeholder="Enter product one_time_price"
                                                        id="price" value="{{ old('one_time_price') ?? '' }}"
                                                        min="0">
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
                                                        name="supply_days" placeholder="eg. 30" id="supply_days"
                                                        value="{{ old('supply_days') ?? '' }}" min="0">
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
                                                        name="stock" placeholder="Enter here stock of the products"
                                                        id="stock" value="{{ old('stock') ?? '' }}" min="0">
                                                    @error('stock')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="short_description" class="form-label">Short Description:</label>
                                                    <textarea class=" form-control @error('description') is-invalid @enderror" name="short_description" id="description"
                                                        placeholder="Enter here short_description" rows="3">{{ old('short_description') ?? '' }}</textarea>
                                                    @error('short_description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="description" class="form-label">Description:</label>
                                                    <textarea class=" form-control @error('description') is-invalid @enderror" name="description" id="description"
                                                        placeholder="Enter here description" rows="3">{{ old('description') ?? '' }}</textarea>
                                                    @error('description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
{{--  --}}
                                        {{-- <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="category_id" class="form-label">Brand:</label>
                                                <select class="form-control @error('brand_id') is-invalid @enderror" name="brand_id" id="brand_id">
                                                    <option>Select a Brand ID</option>
                                                    @if (!empty($brands) && $brands->count() > 0)
                                                    @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                @error('category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>  --}}

                                        {{-- <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="category_id" class="form-label">Category:</label>
                                                <select class="form-control @error('category_id') is-invalid @enderror" name="category_id" id="category_id">
                                                    <option>Select a Category ID</option>
                                                    @if (!empty($categories) && $categories->count() > 0)
                                                    @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                @error('category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>  --}}

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                    <input type="file"
                                                        class="dropify form-control @error('thumbnail') is-invalid @enderror"
                                                        data-default-file="{{ url('default/logo.png') }}"
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
                                            </div>
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
