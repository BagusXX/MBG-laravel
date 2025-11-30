@extends('adminlte::page')

@section('title', 'User')

@section('content_header')
    <h1>User</h1>
@endsection

@section('content')
    <x-button-add
        idTarget="#modalAddUser"
        text="Tambah User"   
    />
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Dapur</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Reza Rahadian</td>
                        <td>rezarahadian352</td>
                        <td>rezadapurtembalang1</td>
                        <td>Dapur A Tembalang</td>
                        <td>Admin</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-form
        id="modalAddUser"
        title="Tambah User"
        action="#"
        submiText="Simpan"
    >
        <div class="form-group">
            <label>Nama</label>
            <input type="text" placeholder="Praz Teguh" class="form-control" name="nama" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" placeholder="prazteguh395" class="form-control" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" placeholder="Masukkan password" class="form-control" name="password" required>
        </div>
        <div class="form-group">
            <label>Dapur</label>
            <input type="text" placeholder="Dapur A Tembalang" class="form-control" name="dapur" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select class="form-control" name="role" required>
                <option value="" disabled selected>Pilih Role</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Superadmin</option>
            </select>
        </div>
    </x-modal-form>
@endsection
