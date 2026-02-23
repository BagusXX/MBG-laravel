@extends('adminlte::page')

@section('title', 'Supplier')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Supplier Dapur MBG</h1>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        @can('master.supplier.create')
        <x-button-add idTarget="#modalAddSupplier" text="Tambah Supplier" />
        @endcan
    </div>
    <div class="col-md-6">
            <form action="{{ route('master.supplier.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari nama supplier atau kode..." 
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('master.supplier.index') }}" class="btn btn-danger">
                                <i class="fa fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
</div>

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 10%;">Kode</th>
                        <th style="width: 15%;">Supplier</th>
                        <th style="width: 25%;">Alamat</th>
                        <th style="width: 15%;">Dapur</th>
                        <th style="width: 10%;">Kontak Person</th>
                        <th style="width: 10%;">Nomor</th>
                        
                        <th style="width: 80px; text-align: center;">Scan TTD</th>
                        <th style="width: 80px; text-align: center;">Logo Supplier</th>
                        @canany(['master.supplier.update', 'master.supplier.delete'])
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr>
                            <td>{{ $suppliers->firstItem() + $index }}</td>
                            <td>{{ $supplier->kode }}</td>
                            <td>{{ $supplier->nama }}</td>
                            <td class="text-wrap text-break">{{ $supplier->alamat }}</td>
                            <td>
                                @foreach ($supplier->kitchens as $kitchen)
                                    <span class="badge badge-info">{{ $kitchen->nama }}</span>
                                @endforeach
                            </td>
                            <td>{{ $supplier->kontak }}</td>
                            <td>{{ $supplier->nomor }}</td>
                            <td class="text-center">
                                @if ($supplier->ttd)
                                    <img src="{{ asset('galeri/' . $supplier->ttd) }}" class="img-thumbnail supplier-image"
                                        style="width: 60px; height: 60px; object-fit: contain; cursor: pointer;"
                                        data-toggle="modal" data-target="#modalPreviewImage"
                                        data-src="{{ asset('galeri/' . $supplier->ttd) }}" data-title="Scan TTD">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($supplier->gambar)
                                    <img src="{{ asset('galeri/' . $supplier->gambar) }}" class="img-thumbnail supplier-image"
                                        style="width: 60px; height: 60px; object-fit: contain; cursor: pointer;"
                                        data-toggle="modal" data-target="#modalPreviewImage"
                                        data-src="{{ asset('galeri/' . $supplier->gambar) }}" data-title="Logo Supplier">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            @canany(['master.supplier.update', 'master.supplier.delete'])
                                <td>
                                    @can('master.supplier.update')
                                        <button type="button" class="btn btn-sm btn-warning btnEditSupplier" data-toggle="modal"
                                            data-target="#modalEditSupplier" data-id="{{ $supplier->id }}"
                                            data-kode="{{ $supplier->kode }}" data-nama="{{ $supplier->nama }}"
                                            data-alamat="{{ $supplier->alamat }}"
                                            data-kitchens="{{ json_encode($supplier->kitchens->pluck('kode')) }}"
                                            data-kontak="{{ $supplier->kontak }}" data-nomor="{{ $supplier->nomor }}"
                                            data-ttd="{{ $supplier->ttd }}" data-gambar="{{ $supplier->gambar }}">
                                            Edit
                                        </button>
                                    @endcan

                                    @can('master.supplier.delete')
                                        <x-button-delete idTarget="#modalDeleteSupplier" formId="formDeleteSupplier"
                                            action="{{ route('master.supplier.destroy', $supplier->id) }}" text="Hapus" />
                                    @endcan
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Belum ada supplier</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                {{ $suppliers->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD --}}
    <x-modal-form id="modalAddSupplier" title="Tambah Supplier" action="{{ route('master.supplier.store') }}"
        submitText="Simpan" enctype="multipart/form-data">
        <div class="form-group">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" value="{{ $kodeBaru }}" readonly required />
        </div>
        <div class="form-group mt-2">
            <label for="nama_supplier">Nama</label>
            <input id="nama_supplier" type="text" name="nama" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label for="alamat_supplier">Alamat</label>
            <input id="alamat_supplier" type="text" name="alamat" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label>Pilih Dapur (Kitchen)</label>
            <div class="row" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                @foreach ($kitchens as $kitchen)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kitchens[]" value="{{ $kitchen->kode }}" id="add_kitchen_{{ $kitchen->kode }}">
                            <label class="form-check-label" for="add_kitchen_{{ $kitchen->kode }}">{{ $kitchen->nama }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="form-group mt-2">
            <label>Kontak Person</label>
            <input type="text" name="kontak" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label>Nomor</label>
            <input type="text" name="nomor" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label>Logo Supplier</label>
            <input type="file" name="gambar" class="form-control" accept="image/*" />
        </div>
        <div class="form-group mt-2">
            <label>Scan TTD</label>
            <input type="file" name="ttd" class="form-control" accept="image/*" />
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form id="modalEditSupplier" title="Edit Supplier" action="" submitText="Update" enctype="multipart/form-data">
        <div class="form-group">
            <label>Kode</label>
            <input type="text" name="kode" id="edit_kode" class="form-control" readonly required />
        </div>
        <div class="form-group">
            <label>Nama</label>
            <input type="text" id="edit_nama" name="nama" class="form-control" required />
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" id="edit_alamat" name="alamat" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label>Pilih Dapur (Kitchen)</label>
            <div class="row" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                @foreach ($kitchens as $kitchen)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input edit-kitchen-checkbox" type="checkbox" name="kitchens[]" value="{{ $kitchen->kode }}" id="edit_kitchen_{{ $kitchen->kode }}">
                            <label class="form-check-label" for="edit_kitchen_{{ $kitchen->kode }}">{{ $kitchen->nama }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label>Kontak Person</label>
            <input type="text" id="edit_kontak" name="kontak" class="form-control" required />
        </div>
        <div class="form-group">
            <label>Nomor</label>
            <input type="text" id="edit_nomor" name="nomor" class="form-control" required />
        </div>
        <div class="form-group">
            <label>Logo Supplier</label>
            <div class="mb-2">
                <img id="edit_preview_gambar" src="" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: contain; display: none;">
            </div>
            <input type="file" name="gambar" class="form-control" accept="image/*" />
        </div>
        <div class="form-group">
            <label>Scan TTD</label>
            <div class="mb-2">
                <img id="edit_preview_ttd" src="" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: contain; display: none;">
            </div>
            <input type="file" name="ttd" class="form-control" accept="image/*" />
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeleteSupplier" formId="formDeleteSupplier" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />

    {{-- MODAL PREVIEW IMAGE (SATU UNTUK SEMUA) --}}
    <div class="modal fade" id="modalPreviewImage" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPreviewTitle">Preview</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImageModal" src="" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ERROR FILE SIZE --}}
    <div class="modal fade" id="modalErrorFile" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger font-weight-bold">Peringatan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center py-4">
                    <h5 class="text-danger font-weight-bold mb-3">Ukuran File Terlalu Besar!</h5>
                    <p class="mb-0">Maksimal ukuran foto adalah <strong>2MB</strong>.</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
