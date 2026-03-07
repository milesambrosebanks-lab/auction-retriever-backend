@extends('backend.app', ['title' => 'Create Area of Focus'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Area of Focus</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Area of Focus</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card post-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">Create Area of Focus</h3>
                            <div class="card-options">
                                <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body border-0">
                            <form class="form form-horizontal" method="POST" action="{{ route('admin.area.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title" class="form-label">Title:</label>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" placeholder="Enter here title" id="title" value="{{ old('title') ?? '' }}">
                                                @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

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
<script>
    $(document).ready(function() {
        $('select[name="category_id"]').on('change', function() {
            NProgress.start();
            var category_id = $(this).val();
            if (category_id) {
                $.ajax({
                    url: `{{ route('ajax.subcategory', ':category_id') }}`.replace(':category_id', category_id),
                    type: "GET",
                    success: function(res) {
                        NProgress.done();
                        let $html = '<option value="">Select a Subcategory ID</option>';
                        res.data.subcategories.forEach(function(subcategory) {
                            $html += '<option value="' + subcategory.id + '">' + subcategory.name + '</option>';
                        });
                        if ($('select[name="subcategory_id"]').val() != '') {
                            $('select[name="subcategory_id"]').html($html).val($('select[name="subcategory_id"]').val());
                        } else {
                            $('select[name="subcategory_id"]').html($html);
                        }
                    }
                });
            } else {
                $('select[name="subcategory_id"]').empty();
            }
        });
    });
</script>
@endpush
