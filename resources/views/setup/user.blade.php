@extends('adminlte::page')

@section('title', 'User')

@section('content_header')
    <h1>User</h1>
@endsection

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


@section('content')
    <x-button-add idTarget="#modalAddUser" text="Tambah User" />

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Dapur</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->nama }}</td>
                        <td>{{ $user->email }}</td>
                        <td>•••••••</td>
                        <td>{{ $user->kitchen?->nama ?? '-' }}</td>
                        <td>{{ $user->role }}</td>
                        <td>

                            {{-- Tombol Edit --}}
                            <button class="btn btn-warning btn-sm"
                                data-toggle="modal"
                                data-target="#modalEditUser{{ $user->id }}">
                                Edit
                            </button>

                            {{-- Tombol Hapus --}}
                            <x-button-delete
                                idTarget="#modalDeleteUser"
                                formId="formDeleteUser"
                                action="{{ route('setup.user.destroy', $user->id) }}"
                                text="Hapus"
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    {{-- ===========================
         MODAL TAMBAH USER
       =========================== --}}
    <x-modal-form
        id="modalAddUser"
        title="Tambah User"
        action="{{ route('setup.user.store') }}"
        submiText="Simpan"
    >
        @csrf

        <div class="form-group">
            <label>Nama</label>
            <input type="text" placeholder="Nama User"
                class="form-control" name="nama" id="namaInput" required>
        </div>

        <div class="form-group">
            <label>Email (Otomatis)</label>
            <input type="text" id="autoEmail" class="form-control"
                name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" placeholder="Masukkan password" class="form-control" name="password" required>
        </div>

        <div class="form-group">
            <label>Dapur</label>
            <select class="form-control" name="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select class="form-control" name="role" required>
                <option value="admin">Admin</option>
                <option value="superadmin">Superadmin</option>
            </select>
        </div>

    </x-modal-form>


    {{-- ===========================
         MODAL EDIT USER
       =========================== --}}
    @foreach ($users as $user)
    <x-modal-form
        id="modalEditUser{{ $user->id }}"
        title="Edit User"
        action="{{ route('setup.user.update', $user->id) }}"
        submiText="Update"
    >
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nama</label>
            <input type="text" class="form-control" name="nama"
                   value="{{ $user->nama }}" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" class="form-control" name="email"
                   value="{{ $user->email }}" required>
        </div>

        <div class="form-group">
            <label>Password (kosongkan jika tidak diganti)</label>
            <input type="password" class="form-control" name="password">
        </div>

        <div class="form-group">
            <label>Dapur</label>
            <select class="form-control" name="kitchen_id" required>
                @foreach($kitchens as $kitchen)
                    <option 
                        value="{{ $kitchen->id }}"
                        {{ $user->kitchen_id == $kitchen->id ? 'selected' : '' }}
                    >
                        {{ $kitchen->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select class="form-control" name="role" required>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="superadmin" {{ $user->role == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
            </select>
        </div>
    </x-modal-form>

    <x-modal-delete 
        id="modalDeleteUser"
        formId="formDeleteUser"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
    />
    @endforeach

@endsection


{{-- ===========================
     SCRIPT AUTO GENERATE EMAIL
   =========================== --}}
@section('js')
<script>
function generateEmail(name) {
    let email = name.toLowerCase()
        .replace(/[^a-z0-9 ]/g, '')  // hapus karakter selain huruf/angka/spasi
        .replace(/\s+/g, '.');       // spasi menjadi titik

    return email + '@gmail.com';
}

document.addEventListener("DOMContentLoaded", function () {

    const namaInput = document.getElementById("namaInput");
    const emailInput = document.getElementById("autoEmail");

    if (namaInput && emailInput) {
        namaInput.addEventListener("input", function() {
            emailInput.value = generateEmail(this.value);
        });
    }

});
</script>
@endsection