<script>
    // 1. Logika Tombol Edit
    document.querySelectorAll('.btnEditSupplier').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            document.getElementById('edit_kode').value = this.dataset.kode;
            document.getElementById('edit_nama').value = this.dataset.nama;
            document.getElementById('edit_alamat').value = this.dataset.alamat;
            document.getElementById('edit_kontak').value = this.dataset.kontak;
            document.getElementById('edit_nomor').value = this.dataset.nomor;

            // Preview Logo (Existing)
            const gambar = this.dataset.gambar;
            const previewGbr = document.getElementById('edit_preview_gambar');
            if (gambar) { previewGbr.src = `/galeri/${gambar}`; previewGbr.style.display = 'block'; } 
            else { previewGbr.style.display = 'none'; }

            // Preview TTD (Existing)
            const ttd = this.dataset.ttd;
            const previewTtd = document.getElementById('edit_preview_ttd');
            if (ttd) { previewTtd.src = `/galeri/${ttd}`; previewTtd.style.display = 'block'; } 
            else { previewTtd.style.display = 'none'; }

            // Checkbox Kitchens
            document.querySelectorAll('.edit-kitchen-checkbox').forEach(box => box.checked = false);
            const connectedKitchens = JSON.parse(this.dataset.kitchens || '[]');
            connectedKitchens.forEach(kode => {
                const cb = document.querySelector(`.edit-kitchen-checkbox[value="${kode}"]`);
                if (cb) cb.checked = true;
            });

            document.querySelector('#modalEditSupplier form').action = `/dashboard/master/supplier/update/${id}`;
        });
    });

    // 2. Logika Preview Gambar Tabel
    document.querySelectorAll('.supplier-image').forEach(img => {
        img.addEventListener('click', function () {
            document.getElementById('previewImageModal').src = this.dataset.src;
            document.getElementById('modalPreviewTitle').innerText = this.dataset.title;
        });
    });

    // 3. Validasi Ukuran File & Live Preview
    function setupFileValidation(inputName, previewId) {
        document.querySelectorAll(`input[name="${inputName}"]`).forEach(input => {
            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (file.size > 2 * 1024 * 1024) {
                        $('#modalErrorFile').modal('show');
                        this.value = '';
                        return;
                    }
                    // Jika di modal edit, tampilkan preview baru
                    const preview = document.getElementById(previewId);
                    if (this.closest('#modalEditSupplier') && preview) {
                        const reader = new FileReader();
                        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
                        reader.readAsDataURL(file);
                    }
                }
            });
        });
    }

    setupFileValidation('gambar', 'edit_preview_gambar');
    setupFileValidation('ttd', 'edit_preview_ttd');
</script>
@endpush