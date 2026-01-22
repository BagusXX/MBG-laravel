@extends('adminlte::page')

@section('title', 'User')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
    {{-- Style tambahan agar area checkbox lebih rapi --}}
    <style>
        .checkbox-group-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
@endsection

@section('content_header')
    <h1>User Management</h1>
@endsection

@section('content')
    {{-- Notifikasi Error Validasi
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}

    <x-button-add idTarget="#modalAddUser" text="Tambah User" />

    <x-notification-pop-up />

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Dapur</th>
                        <th>Region</th>
                        <th>Role</th>
                        <th>Status</th> {{-- KOLOM BARU --}}
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php 
                        $no = ($users->currentPage() - 1) * $users->perPage() + 1; 
                    @endphp
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $no++ }}</td>
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

                            {{-- MENAMPILKAN STATUS --}}
                            <td>
                                @if($user->status === 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($user->status === 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-warning">Menunggu</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex flex-wrap align-items-center">
                                    
                                    {{-- HANYA MUNCUL JIKA STATUS MENUNGGU --}}
                                    @if($user->status === 'menunggu')
                                        
                                        {{-- 1. TOMBOL APPROVE --}}
                                        <form action="{{ route('setup.user.approve', $user->id) }}" method="POST" class="mr-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm mb-2" 
                                                title="Setujui User"
                                                onclick="return confirm('Apakah Anda yakin ingin menyetujui user ini?')">
                                                Setujui
                                            </button>
                                        </form>

                                        {{-- 2. TOMBOL REJECT --}}
                                        <form action="{{ route('setup.user.reject', $user->id) }}" method="POST" class="mr-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm mb-2" 
                                                title="Tolak User"
                                                onclick="return confirm('Apakah Anda yakin ingin MENOLAK user ini?')">
                                                Tolak
                                            </button>
                                        </form>

                                    @endif
                                    {{-- END IF MENUNGGU --}}

                                    {{-- 3. TOMBOL EDIT (Selalu muncul) --}}
                                    <button class="btn btn-warning btn-sm mr-1" data-toggle="modal"
                                        data-target="#modalEditUser{{ $user->id }}" title="Edit">
                                        Edit
                                    </button>

                                    {{-- 4. TOMBOL HAPUS (Selalu muncul kecuali superadmin) --}}
                                    @if(!$user->hasRole('superadmin'))
                                        <x-button-delete idTarget="#modalDeleteUser{{ $user->id }}"
                                            formId="formDeleteUser{{ $user->id }}"
                                            action="{{ route('setup.user.destroy', $user->id) }}"
                                            text="Hapus" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
             {{ $users->links('pagination::bootstrap-4') }}
            </div>
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

        {{-- AREA INPUT DAPUR CHECKBOX (ADD) --}}
        <div class="form-group">
            <label>Pilih Dapur</label>
            <div class="checkbox-group-container">
                <div class="row">
                    @foreach($kitchens as $kitchen)
                        <div class="col-md-6"> 
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                    name="kitchen_kode[]" 
                                    value="{{ $kitchen->kode }}" 
                                    id="add_kitchen_{{ $kitchen->kode }}">
                                <label class="form-check-label" for="add_kitchen_{{ $kitchen->kode }}">
                                    {{ $kitchen->nama }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <small class="text-muted">* Bisa memilih lebih dari satu dapur</small>
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

            {{-- AREA INPUT DAPUR CHECKBOX (EDIT) --}}
            <div class="form-group">
                <label>Pilih Dapur</label>
                <div class="checkbox-group-container">
                    <div class="row">
                        @foreach($kitchens as $kitchen)
                            <div class="col-md-6">
                                <div class="form-check">
                                    {{-- Cek apakah user memiliki dapur ini (pivot), jika ya maka checked --}}
                                    <input class="form-check-input" type="checkbox" 
                                        name="kitchen_kode[]" 
                                        value="{{ $kitchen->kode }}" 
                                        id="edit_kitchen_{{ $user->id }}_{{ $kitchen->kode }}"
                                        {{ $user->kitchens->contains('kode', $kitchen->kode) ? 'checked' : '' }}>
                                    
                                    <label class="form-check-label" for="edit_kitchen_{{ $user->id }}_{{ $kitchen->kode }}">
                                        {{ $kitchen->nama }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
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
        // LOGIKA AUTO EMAIL GENERATOR
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