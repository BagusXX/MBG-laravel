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

    {{-- UPDATE PASSWORD --}}
    <div class="card mb-3">
        <div class="card-header">
            <strong>Ganti Password</strong>
        </div>
        <div class="card-body">
            <form id="changePasswordForm"
                  method="POST"
                  action="{{ route('profile.password.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-group mb-2">
                    <label>Password Saat Ini</label>
                    <input type="password"
                           name="current_password"
                           class="form-control">
                </div>

                <div class="form-group mb-2">
                    <label>Password Baru</label>
                    <input type="password"
                           name="password"
                           class="form-control">
                </div>

                <div class="form-group mb-2">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password"
                           name="password_confirmation"
                           class="form-control">
                </div>

                <button type="button"
                        class="btn btn-warning mt-2"
                        onclick="confirmChangePassword()">
                    Ganti Password
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
            <form id="deleteAccountForm"
                  method="POST"
                  action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="form-group mb-2">
                    <label>Konfirmasi Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Password saat ini">
                </div>

                <button type="button"
                        class="btn btn-danger"
                        onclick="confirmDeleteAccount()">
                    Hapus Akun
                </button>
            </form>
        </div>
    </div>

@endsection

@section('js')
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- VALIDATION ERROR FROM BACKEND --}}
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `
                    <ul style="text-align:left;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `
            });
        </script>
    @endif

    {{-- SUCCESS PASSWORD UPDATED --}}
    @if (session('status') === 'password-updated')
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Password berhasil diperbarui.'
            });
        </script>
    @endif

    <script>
        function confirmChangePassword() {
            const form = document.getElementById('changePasswordForm');
            const current = form.querySelector('input[name="current_password"]').value;
            const password = form.querySelector('input[name="password"]').value;
            const confirmation = form.querySelector('input[name="password_confirmation"]').value;

            if (!current || !password || !confirmation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form belum lengkap',
                    text: 'Silakan lengkapi semua field password terlebih dahulu.'
                });
                return;
            }

            Swal.fire({
                title: 'Ganti Password?',
                text: 'Password akun Anda akan diganti.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Ganti Password',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmDeleteAccount() {
            const form = document.getElementById('deleteAccountForm');
            const password = form.querySelector('input[name="password"]').value;

            if (!password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Password wajib diisi',
                    text: 'Masukkan password terlebih dahulu untuk menghapus akun.'
                });
                return;
            }

            Swal.fire({
                title: 'Hapus Akun?',
                html: '<strong class="text-danger">Akun akan dihapus permanen dan tidak dapat dikembalikan.</strong>',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Akun',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endsection
