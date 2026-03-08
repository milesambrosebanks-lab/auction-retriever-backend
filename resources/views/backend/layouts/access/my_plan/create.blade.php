@extends('backend.app', ['title' => 'Create plans'])
@push('styles')
    <script>
        let index = 1;

        function addVariant() {
            let html = `
        <div class="row variant-row mb-2 align-items-center">
            <div class="col-md-10">
                <input type="text" name="features[]" class="form-control"
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
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Subcriptions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">

                        <div class="tab-content">
                            <div class="tab-pane active show" id="editProfile">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h3 class="card-title mb-0">Create</h3>
                                        <div class="card-options">
                                            <a href="javascript:window.history.back()"
                                                class="btn btn-sm btn-primary">Back</a>
                                        </div>
                                    </div>
                                    <div class="card-body border-0">
                                        <form class="form form-horizontal" action="{{ route('admin.my_plan.store') }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Plan Name</label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                                    name="name" value="{{ old('name') }}">
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <div class="mb-3 col-6">
                                                    <label for="email" class="form-label">Price</label>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control @error('price') is-invalid @enderror"
                                                        id="price" name="price" value="{{ old('email') }}">
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3 col-6">
                                                    <label for="password" class="form-label">Trial Days</label>
                                                    <input type="number"
                                                        class="form-control @error('trial_days') is-invalid @enderror"
                                                        id="trial_days" name="trial_days" value="7">
                                                    @error('trial_days')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- addes new --}}
                                            <div id="variant-wrapper">
                                                <div class="row variant-row mb-2 align-items-center">
                                                    <div class="col-md-10">
                                                        <input type="text" name="features[]" class="form-control"
                                                            placeholder="Included Feature Name">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger w-100"
                                                            onclick="removeVariant(this)">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-info mb-3" onclick="addVariant()">+ Add Features</button>

                                            <hr>

                                            <button type="submit" class="submit btn btn-primary text-right">Submit</button>
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
@endpush
