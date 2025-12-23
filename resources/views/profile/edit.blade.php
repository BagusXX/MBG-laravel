@extends('adminlte::page')

@section('title', 'Profile')

@section('content_header')
    <h1>Profile</h1>
@endsection

@section('content')

{{-- UPDATE PROFILE --}}
<div class="card mb-3">
    <div class="card-header">
        <strong>Informasi Profil</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-group mb-2">
                <label>Nama</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name', $user->name) }}"
                       required>
            </div>

            <div class="form-group mb-2">
                <label>Email</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', $user->email) }}"
                       required>
            </div>

            <button class="btn btn-primary mt-2">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

{{-- DELETE ACCOUNT --}}
<div class="card border-danger">
    <div class="card-header bg-danger text-white">
        Hapus Akun
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')

            <div class="form-group mb-2">
                <label>Konfirmasi Password</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Password saat ini"
                       required>
            </div>

            <button class="btn btn-danger">
                Hapus Akun
            </button>
        </form>
    </div>
</div>

@endsection
