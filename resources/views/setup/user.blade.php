@extends('adminlte::page')

@section('title', 'User')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>User</h1>
@endsection

@section('content')
    {{-- Notifikasi Error Validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-button-add idTarget="#modalAddUser" text="Tambah User" />

    <x-notification-pop-up />

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Dapur</th>
                        <th>Region</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>

                            {{-- MENAMPILKAN KITCHEN --}}
                            <td>
                                @if($user->kitchens->isNotEmpty())
                                    @foreach($user->kitchens as $k)
                                        <span class="badge badge-info">{{ $k->nama }}</span>
                                    @endforeach
                                @else
                                    <span class="badge badge-secondary">Tidak ada</span>
                                @endif
                            </td>

                            {{-- REGION --}}
                            {{-- REGION --}}
                            <td>
                                @if($user->kitchens->isNotEmpty())
                                    @foreach($user->kitchens->pluck('region.nama_region')->unique() as $region)
                                        <span class="badge badge-success">{{ $region }}</span>
                                    @endforeach
                                @else
                                    <span class="badge badge-secondary">Tidak ada</span>
                                @endif
                            </td>


                            {{-- MENAMPILKAN ROLE --}}
                            <td>
                                @if(!empty($user->getRoleNames()))
                                    @foreach($user->getRoleNames() as $roleName)
                                        <span class="badge badge-primary">{{ $roleName }}</span>
                                    @endforeach
                                @endif
                            </td>

                            <td>
                                {{-- Tombol Edit --}}
                                <button class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#modalEditUser{{ $user->id }}">
                                    Edit
                                </button>

                                {{-- Tombol Hapus --}}
                                @if(!$user->hasRole('superadmin'))
    <x-button-delete idTarget="#modalDeleteUser{{ $user->id }}"
        formId="formDeleteUser{{ $user->id }}"
        action="{{ route('setup.user.destroy', $user->id) }}"
        text="Hapus" />
@else
    <span class="badge badge-danger">Superadmin</span>
@endif

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
    <x-modal-form id="modalAddUser" title="Tambah User" action="{{ route('setup.user.store') }}" submiText="Simpan">
        @csrf

        <div class="form-group">
            <label>Nama</label>
            <input type="text" placeholder="Nama User" class="form-control" name="name" id="namaInput" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" id="autoEmail" class="form-control" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" placeholder="Masukkan password" class="form-control" name="password" required>
        </div>

        {{-- AREA INPUT DAPUR DINAMIS (ADD) --}}
        <div class="form-group">
            <label>Dapur</label>
            <div id="kitchen-wrapper-add">
                {{-- Default 1 Input --}}
                <div class="input-group mb-2">
                    <select class="form-control" name="kitchen_kode[]" required>
                        <option value="" disabled selected>Pilih Dapur</option>
                        @foreach($kitchens as $kitchen)
                            <option value="{{ $kitchen->kode }}">{{ $kitchen->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- Tombol Tambah --}}
            <button type="button" class="btn btn-success btn-sm mt-1" onclick="addKitchenRow('kitchen-wrapper-add')">
                <i class="fas fa-plus"></i> Tambah Dapur Lain
            </button>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select class="form-control" name="role" required>
                <option value="" disabled selected>Pilih Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

    </x-modal-form>


    {{-- ===========================
    LOOPING MODAL EDIT & DELETE
    =========================== --}}
    @foreach ($users as $user)

        {{-- MODAL EDIT --}}
        <x-modal-form id="modalEditUser{{ $user->id }}" title="Edit User" action="{{ route('setup.user.update', $user->id) }}"
            submiText="Update">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama</label>
                <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
            </div>

            <div class="form-group">
                <label>Password <small>(Kosongkan jika tidak ingin mengganti)</small></label>
                <input type="password" class="form-control" name="password">
            </div>

            {{-- AREA INPUT DAPUR DINAMIS (EDIT) --}}
            <div class="form-group">
                <label>Dapur</label>
                <div id="kitchen-wrapper-edit-{{ $user->id }}">

                    {{-- Loop data dapur yang sudah ada --}}
                    @foreach($user->kitchens as $userKitchen)
                        <div class="input-group mb-2">
                            <select class="form-control" name="kitchen_kode[]" required>
                                @foreach($kitchens as $kitchen)
                                    <option value="{{ $kitchen->kode }}" {{ $userKitchen->kode == $kitchen->kode ? 'selected' : '' }}>
                                        {{ $kitchen->nama }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- Tombol Hapus Baris --}}
                            <div class="input-group-append">
                                <button class="btn btn-danger" type="button" onclick="removeRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    {{-- Jika user belum punya dapur, tampilkan 1 kosong --}}
                    @if($user->kitchens->isEmpty())
                        <div class="input-group mb-2">
                            <select class="form-control" name="kitchen_kode[]" required>
                                <option value="" disabled selected>Pilih Dapur</option>
                                @foreach($kitchens as $kitchen)
                                    <option value="{{ $kitchen->kode }}">{{ $kitchen->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                </div>

                {{-- Tombol Tambah --}}
                <button type="button" class="btn btn-success btn-sm mt-1"
                    onclick="addKitchenRow('kitchen-wrapper-edit-{{ $user->id }}')">
                    <i class="fas fa-plus"></i> Tambah Dapur Lain
                </button>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select class="form-control" name="role" required>
                    <option value="" disabled selected>Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-modal-form>

        {{-- MODAL DELETE --}}
        @if(!$user->hasRole('superadmin'))
    <x-modal-delete
        id="modalDeleteUser{{ $user->id }}"
        formId="formDeleteUser{{ $user->id }}"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus user {{ $user->name }}?"
        confirmText="Hapus"
    />
@endif

    @endforeach

@endsection

@section('js')
    <script>
        // ==========================================
        // 1. LOGIKA DYNAMIC INPUT DAPUR
        // ==========================================

        // Simpan opsi select ke variabel JS agar mudah dicopy
        const kitchenOptions = `
                    <option value="" disabled selected>Pilih Dapur</option>
                    @foreach($kitchens as $kitchen)
                        <option value="{{ $kitchen->kode }}">{{ $kitchen->nama }}</option>
                    @endforeach
                `;

        function addKitchenRow(wrapperId) {
            let newRow = `
                        <div class="input-group mb-2">
                            <select class="form-control" name="kitchen_kode[]" required>
                                ${kitchenOptions}
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-danger" type="button" onclick="removeRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
            document.getElementById(wrapperId).insertAdjacentHTML('beforeend', newRow);
        }

        function removeRow(button) {
            button.closest('.input-group').remove();
        }

        // ==========================================
        // 2. LOGIKA AUTO EMAIL GENERATOR
        // ==========================================
        function generateEmail(name) {
            let email = name.toLowerCase()
                .replace(/[^a-z0-9 ]/g, '')  // hapus karakter aneh
                .replace(/\s+/g, '.');       // spasi jadi titik
            return email + '@gmail.com';
        }

        document.addEventListener("DOMContentLoaded", function () {
            const namaInput = document.getElementById("namaInput");
            const emailInput = document.getElementById("autoEmail");

            if (namaInput && emailInput) {
                namaInput.addEventListener("input", function () {
                    emailInput.value = generateEmail(this.value);
                });
            }
        });
    </script>
@endsection