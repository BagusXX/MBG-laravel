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
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteSupplier" 
        formId="formDeleteSupplier" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />
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
</script>
@endif
@endpush
