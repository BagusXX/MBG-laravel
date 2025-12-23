@extends('adminlte::page')

@section('title', 'Role & Permission')

@section('css')
<link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
<h1>Role & Permission Management</h1>
@endsection

@section('content')

<x-notification-pop-up />

<div class="mb-3">
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddRole">
        <i class="fas fa-user-shield"></i> Tambah Role
    </button>

    <button class="btn btn-success" data-toggle="modal" data-target="#modalAddPermission">
        <i class="fas fa-key"></i> Tambah Permission
    </button>
</div>

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
                @foreach($roles as $role)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $role->name }}</strong></td>

                    <td>
                        @forelse($role->permissions as $permission)
                            <span class="badge badge-info">{{ $permission->name }}</span>
                        @empty
                            <span class="badge badge-secondary">Tidak ada</span>
                        @endforelse
                    </td>

                    <td>
                        <button class="btn btn-warning btn-sm"
                            data-toggle="modal"
                            data-target="#modalEditRole{{ $role->id }}">
                            Edit
                        </button>

                        <form method="POST"
                            action="{{ route('setup.role.destroy', $role->id) }}"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus role ini?')"
                                class="btn btn-danger btn-sm">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

{{-- ================= MODAL ADD ROLE ================= --}}
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

        <label>Permission</label>
        <div class="row">
            @foreach($permissions as $p)
            <div class="col-md-4">
                <div class="form-check">
                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}">
                    <label>{{ $p->name }}</label>
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

{{-- ================= MODAL EDIT ROLE ================= --}}
@foreach($roles as $role)
<div class="modal fade" id="modalEditRole{{ $role->id }}">
<div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('setup.role.update', $role->id) }}">
@csrf
@method('PUT')
<div class="modal-content">
    <div class="modal-header">
        <h5>Edit Role</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
        <div class="form-group">
            <label>Nama Role</label>
            <input type="text" name="name" class="form-control"
                value="{{ $role->name }}" required>
        </div>

        <label>Permission</label>
        <div class="row">
            @foreach($permissions as $p)
            <div class="col-md-4">
                <div class="form-check">
                    <input type="checkbox"
                        name="permissions[]"
                        value="{{ $p->name }}"
                        {{ $role->hasPermissionTo($p->name) ? 'checked' : '' }}>
                    <label>{{ $p->name }}</label>
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

{{-- ================= MODAL ADD PERMISSION ================= --}}
<div class="modal fade" id="modalAddPermission">
<div class="modal-dialog">
<form method="POST" action="{{ route('setup.permission.store') }}">
@csrf
<div class="modal-content">
    <div class="modal-header">
        <h5>Tambah Permission</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
        <div class="form-group">
            <label>Nama Permission</label>
            <input type="text"
                name="name"
                class="form-control"
                placeholder="contoh: bahan-baku.view"
                required>
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
