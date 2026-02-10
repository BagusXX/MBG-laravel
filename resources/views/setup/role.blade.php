@extends('adminlte::page')

@section('title', 'Role & Permission')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
    <style>
        .fa-rotate-90 {
            transform: rotate(90deg);
            transition: 0.3s;
        }

        .btn-link:hover {
            text-decoration: none;
        }

        .card-header {
            cursor: pointer;
        }

        .permission-badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
    </style>
@endsection

@section('content_header')
    <h1>Role & Permission Management</h1>
@endsection

@section('content')
    <x-notification-pop-up />

    @can('setup.role.create')
        <div class="mb-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddRole">
                <i class="fas fa-user-shield"></i> Tambah Role
            </button>
            <button class="btn btn-success" data-toggle="modal" data-target="#modalAddPermission">
                <i class="fas fa-key"></i> Tambah Permission
            </button>
        </div>
    @endcan

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Role</th>
                        <th>Permission</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = ($roles->currentPage() - 1) * $roles->perPage() + 1;
                        $categories = [
                            'Master' => 'master.',
                            'Setup' => ['setup.', 'recipe.'],
                            'Transaction' => 'transaction.',
                            'Report' => 'report.',
                        ];
                    @endphp

                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td>
                                <div class="permission-badge-container">
                                    @forelse($role->permissions as $permission)
                                        <span class="badge badge-info">{{ $permission->name }}</span>
                                    @empty
                                        <span class="badge badge-secondary">Tidak ada</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @can('setup.role.update')
                                    <button class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#modalEditRole{{ $role->id }}">
                                        Edit
                                    </button>
                                @endcan

                                @can('setup.role.delete')
                                    {{-- Proteksi: Jika nama role bukan superadmin, tampilkan tombol hapus --}}
                                    @if (strtolower($role->name) !== 'superadmin')
                                        <form method="POST" action="{{ route('setup.role.destroy', $role->id) }}"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Hapus role ini?')" class="btn btn-danger btn-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-danger btn-sm disabled" title="Role ini diproteksi" disabled>
                                            Hapus
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD ROLE --}}
    <div class="modal fade" id="modalAddRole">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('setup.role.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Tambah Role</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Role</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <label>Permissions</label>
                        <div id="accordionAdd">
                            @foreach ($categories as $title => $prefix)
                                <div class="card mb-2">
                                    <div class="card-header bg-light p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button class="btn btn-link btn-block text-left text-dark font-weight-bold"
                                                type="button" data-toggle="collapse"
                                                data-target="#collapseAdd{{ $loop->index }}">
                                                <i class="fas fa-chevron-right mr-2"></i> {{ $title }}
                                            </button>
                                            <div class="custom-control custom-checkbox mr-3">
                                                <input type="checkbox" class="custom-control-input check-all"
                                                    id="checkAllAdd{{ $loop->index }}"
                                                    data-group="add-{{ $loop->index }}">
                                                <label class="custom-control-label"
                                                    for="checkAllAdd{{ $loop->index }}">All</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="collapseAdd{{ $loop->index }}" class="collapse" data-parent="#accordionAdd">
                                        <div class="card-body row">
                                            @foreach ($permissions as $p)
                                                @php
                                                    $match = is_array($prefix)
                                                        ? collect($prefix)->contains(
                                                            fn($pre) => str_starts_with($p->name, $pre),
                                                        )
                                                        : str_starts_with($p->name, $prefix);
                                                @endphp
                                                @if ($match)
                                                    <div class="col-md-4">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="permissions[]"
                                                                class="custom-control-input add-{{ $parentLoopIndex = $loop->parent->index }}"
                                                                id="permAdd{{ $p->id }}"
                                                                value="{{ $p->name }}">
                                                            <label class="custom-control-label font-weight-normal"
                                                                for="permAdd{{ $p->id }}">{{ $p->name }}</label>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT ROLE --}}
    @foreach ($roles as $role)
        <div class="modal fade" id="modalEditRole{{ $role->id }}">
            <div class="modal-dialog modal-lg">
                <form method="POST" action="{{ route('setup.role.update', $role->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header ">
                            <h5>Edit Role: {{ $role->name }}</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Nama Role</label>
                                <input type="text" name="name" class="form-control bg-light"
                                    value="{{ $role->name }}" readonly>
                                <small class="text-muted text-italic">*Nama role tidak dapat diubah
                                    data.</small>
                            </div>
                            <div id="accordionEdit{{ $role->id }}">
                                @foreach ($categories as $title => $prefix)
                                    <div class="card mb-2">
                                        <div class="card-header bg-light p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <button class="btn btn-link btn-block text-left text-dark font-weight-bold"
                                                    type="button" data-toggle="collapse"
                                                    data-target="#collapseEdit{{ $role->id }}{{ $loop->index }}">
                                                    <i class="fas fa-chevron-right mr-2"></i> {{ $title }}
                                                </button>
                                                <div class="custom-control custom-checkbox mr-3">
                                                    <input type="checkbox" class="custom-control-input check-all"
                                                        id="checkAllEdit{{ $role->id }}{{ $loop->index }}"
                                                        data-group="edit-{{ $role->id }}-{{ $loop->index }}">
                                                    <label class="custom-control-label"
                                                        for="checkAllEdit{{ $role->id }}{{ $loop->index }}">All</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="collapseEdit{{ $role->id }}{{ $loop->index }}" class="collapse"
                                            data-parent="#accordionEdit{{ $role->id }}">
                                            <div class="card-body row">
                                                @foreach ($permissions as $p)
                                                    @php
                                                        $match = is_array($prefix)
                                                            ? collect($prefix)->contains(
                                                                fn($pre) => str_starts_with($p->name, $pre),
                                                            )
                                                            : str_starts_with($p->name, $prefix);
                                                    @endphp
                                                    @if ($match)
                                                        <div class="col-md-4">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" name="permissions[]"
                                                                    class="custom-control-input edit-{{ $role->id }}-{{ $loop->parent->index }}"
                                                                    id="permEdit{{ $role->id }}{{ $p->id }}"
                                                                    value="{{ $p->name }}"
                                                                    {{ $role->hasPermissionTo($p->name) ? 'checked' : '' }}>
                                                                <label class="custom-control-label font-weight-normal"
                                                                    for="permEdit{{ $role->id }}{{ $p->id }}">{{ $p->name }}</label>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-warning">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- MODAL ADD PERMISSION --}}
    <div class="modal fade" id="modalAddPermission">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('setup.permission.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5>Tambah Permission</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Permission</label>
                            <input type="text" name="name" class="form-control"
                                placeholder="contoh: setup.user.view" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Handle Check All
            $(document).on('change', '.check-all', function() {
                let groupClass = $(this).data('group');
                $(`.${groupClass}`).prop('checked', $(this).prop('checked'));
            });

            // Rotate Icon on Collapse
            $('.collapse').on('show.bs.collapse', function() {
                $(this).prev('.card-header').find('.fa-chevron-right').addClass('fa-rotate-90');
            }).on('hide.bs.collapse', function() {
                $(this).prev('.card-header').find('.fa-chevron-right').removeClass('fa-rotate-90');
            });
        });
    </script>
@endsection
