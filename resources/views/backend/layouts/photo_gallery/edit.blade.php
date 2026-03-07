@extends('backend.app', ['title' => 'Create/Edit Gallery'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">Gallery</h1>
                    <div class="ms-auto pageheader-btn">
                        <a href="javascript:window.history.back()" class="btn btn-primary">Back</a>
                    </div>
                </div>

                <div class="row" id="user-profile">
                    <div class="col-lg-12">
                        <div class="card post-sales-main">
                            <div class="card-header border-bottom">
                                <h3 class="card-title mb-0">Create/Edit Gallery</h3>
                            </div>
                            <div class="card-body border-0">
                                <form class="form form-horizontal" method="POST"
                                    action="{{ route('admin.image-gallery.update', $gallery->id) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        <!-- Title -->
                                        <div class="col-md-12 mb-3">
                                            <label for="title" class="form-label">Title:</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" id="title" placeholder="Enter title"
                                                value="{{ old('title') ?? ($gallery->title ?? '') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Thumbnail (single) -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="thumbnail" class="form-label">Thumbnail:</label>
                                                    <input type="file"
                                                        class="dropify form-control @error('thumbnail') is-invalid @enderror"
                                                        name="thumbnail" id="thumbnail"
                                                        @if (!empty($gallery->thumbnail)) data-default-file="{{ $gallery->thumbnail }}" @endif>
                                                    @error('thumbnail')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="col-md-12 mb-3">
                                            <label for="description" class="form-label">Description:</label>
                                            <textarea class="summernote form-control @error('description') is-invalid @enderror" name="description" id="description"
                                                rows="3" placeholder="Enter description">{{ old('description') ?? ($gallery->description ?? '') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Multiple Images -->
                                        <div class="col-md-12 mb-3">
                                            <label for="images" class="form-label">Upload Images:</label>
                                            <input type="file" class="form-control @error('images') is-invalid @enderror"
                                                name="images[]" id="images" multiple accept="image/*">
                                            @error('images')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror

                                            <!-- Image preview container -->
                                            <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2">
                                                @if (!empty($gallery->images))
                                                    @foreach ($gallery->images as $img)
                                                        <div class="position-relative d-inline-block"
                                                            id="image-{{ $img->id }}">
                                                            <img src="{{ $img->image }}"
                                                                style="width:120px; height:120px; object-fit:cover; border-radius:8px;"
                                                                class="shadow-sm">
                                                            <span class="remove-image-btn" data-id="{{ $img->id }}"
                                                                style="position:absolute; top:2px; right:6px; background:red; color:#fff; border-radius:50%; padding:2px 6px; cursor:pointer; font-weight:bold;">&times;</span>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                        </div>

                                        <div class="col-md-12">
                                            <button class="btn btn-primary" type="submit">Submit</button>
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
@endsection

@push('scripts')
    <script>
        const input = document.getElementById('images');
        const preview = document.getElementById('imagePreview');
        let filesArray = [];

        // Existing previews (edit mode)
        document.querySelectorAll('.remove-image-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });

        // Add new images
        input.addEventListener('change', function(event) {
            Array.from(event.target.files).forEach(file => {
                filesArray.push(file);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.createElement('div');
                    container.style.position = 'relative';
                    container.style.display = 'inline-block';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '120px';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.classList.add('shadow-sm');

                    const removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '2px';
                    removeBtn.style.right = '6px';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = '#fff';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.padding = '2px 6px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.fontWeight = 'bold';
                    removeBtn.addEventListener('click', function() {
                        const index = filesArray.indexOf(file);
                        if (index > -1) filesArray.splice(index, 1);
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

        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }

        $(document).on('click', '.remove-image-btn', function() {
            let imageId = $(this).data('id');
            let parentDiv = $('#image-' + imageId);

            if (confirm("Are you sure you want to delete this image?")) {
                $.ajax({
                    url: "/admin/photo-gallery/image/" + imageId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            parentDiv.remove();
                            alert(response.message);
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert("Something went wrong!");
                    }
                });
            }
        });
    </script>
@endpush
