@extends('adminlte::page')

@section('title', 'Supplier')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Supplier</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddSupplier"
        text="Tambah Supplier"
    />

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
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Region</th>
                        <th>Kontak Person</th>
                        <th>Nomor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $supplier->kode }}</td>
                            <td>{{ $supplier->nama }}</td>
                            <td>{{ $supplier->alamat }}</td>
                            <td>{{ $supplier->region->nama_region ?? '-' }}</td>
                            <td>{{ $supplier->kontak }}</td>
                            <td>{{ $supplier->nomor }}</td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning btnEditSupplier"
                                    data-toggle="modal"
                                    data-target="#modalEditSupplier"
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada supplier</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD SUPPLIER --}}
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
                id="kode_supplier"
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
            <label>Region</label>
            <select name="region_id" class="form-control" required>
                <option value="">-- Pilih Region --</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">
                        {{ $region->nama_region }}
                    </option>
                @endforeach
            </select>
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
        action="#"
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                id=""
                readonly
                required />
        </div>

        <div class="form-group">
            <label>Nama</label>
            <input type="text" id="" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" id="" name="alamat" class="form-control" required />
        </div>
        <div class="form-group mt-2">
            <label>Region</label>
            <select name="region_id" class="form-control" required>
                <option value="">-- Pilih Region --</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">
                        {{ $region->nama_region }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Kontak Person</label>
            <input type="text" id="" name="kontak" class="form-control" required />
        </div>
        
        <div class="form-group">
            <label>Nomor</label>
            <input type="text" id="" name="nomor" class="form-control" required />
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
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kodeInput = document.getElementById('kode_supplier');

        // Generate kode SPR11-SPR99
        const generatedCodes = @json($generatedCodes);

        // Ambil kode terakhir yang ada di database
        const kodeTerakhir = Object.values(generatedCodes).pop();
        kodeInput.value = kodeTerakhir; // set default kode saat tambah supplier
    });
</script>
@endpush
