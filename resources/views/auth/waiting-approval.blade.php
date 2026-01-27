@extends('adminlte::auth.login')

@section('title', 'Menunggu Persetujuan')

@section('auth_header')
<style>
    /* Menghilangkan kesan kaku AdminLTE */
    .login-box, .register-box { width: 400px; } /* Sedikit melebarkan box */
    .card { box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important; border-radius: 20px !important; border: none !important; }
    .card-body { padding: 3rem 2rem !important; }

    /* Custom Icon Circle */
    .status-icon-wrapper {
        width: 80px;
        height: 80px;
        background: #FFF4E6; /* Orange muda lembut */
        color: #FF9F43; /* Orange modern */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        font-size: 2rem;
    }

    /* Custom Status Badge */
    .status-badge {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px dashed #ced4da;
    }
    
    /* Tombol Soft */
    .btn-soft-logout {
        background-color: #ffeaea;
        color: #ff5b5b;
        border: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-soft-logout:hover {
        background-color: #ff5b5b;
        color: white;
    }
</style>
@endsection

@section('auth_body')
<div class="text-center">
    
    {{-- 1. Ikon Modern dalam Lingkaran --}}
    <div class="status-icon-wrapper">
        <i class="fas fa-hourglass-half"></i>
    </div>

    {{-- 2. Typography yang lebih bersih --}}
    <h4 class="font-weight-bolder text-dark mb-2" style="letter-spacing: -0.5px;">Review Process</h4>
    <p class="text-muted mb-4" style="line-height: 1.6;">
        Data pendaftaran Anda berhasil kami terima dan sedang dalam antrean verifikasi.
    </p>

    {{-- 3. Status Box Custom (Bukan Callout) --}}
    <div class="status-badge">
        <div class="d-flex align-items-center justify-content-center text-left">
            <i class="fas fa-info-circle text-muted mr-3" style="font-size: 1.5rem;"></i>
            <div class="text-left" style="line-height: 1.2;">
                <span class="text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">Status Saat Ini</span><br>
                <span class="font-weight-bold text-dark">Menunggu Approval Admin</span>
            </div>
        </div>
    </div>

    {{-- 4. Tombol Logout Custom --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-soft-logout btn-block rounded-pill py-2">
            LOGOUT
        </button>
    </form>

    <!-- {{-- 5. Link Bantuan Minimalis --}}
    <div class="mt-4">
        <a href="#" class="text-muted small" style="text-decoration: none;">
            Butuh bantuan mendesak? <span class="text-primary font-weight-bold">Hubungi Admin</span>
        </a>
    </div> -->

</div>
@endsection

@section('auth_footer')
    {{-- Footer tetap kosong atau copyright --}}
    <div class="text-center mt-3 small text-muted">
        &copy; {{ date('Y') }} Sistem MBG
    </div>
@endsection