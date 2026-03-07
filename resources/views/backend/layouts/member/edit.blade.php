@extends('backend.app', ['title' => 'Edit Member'])

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">Edit Member</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.member.index') }}">Member</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-body border-0">
                            <form method="POST" action="{{ route('admin.member.update', $user->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('POST')

                                <div class="row">
                                    <div class="col-lg-6">
                                        <!-- Avatar -->
                                        <div class="mb-3">
                                            <label for="avatar" class="form-label">Profile Image</label>
                                            <input type="file" id="avatar" name="avatar"
                                                   class="form-control dropify @error('avatar') is-invalid @enderror"
                                                   data-default-file="{{ $user->avatar ? url($user->avatar) : url('default/logo.png') }}">
                                            @error('avatar')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Registered By -->
                                        <div class="mb-3">
                                            <label for="registered_by" class="form-label">Registered By *</label>
                                            <input type="text" id="registered_by" name="registered_by"
                                                   class="form-control @error('registered_by') is-invalid @enderror"
                                                   value="{{ old('registered_by', $user->profile->registered_by) }}">
                                            @error('registered_by')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- First Name -->
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" id="first_name" name="first_name"
                                                   class="form-control @error('first_name') is-invalid @enderror"
                                                   value="{{ old('first_name', $user->profile->first_name) }}">
                                            @error('first_name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Last Name -->
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Last Name *</label>
                                            <input type="text" id="last_name" name="last_name"
                                                   class="form-control @error('last_name') is-invalid @enderror"
                                                   value="{{ old('last_name', $user->profile->last_name) }}">
                                            @error('last_name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Date of Birth -->
                                        <div class="mb-3">
                                            <label for="dob" class="form-label">Date of Birth *</label>
                                            <input type="date" id="dob" name="dob"
                                                   class="form-control @error('dob') is-invalid @enderror"
                                                   value="{{ old('dob', $user->profile->dob) }}">
                                            @error('dob')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Gender -->
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Gender *</label>
                                            <select id="gender" name="gender"
                                                    class="form-control @error('gender') is-invalid @enderror">
                                                <option value="">--Select--</option>
                                                <option value="male" {{ old('gender', $user->profile->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender', $user->profile->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old('gender', $user->profile->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('gender')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Phone -->
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone *</label>
                                            <input type="text" id="phone" name="phone"
                                                   class="form-control @error('phone') is-invalid @enderror"
                                                   value="{{ old('phone', $user->profile->phone) }}">
                                            @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" id="email" name="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   value="{{ old('email', $user->email) }}">
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
                                                    class="form-control @error('country_of_residence') is-invalid @enderror">
                                                <option value="">--Select--</option>
                                                <option value="Liberia" {{ old('country_of_residence', $user->profile->country_of_residence) == 'Liberia' ? 'selected' : '' }}>Liberia</option>
                                                <option value="Other" {{ old('country_of_residence', $user->profile->country_of_residence) == 'Other' ? 'selected' : '' }}>Other</option>
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
                                                        $counties = ['Bomi','Bong','Gbarpolu','Grand Bassa','Grand Cape Mount','Grand Gedeh','Grand Kru','Lofa','Margibi','Maryland','Montserrado','Nimba','River Cess','River Gee','Sinoe'];
                                                    @endphp
                                                    @foreach($counties as $county)
                                                        <option value="{{ $county }}" {{ old('county', $user->profile->county) == $county ? 'selected' : '' }}>{{ $county }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-2">
                                                <label for="district" class="form-label">District *</label>
                                                <select id="district" name="district" class="form-control">
                                                    <option value="">--Select district--</option>
                                                    @for($i=1; $i<=17; $i++)
                                                        <option value="{{ $i }}" {{ old('district', $user->profile->district) == $i ? 'selected' : '' }}> {{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Other Country Fields -->
                                        <div id="other-fields" class="mb-3" style="display:none;">
                                            <div class="mb-2">
                                                <label for="country" class="form-label">Current Country *</label>
                                                <input type="text" id="country" name="country"
                                                       class="form-control" value="{{ old('country', $user->profile->country) }}">
                                            </div>
                                            <div class="mb-2">
                                                <label for="address" class="form-label">Address *</label>
                                                <input type="text" id="address" name="address"
                                                       class="form-control" value="{{ old('address', $user->profile->address) }}">
                                            </div>
                                            <div class="mb-2">
                                                <label for="state" class="form-label">State/Province/Region *</label>
                                                <input type="text" id="state" name="state"
                                                       class="form-control" value="{{ old('state', $user->profile->state) }}">
                                            </div>
                                            <div class="mb-2">
                                                <label for="postal_code" class="form-label">ZIP/Postcode *</label>
                                                <input type="text" id="postal_code" name="postal_code"
                                                       class="form-control" value="{{ old('postal_code', $user->profile->postal_code) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button class="btn btn-primary w-100" type="submit">Update Member</button>
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
    const countrySelect = document.getElementById('country_of_residence');
    const liberiaFields = document.querySelectorAll("#liberia-fields select, #liberia-fields input");
    const otherFields   = document.querySelectorAll("#other-fields select, #other-fields input");

    function toggleFields() {
        let selected = countrySelect.value;

        if (selected === "Liberia") {
            document.getElementById('liberia-fields').style.display = 'block';
            document.getElementById('other-fields').style.display = 'none';

            liberiaFields.forEach(el => el.setAttribute("required", "required"));
            otherFields.forEach(el => el.removeAttribute("required"));

        } else if (selected === "Other") {
            document.getElementById('liberia-fields').style.display = 'none';
            document.getElementById('other-fields').style.display = 'block';

            otherFields.forEach(el => el.setAttribute("required", "required"));
            liberiaFields.forEach(el => el.removeAttribute("required"));

        } else {
            document.getElementById('liberia-fields').style.display = 'none';
            document.getElementById('other-fields').style.display = 'none';

            liberiaFields.forEach(el => el.removeAttribute("required"));
            otherFields.forEach(el => el.removeAttribute("required"));
        }
    }

    countrySelect.addEventListener('change', toggleFields);
    window.addEventListener('DOMContentLoaded', toggleFields);
</script>
@endpush

