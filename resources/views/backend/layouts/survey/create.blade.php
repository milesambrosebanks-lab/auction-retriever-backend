@extends('backend.app', ['title' => 'Create Survey'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Survey</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Survey</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Create Survey</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body border-0">
                                <form class="form form-horizontal" method="POST" action="{{ route('admin.survey.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="title" class="form-label">Title:</label>
                                                    <input type="text"
                                                        class="form-control @error('title') is-invalid @enderror"
                                                        name="title" placeholder="Enter here title" id="title"
                                                        value="{{ old('title') ?? '' }}">
                                                    @error('title')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="end_date" class="form-label">End Date:</label>
                                                    <input type="date"
                                                        class="form-control @error('end_date') is-invalid @enderror"
                                                        name="end_date" id="end_date" value="{{ old('end_date') ?? '' }}">
                                                    @error('end_date')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="description" class="form-label">Description:</label>
                                                    <textarea class="summernote form-control @error('description') is-invalid @enderror" name="description" id="description"
                                                        placeholder="Enter here description" rows="3">{{ old('description') ?? '' }}</textarea>
                                                    @error('description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" id="opinion_list">
                                            @error('opinion')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            @for ($i = 0; $i < 4; $i++)
                                                <div class="col-md-12 opinion-item">
                                                    <div class="form-group">
                                                        <label class="form-label">Opinion({{ $i + 1 }}):</label>
                                                        <input type="text" class="form-control" name="opinion[]"
                                                            placeholder="Enter here opinion">
                                                    </div>
                                                    <div class="form-group">
                                                        <button onclick="removeNewOpinion(this)" class="btn btn-danger"
                                                            type="button">Remove</button>
                                                    </div>

                                                </div>
                                            @endfor
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12 text-end">
                                                <div class="form-group">
                                                    <button onclick="addOpinion()" class="btn btn-primary"
                                                        type="button">Add Opinion</button>
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
        let opinionCount = 4;

        function addOpinion() {
            const opinionList = document.getElementById('opinion_list');
            const row = document.createElement('div');
            row.classList.add('row', 'opinion-item');

            row.innerHTML = `
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Opinion(${++opinionCount}):</label>
                    <input type="text" class="form-control" name="opinion[]" placeholder="Enter here opinion">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <button onclick="removeNewOpinion(this)" class="btn btn-danger" type="button">Remove</button>
                </div>
            </div>
        `;

            opinionList.appendChild(row);
        }

        function removeNewOpinion(button) {
            const item = button.closest('.opinion-item');
            if (item) item.remove();
        }
    </script>
@endpush
