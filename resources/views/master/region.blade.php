@extends('adminlte::page')

@section('title', 'Region')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Region</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddRegion"
        text="Tambah Region"
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
                        <th>Penanggung Jawab</th>
                        {{-- <th>Region</th>
                        <th>Kontak Person</th>
                        <th>Nomor</th> --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse($regions as $index => $region)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $region->kode }}</td>
                            <td>{{ $region->nama }}</td>
                            <td>{{ $region->alamat }}</td>
                            <td>{{ $region->region }}</td>
                            <td>{{ $region->kontak_person }}</td>
                            <td>{{ $region->nomor }}</td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning btnEditRegion"
                                    data-toggle="modal"
                                    data-target="#modalEditRegion"
                                >
                                    Edit    
                                </button> --}}
                                {{-- Tombol Hapus --}}
                                {{-- <x-button-delete 
                                    idTarget="#modalDeleteRegion" 
                                    formId="formDeleteRegion"
                                    action="{{ route('master.region.destroy', $region->id) }}"
                                    text="Hapus" 
                                />

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada Region</td>
                        </tr>
                    @endforelse --}}
                    {{-- DATA DUMMY 1 --}}
                    <tr>
                        <td>1</td>
                        <td>RGN01</td>
                        <td>Jawa Timur</td>
                        <td>Hasann</td>
                        {{-- <td>East Java</td>
                        <td>Budi Santoso</td>
                        <td>08123456789</td> --}}
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#modalEditRegion"
                            >
                                Edit    
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalDeleteRegion">
                                Hapus
                            </button>
                        </td>
                    </tr>

                    {{-- DATA DUMMY 2 --}}
                    <tr>
                        <td>2</td>
                        <td>RGN02</td>
                        <td>DKI Jakarta</td>
                        <td>Hasann 2</td>
                        {{-- <td>Jakarta Raya</td>
                        <td>Siti Aminah</td>
                        <td>08987654321</td> --}}
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#modalEditRegion"
                            >
                                Edit    
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalDeleteRegion">
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD REGION --}}
    <x-modal-form
    id="modalAddRegion"
    title="Tambah Region"
    {{-- action="{{ route('master.region.store') }}" --}}
    action="#"
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
            <label for="nama_region">Nama</label>
            <input id="nama_region" type="text" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="penanggung_jawab">Penanggung Jawab</label>
            <input id="penanggung_jawab" type="text" name="penanggungjawab" class="form-control" required />
        </div>
        {{-- <div class="form-group mt-2">
            <label for="alamat_region">Region</label>
            <input id="alamat_region" type="text" name="region" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="kontak_region">Kontak Person</label>
            <input id="kontak_region" type="text" name="kontak_person" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label for="nomor_region">Nomor</label>
            <input id="nomor_region" type="text" name="nomor" class="form-control" required />
        </div> --}}
    </x-modal-form>

    {{-- MODAL EDIT REGION --}}
    <x-modal-form
        id="modalEditRegion"
        title="Edit Region"
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
            <label>Penanggung Jawab</label>
            <input type="text" id="" name="penanggungjawab" class="form-control" required />
        </div>
        {{-- <div class="form-group">
            <label>Region</label>
            <input type="text" id="" name="region" class="form-control" required />
        </div>
        
        <div class="form-group">
            <label>Kontak Person</label>
            <input type="text" id="" name="kontak_person" class="form-control" required />
        </div>
        
        <div class="form-group">
            <label>Nomor</label>
            <input type="text" id="" name="nomor" class="form-control" required />
        </div> --}}
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteRegion" 
        formId="formDeleteRegion" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />  
@endsection

{{-- @push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kodeInput = document.getElementById('kode_region');

        // Generate kode SPR11-SPR99
        const generatedCodes = @json($generatedCodes);

        // Ambil kode terakhir yang ada di database
        const kodeTerakhir = Object.values(generatedCodes).pop();
        kodeInput.value = kodeTerakhir; // set default kode saat tambah supplier
    });
</script>
@endpush --}}

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // PERHATIKAN: Pastikan ID ini sama dengan ID di input Modal Tambah Anda
        // Di kode sebelumnya ID input Anda adalah 'kode_supplier', jadi kita pakai itu.
        const kodeInput = document.getElementById('kode_supplier'); 

        if(kodeInput) {
            // Kita set manual saja untuk tampilan statis
            kodeInput.value = "REG-001"; 
            console.log('Kode otomatis di-set ke REG-001');
        } else {
            console.warn('Input kode tidak ditemukan! Cek ID input HTML Anda.');
        }
    });
</script>
@endpush
