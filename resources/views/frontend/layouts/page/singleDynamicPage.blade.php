@extends('frontend.app', ['title' => $dynamicPage->title])

@section('content')
<!-- HERO SECTION -->
<section class="py-5 bg-light border-bottom">
    <div class="container text-center">
        <div class="mb-3">
            <i class="bi bi-file-earmark-text display-4 text-primary"></i>
        </div>
        <h1 class="fw-bold mb-3">{{ $dynamicPage->title }}</h1>
        <p class="text-muted lead">Updated content directly from our platform</p>
    </div>
</section>

<!-- PAGE CONTENT SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="content-area">
                            {!! $dynamicPage->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Content styling */
.content-area {
    line-height: 1.6;
    color: #333;
}

.content-area h1,
.content-area h2,
.content-area h3,
.content-area h4,
.content-area h5,
.content-area h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.content-area h1 {
    font-size: 2.5rem;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.content-area h2 {
    font-size: 2rem;
    color: #495057;
}

.content-area h3 {
    font-size: 1.5rem;
}

.content-area p {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}

.content-area ul,
.content-area ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.content-area li {
    margin-bottom: 0.5rem;
}

.content-area blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 1rem 1.5rem;
}

.content-area code {
    background-color: #f8f9fa;
    color: #e83e8c;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.9em;
}

.content-area pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
    margin: 1.5rem 0;
    border: 1px solid #dee2e6;
}

.content-area img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
    margin: 1.5rem 0;
}

.content-area table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    border: 1px solid #dee2e6;
}

.content-area th,
.content-area td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.content-area th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.content-area tr:nth-child(even) {
    background-color: #f8f9fa;
}

.content-area a {
    color: #007bff;
    text-decoration: none;
}

.content-area a:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .content-area h1 {
        font-size: 2rem;
    }

    .content-area h2 {
        font-size: 1.5rem;
    }

    .content-area h3 {
        font-size: 1.25rem;
    }

    .content-area p {
        font-size: 1rem;
    }

    .card-body {
        padding: 2rem !important;
    }
}
</style>
@endsection
