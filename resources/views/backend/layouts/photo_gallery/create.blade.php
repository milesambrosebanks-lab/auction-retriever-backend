@extends('backend.app', ['title' => 'Create Gallery'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Gallery</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Gallery</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Create Gallery</h3>
                                <div class="card-options">
                                    <a href="javascript:window.history.back()" class="btn btn-sm btn-primary">Back</a>
                                </div>
                            </div>
                            <div class="card-body border-0">
                                <form class="form form-horizontal" method="POST" action="{{ route('admin.image-gallery.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        <div class="row">
                                            <div class="col-md-12">
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
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                    <input type="file"
                                                        class="dropify form-control @error('thumbnail') is-invalid @enderror"
                                                        name="thumbnail" id="thumbnail">
                                                    @error('thumbnail')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>



                                        <div class="row" id="contentContainer">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="description" class="form-label">Description:</label>
                                                    <textarea class="summernote form-control @error('description') is-invalid @enderror" name="description" id="description"
                                                        placeholder="Enter here description" rows="3">{{ old('description') ?? '' }}</textarea>
                                                    @error('content')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="images" class="form-label">Upload Images:</label>
                                                    <input type="file"
                                                        class="form-control @error('images') is-invalid @enderror"
                                                        name="images[]" id="images" multiple accept="image/*">

                                                    @error('images')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Image preview container -->
                                                <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                            </div>
                                        </div>




                                        <div class="form-group mt-4">
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
        const input = document.getElementById('images');
        const preview = document.getElementById('imagePreview');
        let filesArray = [];

        input.addEventListener('change', function(event) {
            Array.from(event.target.files).forEach(file => {
                filesArray.push(file); // Save file in array for later submission
                const reader = new FileReader();

                reader.onload = function(e) {
                    const container = document.createElement('div');
                    container.style.position = 'relative';
                    container.style.display = 'inline-block';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '160px';
                    img.style.height = '130px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.classList.add('shadow-sm');

                    // Remove button
                    const removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '2px';
                    removeBtn.style.right = '6px';
                    removeBtn.style.background = 'rgba(255,0,0,0.8)'; // red background
                    removeBtn.style.color = '#fff';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.padding = '2px 6px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.fontWeight = 'bold';
                    removeBtn.style.fontSize = '16px'; // slightly bigger for visibility

                    removeBtn.addEventListener('click', function() {
                        const index = filesArray.indexOf(file);
                        if (index > -1) {
                            filesArray.splice(index, 1);
                        }
                        container.remove();
                        updateInputFiles();
                    });

                    container.appendChild(img);
                    container.appendChild(removeBtn);
                    preview.appendChild(container);
                }

                reader.readAsDataURL(file);
            });

            updateInputFiles();
        });

        // Function to update the input's files list after removing
        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }
    </script>
@endpush
