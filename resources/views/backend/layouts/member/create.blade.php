@extends('backend.app', ['title' => 'Create Member'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Create Member</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Member</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">New Member</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body border-0">
                                <form class="form-horizontal" method="POST"
                                      action="{{ route('admin.member.store') }}" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <!-- Avatar -->
                                        <div class="mb-3">
                                            <label for="avatar" class="form-label">Profile Image</label>
                                            <input required type="file" id="avatar" name="avatar"
                                                   class="form-control dropify @error('avatar') is-invalid @enderror"
                                                   data-default-file="{{ url('default/logo.png') }}">
                                            @error('avatar')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <!-- Registered By -->
                                            <div class="mb-3">
                                                <label for="registered_by" class="form-label">Registered By *</label>
                                                <input type="text" id="registered_by" name="registered_by"
                                                       class="form-control @error('registered_by') is-invalid @enderror"
                                                       value="{{ old('registered_by') }}"
                                                       placeholder="Enter registered by" >
                                                @error('registered_by')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- First Name -->
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">First Name *</label>
                                                <input type="text" id="first_name" name="first_name"
                                                       class="form-control @error('first_name') is-invalid @enderror"
                                                       value="{{ old('first_name') }}"
                                                       placeholder="Enter first name" required>
                                                @error('first_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Last Name -->
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last Name *</label>
                                                <input type="text" id="last_name" name="last_name"
                                                       class="form-control @error('last_name') is-invalid @enderror"
                                                       value="{{ old('last_name') }}"
                                                       placeholder="Enter last name" required>
                                                @error('last_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Date of Birth -->
                                            <div class="mb-3">
                                                <label for="dob" class="form-label">Date of Birth *</label>
                                                <input type="date" id="dob" name="dob"
                                                       class="form-control @error('dob') is-invalid @enderror"
                                                       value="{{ old('dob') }}" required>
                                                @error('dob')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Gender -->
                                            <div class="mb-3">
                                                <label for="gender" class="form-label">Gender *</label>
                                                <select id="gender" name="gender"
                                                        class="form-control @error('gender') is-invalid @enderror" required>
                                                    <option value="">--Select--</option>
                                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                                @error('gender')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Phone -->
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone *</label>
                                                <input type="text" id="phone" name="phone" pattern="[0-9]{6,16}"
                                                       class="form-control @error('phone') is-invalid @enderror"
                                                       value="{{ old('phone') }}"
                                                       placeholder="Enter phone" required>
                                                @error('phone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Email -->
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email (Optional)</label>
                                                <input type="email" id="email" name="email"
                                                       class="form-control @error('email') is-invalid @enderror"
                                                       value="{{ old('email') }}"
                                                       placeholder="Enter email">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <!-- Country of Residence -->
                                            <div class="mb-3">
                                                <label for="country_of_residence" class="form-label">Country of Residence *</label>
                                                <select id="country_of_residence" name="country_of_residence"
                                                        class="form-control @error('country_of_residence') is-invalid @enderror"
                                                        required>
                                                    <option value="">--Select country--</option>
                                                    <option value="Liberia" {{ old('country_of_residence') == 'Liberia' ? 'selected' : '' }}>Liberia</option>
                                                    <option value="Other" {{ old('country_of_residence') == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('country_of_residence')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Liberia Fields -->
                                            <div id="liberia-fields" class="mb-3" style="display:none;">
                                                <div class="mb-2">
                                                    <label for="county" class="form-label">County *</label>
                                                    <select id="county" name="county" class="form-control">
                                                        <option value="">--Select county--</option>
                                                        @php
                                                            $counties = [
                                                                'Bomi','Bong','Gbarpolu','Grand Bassa','Grand Cape Mount',
                                                                'Grand Gedeh','Grand Kru','Lofa','Margibi','Maryland',
                                                                'Montserrado','Nimba','River Cess','River Gee','Sinoe',
                                                            ];
                                                        @endphp
                                                        @foreach($counties as $county)
                                                            <option value="{{ $county }}" {{ old('county') == $county ? 'selected' : '' }}>
                                                                {{ $county }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label for="district" class="form-label">District *</label>
                                                    <select id="district" name="district" class="form-control">
                                                        <option value="">--Select district--</option>
                                                        @for($i=1; $i<=17; $i++)
                                                            <option value="{{ $i }}" {{ old('district') == $i ? 'selected' : '' }}>District {{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Other Country Fields -->
                                            <div id="other-fields" class="mb-3" style="display:none;">
                                                <div class="mb-2">
                                                    <label for="country" class="form-label">Current Country *</label>
                                                    <input type="text" id="country" name="country"
                                                           class="form-control" value="{{ old('country') }}">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="address" class="form-label">Address *</label>
                                                    <input type="text" id="address" name="address"
                                                           class="form-control" value="{{ old('address') }}">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="state" class="form-label">State/Province/Region *</label>
                                                    <input type="text" id="state" name="state"
                                                           class="form-control" value="{{ old('state') }}">
                                                </div>
                                                <div>
                                                    <label for="postal_code" class="form-label">ZIP/Postcode *</label>
                                                    <input type="text" id="postal_code" name="postal_code"
                                                           class="form-control" value="{{ old('postal_code') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="mb-3">
                                        <button class="btn btn-primary w-100" type="submit">Create Member</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleCountryFields() {
        let selected = document.getElementById("country_of_residence").value;

        let liberiaFields = document.querySelectorAll("#liberia-fields select, #liberia-fields input");
        let otherFields   = document.querySelectorAll("#other-fields select, #other-fields input");

        if (selected === "Liberia") {
            document.getElementById("liberia-fields").style.display = "block";
            document.getElementById("other-fields").style.display = "none";

            liberiaFields.forEach(el => el.setAttribute("required", "required"));
            otherFields.forEach(el => el.removeAttribute("required"));

        } else if (selected === "Other") {
            document.getElementById("liberia-fields").style.display = "none";
            document.getElementById("other-fields").style.display = "block";

            otherFields.forEach(el => el.setAttribute("required", "required"));
            liberiaFields.forEach(el => el.removeAttribute("required"));

        } else {
            document.getElementById("liberia-fields").style.display = "none";
            document.getElementById("other-fields").style.display = "none";

            liberiaFields.forEach(el => el.removeAttribute("required"));
            otherFields.forEach(el => el.removeAttribute("required"));
        }
    }

    document.getElementById("country_of_residence").addEventListener("change", toggleCountryFields);
    window.addEventListener('DOMContentLoaded', toggleCountryFields);
</script>
@endpush
