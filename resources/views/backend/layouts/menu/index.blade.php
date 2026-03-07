<?php
function adminMenu($menus, $groupedMenus)
{
    echo '<ul id="sortable" class="list-group">';
    foreach ($menus as $menu) {
        $menuJson = htmlspecialchars(json_encode($menu), ENT_QUOTES, 'UTF-8');

        $name = $menu->name;
        if (strlen($name) > 20) {
            $name = substr($name, 0, 17) . '...';
        }

        echo '<li onclick="openEditor(' . $menuJson . ')" class="list-group-item d-flex justify-content-between align-items-center mb-3 bg-success" style="border-radius: 5px; cursor: pointer;">';

        // Flex row: title on left, delete icon on right
        echo '<div class="d-flex justify-content-between w-100 align-items-center">';
        echo '<h5 class="mb-0">' . $name . '</h5>';
        echo '<a href="' . route('admin.menu.destroy', $menu->id) . '" onclick="event.stopPropagation(); return confirm(\'Are you sure you want to delete this menu?\')" class="text-white">';
        echo '<i class="fe fe-trash-2"></i>';
        echo '</a>';
        echo '</div>';

        echo '</li>';

        // Render children
        if (isset($groupedMenus[$menu->id])) {
            echo '<li class="ms-5">';
            adminMenu($groupedMenus[$menu->id], $groupedMenus);
            echo '</li>';
        }
    }
    echo '</ul>';
}

?>

@extends('backend.app', ['title' => 'Menu'])

@push('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
@endpush


@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">


            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $crud ? ucwords(str_replace('_', ' ', $crud)) : 'N/A' }}</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url("admin/dashboard") }}"><i class="fe fe-home me-2 fs-14"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Menu</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card product-sales-main">
                        <div class="card-header border-bottom">
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="button" class="btn btn-danger"><a href="#">Import</a></button>
                                <button type="button" class="btn btn-warning"><a href="#">Export</a></button>
                            </div>
                            <div class="card-options">
                                <button class="btn btn-primary" onclick="openCreate()" data-bs-toggle="modal" data-bs-target="#exampleModal">Create</button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($menus->count() > 0)
                            @php
                            adminMenu($groupedMenus[null] ?? $groupedMenus[0] ?? [], $groupedMenus);
                            @endphp
                            @else
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">No data found</h5>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div><!-- COL END -->

                <div class="col-md-6">
                    <div class="tab-content">
                        <div class="tab-pane active show" id="editProfile">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h3 class="card-title mb-0" id="formTitle">Create Menu</h3>
                                </div>
                                <div class="card-body border-0">
                                    <form id="form" name="form" class="form form-horizontal" method="post" action="{{ route('admin.menu.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row mb-4">

                                            <div class="form-group">
                                                <label for="name" class="form-label">Name:</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Name" id="name" value="{{ old('name') }}">
                                                @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="parent_id" class="form-label">Select Parent:</label>
                                                <select class="form-control @error('parent_id') is-invalid @enderror" name="parent_id" id="parent_id">
                                                    <option value="">Select Parent</option>
                                                    @foreach($menus as $childs)
                                                    <option value="{{ $childs->id }}" {{ old('parent_id') == $childs->parent_id ? 'selected' : '' }}>{{ $childs->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('parent_id')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="order" class="form-label">Order:</label>
                                                <input type="number" class="form-control @error('order') is-invalid @enderror" name="order" placeholder="order" id="order" value="{{ old('order') }}">
                                                @error('order')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
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
                </div><!-- COL END -->

            </div>
            <!-- ROW-4 END -->


        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script>
    function openCreate() {
        $('#formTitle').text('Create Menu');
        $('#form').attr('action', "{{ route('admin.menu.store') }}");
        $('#name').val('');
        $('#parent_id').val('');
    }

    function openEditor(menu) {
        $('#formTitle').text('Edit Menu');
        $('#form').attr('action', "{{ route('admin.menu.update', ':id') }}".replace(':id', menu.id));
        $('#name').val(menu.name);
        $('#order').val(menu.order);
        $('#parent_id').val(menu.parent_id);
        $('#parent_id option[value="' + menu.id + '"]').remove();
    }
</script>
@endpush