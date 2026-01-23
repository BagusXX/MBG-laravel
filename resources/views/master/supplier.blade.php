@extends('adminlte::page')

@section('title', 'Supplier')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Supplier Dapur MBG</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    {{-- <x-button-add 
        idTarget="#modalAddSupplier"
        text="Tambah Supplier"
    /> --}}
    @if($canManage)
        <x-button-add 
            idTarget="#modalAddSupplier"
            text="Tambah Supplier"
        />
    @endif

    {{-- ALERT SUCCESS --}}
    {{-- @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif --}}
    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Supplier</th>
                        <th>Alamat</th>
                        <th>Dapur</th>
                        <th>Kontak Person</th>
                        <th>Nomor</th>
                        <th>Foto</th>
                        @if($canManage)
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr>
                            <td>{{ $suppliers->firstItem() + $index }}</td>
                            <td>{{ $supplier->kode }}</td>
                            <td>{{ $supplier->nama }}</td>
                            <td>{{ $supplier->alamat }}</td>
                            <td>
                                @foreach($supplier->kitchens as $kitchen)
                                    <span class="badge badge-info">{{ $kitchen->nama }}</span>
                                @endforeach
                            </td>
                            <td>{{ $supplier->kontak }}</td>
                            <td>{{ $supplier->nomor }}</td>
                            <td class="text-center">
                                @if($supplier->gambar)
                                    <img 
                                        src="{{ asset('storage/' . $supplier->gambar) }}" 
                                        width="60"
                                        class="img-thumbnail supplier-image"
                                        style="cursor:pointer"
                                        data-toggle="modal"
                                        data-target="#modalPreviewImage"
                                        data-src="{{ asset('storage/' . $supplier->gambar) }}"
                                    >
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            @if($canManage)
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning btnEditSupplier"
                                    data-toggle="modal"
                                    data-target="#modalEditSupplier"
                                    data-id="{{ $supplier->id }}"
                                    data-kode="{{ $supplier->kode }}"
                                    data-nama="{{ $supplier->nama }}"
                                    data-alamat="{{ $supplier->alamat }}"
                                    data-kitchens="{{ json_encode($supplier->kitchens->pluck('kode')) }}"
                                    data-kontak="{{ $supplier->kontak }}"
                                    data-nomor="{{ $supplier->nomor }}"
                                    data-gambar="{{ $supplier->gambar }}"
                                >
                                    Edit    
                                </button>
                                {{-- Tombol Hapus --}}
                                <x-button-delete 
                                    idTarget="#modalDeleteSupplier" 
                                    formId="formDeleteSupplier"
                                    action="{{ route('master.supplier.destroy', $supplier->id) }}"
                                    text="Hapus" 
                                />

                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? '8' : '7' }}" class="text-center">Belum ada supplier</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $suppliers->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD SUPPLIER --}}
    @if($canManage)
    <x-modal-form
    id="modalAddSupplier"
    title="Tambah Supplier"
    action="{{ route('master.supplier.store') }}"
    submitText="Simpan"
>
        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                value="{{ $kodeBaru }}"
                readonly 
                required 
            />
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
                @foreach($kitchens as $kitchen)
                    <div class="col-md-6">
                        <div class="form-check">
                            {{-- Value menggunakan KODE sesuai validasi controller --}}
                            <input class="form-check-input" type="checkbox" name="kitchens[]" value="{{ $kitchen->kode }}" id="add_kitchen_{{ $kitchen->kode }}">
                            <label class="form-check-label" for="add_kitchen_{{ $kitchen->kode }}">
                                {{ $kitchen->nama }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        
        <div class="form-group mt-2">
            <label for="kontak_supplier">Kontak Person</label>
            <input id="kontak_supplier" type="text" name="kontak" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="nomor_supplier">Nomor</label>
            <input id="nomor_supplier" type="text" name="nomor" class="form-control" required />
        </div>

        <div class="form-group mt-2">
            <label>Foto Supplier</label>
            <input 
                type="file" 
                name="gambar" 
                class="form-control"
                accept="image/*"
            />
            <small class="text-muted">
                Format JPG / PNG, maksimal 2MB
            </small>
        </div>

    </x-modal-form>

    {{-- MODAL EDIT SUPPLIER --}}
    <x-modal-form
        id="modalEditSupplier"
        title="Edit Supplier"
        action=""
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode" 
                id="edit_kode"
                class="form-control" 
                readonly
                required />
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
                @foreach($kitchens as $kitchen)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input edit-kitchen-checkbox" type="checkbox" name="kitchens[]" value="{{ $kitchen->kode }}" id="edit_kitchen_{{ $kitchen->kode }}">
                            <label class="form-check-label" for="edit_kitchen_{{ $kitchen->kode }}">
                                {{ $kitchen->nama }}
                            </label>
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
            <label>Foto Supplier</label>

            {{-- preview foto lama --}}
            <div class="mb-2">
                <img 
                    id="edit_preview_gambar"
                    src=""
                    alt="Preview"
                    class="img-thumbnail"
                    style="max-height: 120px; display: none;"
                >
            </div>

            <input 
                type="file" 
                name="gambar" 
                class="form-control"
                accept="image/*"
            />

            <small class="text-muted">
                Kosongkan jika tidak ingin mengganti foto
            </small>
        </div>

    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteSupplier" 
        formId="formDeleteSupplier" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />

    <div class="modal fade" id="modalPreviewImage" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img 
                        id="previewImageModal"
                        src=""
                        class="img-fluid rounded"
                    >
                </div>
            </div>
        </div>
    </div>

    @endif

@endsection

@push('js')
@if($canManage)
<script>
     document.querySelectorAll('.btnEditSupplier').forEach(button => {
        button.addEventListener('click', function () {

            const id = this.dataset.id;

            document.getElementById('edit_kode').value = this.dataset.kode;
            document.getElementById('edit_nama').value = this.dataset.nama;
            document.getElementById('edit_alamat').value = this.dataset.alamat;
            document.getElementById('edit_kontak').value = this.dataset.kontak;
            document.getElementById('edit_nomor').value = this.dataset.nomor;

            const gambar = this.dataset.gambar;
            const preview = document.getElementById('edit_preview_gambar');

            if (gambar) {
                preview.src = `/storage/${gambar}`;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }

            // 1. Reset semua checkbox di modal edit menjadi tidak tercentang
            document.querySelectorAll('.edit-kitchen-checkbox').forEach(box => box.checked = false);

            // 2. Ambil data kitchens dari atribut tombol (format JSON array)
            // Contoh data: ["K001", "K002"]
            const connectedKitchens = JSON.parse(this.dataset.kitchens || '[]');

            // 3. Loop kitchen yang terhubung, lalu centang checkbox yang sesuai valuenya
            connectedKitchens.forEach(kodeKitchen => {
                // Cari checkbox dengan value = kodeKitchen
                const checkbox = document.querySelector(`.edit-kitchen-checkbox[value="${kodeKitchen}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            // set action form
            const form = document.querySelector('#modalEditSupplier form');
            form.action = `/dashboard/master/supplier/${id}`;
        });
    });
    document.querySelectorAll('.supplier-image').forEach(img => {
    img.addEventListener('click', function () {
        document.getElementById('previewImageModal').src = this.dataset.src;
    });
});

</script>
@endif
@endpush
