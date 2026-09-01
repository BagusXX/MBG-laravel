@extends('adminlte::page')

@section('title', 'User Manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>User Manual</h1>
@endsection

@section('content')

<x-notification-pop-up />

<div class="row mb-3">
    <div class="col-md-6 d-flex align-items-center">
        {{-- Button Upload - Hanya Superadmin --}}
        @role('superadmin')
            <x-button-add idTarget="#modalUploadManual" text="Masukkan File" />
        @endrole
    </div>

    <div class="col-md-6">
        {{-- Dropdown Role - Hanya Superadmin & SuperadminDapur --}}
        @if($isSuperRole)
            <form action="{{ route('user-manual.index') }}" method="GET">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <select name="role" class="form-control" onchange="this.form.submit()">
                        @foreach($allRoles as $role)
                            <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif
    </div>
</div>

{{-- PDF Viewer --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-file-pdf text-danger mr-2"></i>
            Manual for Role: <span class="text-primary">{{ ucfirst($selectedRole) }}</span>
        </h3>
    </div>
    <div class="card-body">
        @if($manual)
            <div class="embed-responsive" style="height: 700px;">
                <iframe 
                    src="{{ Storage::disk('public')->url($manual->file_path) }}" 
                    style="width: 100%; height: 100%; border: none; border-radius: 8px;"
                    type="application/pdf">
                    <p>Browser Anda tidak mendukung tampilan PDF. 
                        <a href="{{ route('user-manual.download', $manual->id) }}">Download PDF</a>
                    </p>
                </iframe>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-file mr-1"></i> {{ $manual->nama_file }}
                        &mdash; Diupload {{ $manual->updated_at->diffForHumans() }}
                    </small>
                </div>
                <div>
                    <a href="{{ route('user-manual.download', $manual->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-download mr-1"></i> Download PDF
                    </a>

                    @role('superadmin')
                        <form action="{{ route('user-manual.destroy', $manual->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus manual ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </button>
                        </form>
                    @endrole
                </div>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-book-open fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">Belum ada manual untuk role <strong>{{ ucfirst($selectedRole) }}</strong>.</p>
                @role('superadmin')
                    <p class="mt-2">Klik tombol <strong>"Masukkan File"</strong> untuk mengupload manual.</p>
                @endrole
            </div>
        @endif
    </div>
</div>

{{-- Modal Upload Manual --}}
@role('superadmin')
<x-modal-form id="modalUploadManual" title="Upload User Manual" action="{{ route('user-manual.store') }}" 
    submitText="Upload" enctype="multipart/form-data">
    <div class="form-group">
        <label>Pilih Role</label>
        <select name="role_name" class="form-control" required>
            @foreach($allRoles as $role)
                <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>
                    {{ ucfirst($role) }}
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">Jika sudah ada manual untuk role ini, file lama akan diganti.</small>
    </div>
    <div class="form-group mt-2">
        <label>File PDF</label>
        <input type="file" name="file_pdf" class="form-control" accept=".pdf" required />
        <small class="form-text text-muted">Maksimal 10MB, format PDF.</small>
    </div>
</x-modal-form>
@endrole

@endsection

@section('js')
<script>
    // File size validation
    document.querySelector('input[name="file_pdf"]')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            if (this.files[0].size > 10 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 10MB.');
                this.value = '';
            }
        }
    });
</script>
@endsection
