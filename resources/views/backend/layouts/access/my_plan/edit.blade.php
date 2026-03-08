@extends('backend.app', ['title' => 'Update My plan'])

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
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Subsciption</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Update</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">

                        <div class="tab-content">
                            <div class="tab-pane active show" id="editProfile">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h3 class="card-title mb-0">Update</h3>
                                        <div class="card-options">
                                            <a href="javascript:window.history.back()"
                                                class="btn btn-sm btn-primary">Back</a>
                                        </div>
                                    </div>
                                    <div class="card-body border-0">
                                        <form class="form form-horizontal"
                                            action="{{ route('admin.my_plan.update', $plan->id) }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            <div class="row">
                                                <div class="mb-3 col-6">
                                                    <label for="name" class="form-label">Plan Name</label>
                                                    <input type="text"
                                                        class="form-control @error('name') is-invalid @enderror"
                                                        name="name" value="{{ old('name', $plan->name) }}">
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3 col-6">
                                                    <label for="price" class="form-label">Price</label>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control @error('price') is-invalid @enderror"
                                                        name="price" value="{{ old('price', $plan->price) }}">
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-4">
                                                    <label for="currency" class="form-label">Currency</label>
                                                    <input type="text" class="form-control" readonly
                                                        value="{{ $plan->currency }}">
                                                </div>
                                                <div class="mb-3 col-4">
                                                    <label for="product" class="form-label">Product ID</label>
                                                    <input type="text" class="form-control" readonly
                                                        value="{{ $plan->stripe_product_id }}">
                                                </div>
                                                <div class="mb-3 col-4">
                                                    <label for="price_id" class="form-label">Price ID</label>
                                                    <input type="text" step="0.01" min="0" class="form-control"
                                                        readonly value="{{ $plan->stripe_price_id }}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3 col-4">
                                                    <label for="interval" class="form-label">Interval</label>
                                                    <input type="text" class="form-control " id="interval" readonly
                                                        value="{{ $plan->interval }}">

                                                </div>
                                                <div class="mb-3 col-4">
                                                    <label for="trial" class="form-label">Trial Days</label>
                                                    <input type="text" class="form-control" readonly
                                                        value="{{ $plan->trial_days }}">
                                                </div>
                                                <div class="mb-3 col-4">
                                                    <label for="created" class="form-label">Created</label>
                                                    <input type="text" step="0.01" min="0"
                                                        class="form-control " readonly
                                                        value="{{ $plan->created_at->format('d-M-Y') }}">
                                                </div>
                                            </div>

                                            {{-- addes new --}}
                                            <div id="variant-wrapper">
                                                <label for="created" class="form-label">Features: </label>
                                                @foreach ($plan->features as $item)
                                                    <div class="row variant-row mb-2 align-items-center">
                                                        <div class="col-md-10">

                                                            <input type="text" name="features[{{ $item->id }}]"
                                                                class="form-control" placeholder="Included Features"
                                                                value="{{ $item->name }}">
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
                                                Features</button>


                                            <hr>

                                            <button type="submit" class="submit btn btn-primary">Submit</button>
                                        </form>
                                    </div>
                                </div>
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
    <script>
        let index = 1;

        function addVariant() {
            let html = `
        <div class="row variant-row mb-2 align-items-center">
            <div class="col-md-10">
                <input type="text" name="features[]" class="form-control"
                       placeholder="Included Features">
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
