@extends('adminlte::page')

@section('title', 'Profile')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-cog mr-2 text-secondary"></i> Pengaturan Profil</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Profil</li>
        </ol>
    </div>
@endsection

@section('content')
<div class="row">
    {{-- KOLOM KIRI: KARTU PROFIL & DAFTAR DAPUR --}}
    <div class="col-md-4">
        
        {{-- 1. Widget Kartu Profil --}}
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body box-profile">
                <div class="text-center">
                    {{-- Avatar otomatis berdasarkan inisial nama --}}
                    <img class="profile-user-img img-fluid img-circle"
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=128"
                         alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">{{ $user->email }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Bergabung</b> <a class="float-right">{{ $user->created_at->format('d M Y') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- 2. Widget Daftar Dapur (SCROLLABLE) --}}
        <div class="card card-info card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-utensils mr-1"></i> Daftar Dapur
                </h3>
                {{-- Badge jumlah total --}}
                <div class="card-tools">
                    <span class="badge badge-info" title="Total Dapur">{{ $kitchens->count() }} Unit</span>
                </div>
            </div>
            
            {{-- Bagian ini yang membuat list bisa di-scroll jika panjang --}}
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <ul class="nav nav-pills flex-column">
                    @forelse ($kitchens as $kitchen)
                        <li class="nav-item border-bottom">
                            <span class="nav-link text-dark py-3">
                                <i class="fas fa-store text-info mr-2"></i> {{ $kitchen->nama }}
                            </span>
                        </li>
                    @empty
                        <li class="nav-item p-4 text-center text-muted">
                            <i class="fas fa-info-circle mb-2" style="font-size: 20px;"></i><br>
                            Belum terhubung dengan dapur manapun.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: FORM EDIT --}}
    <div class="col-md-8">
        
        {{-- 1. Form Edit Informasi Umum --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h3 class="card-title text-primary"><i class="fas fa-edit mr-1"></i> Edit Informasi Akun</h3>
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- 2. Form Ganti Password --}}
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white border-bottom-0">
                <h3 class="card-title text-warning"><i class="fas fa-lock mr-1"></i> Ganti Password</h3>
            </div>
            <form id="changePasswordForm" method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="callout callout-warning py-2 mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle mr-1"></i> Gunakan kombinasi huruf, angka, dan simbol agar password lebih aman.
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Password Saat Ini</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="password" name="current_password" class="form-control" placeholder="********">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Password Baru</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" placeholder="********">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Konfirmasi Password</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="********">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-right">
                    <button type="button" class="btn btn-warning text-white" onclick="confirmChangePassword()">
                        <i class="fas fa-sync-alt mr-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- 3. Form Hapus Akun (Collapsed/Tertutup Default) --}}
        <div class="card card-danger card-outline collapsed-card mt-4 shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Hapus Akun</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i> Buka
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <i class="icon fas fa-ban"></i> Peringatan! Tindakan ini tidak dapat dibatalkan.
                </div>
                <p>Setelah akun dihapus, semua sumber daya dan data yang terkait akan dihapus secara permanen.</p>
                
                <form id="deleteAccountForm" method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div class="form-group">
                        <label>Masukkan Password Anda untuk konfirmasi</label>
                        <input type="password" name="password" class="form-control" placeholder="Password saat ini...">
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-block" onclick="confirmDeleteAccount()">
                        <i class="fas fa-trash-alt mr-1"></i> Saya mengerti, Hapus Akun Saya
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
    {{-- SweetAlert2 Library --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Menampilkan Error Validasi dari Laravel --}}
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                html: '<ul class="text-left pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                confirmButtonColor: '#d33',
            });
        </script>
    @endif

    {{-- Menampilkan Pesan Sukses Update Password --}}
    @if (session('status') === 'password-updated')
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Password Anda telah berhasil diperbarui.',
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif

    <script>
        // Konfirmasi Ganti Password
        function confirmChangePassword() {
            const form = document.getElementById('changePasswordForm');
            const current = form.querySelector('input[name="current_password"]').value;
            const password = form.querySelector('input[name="password"]').value;
            const confirmation = form.querySelector('input[name="password_confirmation"]').value;

            if (!current || !password || !confirmation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Mohon isi semua kolom password (lama, baru, dan konfirmasi).',
                    confirmButtonColor: '#f39c12'
                });
                return;
            }

            Swal.fire({
                title: 'Simpan Password Baru?',
                text: "Pastikan Anda mencatat password baru ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ganti!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Konfirmasi Hapus Akun
        function confirmDeleteAccount() {
            const form = document.getElementById('deleteAccountForm');
            const password = form.querySelector('input[name="password"]').value;

            if (!password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Butuh Konfirmasi',
                    text: 'Demi keamanan, masukkan password Anda untuk melanjutkan penghapusan.',
                });
                return;
            }

            Swal.fire({
                title: 'Anda Yakin?',
                html: "Akun akan dihapus <b>PERMANEN</b>.<br>Data tidak bisa dikembalikan lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Permanen!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endsection