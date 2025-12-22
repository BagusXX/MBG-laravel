@extends('adminlte::page')

@section('title', 'Operational')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Operasional</h1>
@endsection

@section('content')
    {{-- BUTTON ADD --}}
    <x-button-add 
        idTarget="#modalAddOperasional"
        text="Tambah Biaya Operasional"
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
                        <th>Nama Biaya</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Jumlah (Rp)</th>
                        <th>Keterangan</th>
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
                    {{-- DATA DUMMY 1 --}}
                    <tr>
                        <td>1</td>
                        <td>OPS-001</td>
                        <td>Tagihan Listrik & Air</td>
                        <td>Utilitas</td>
                        <td>2023-10-01</td>
                        <td class="text-right">Rp 2.500.000</td>
                        <td>Pembayaran PLN & PDAM Pusat</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#modalEditOperasional"
                            >
                                Edit    
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalDeleteOperasional">
                                Hapus
                            </button>
                        </td>
                    </tr>

                    {{-- DATA DUMMY 2 --}}
                    <tr>
                        <td>2</td>
                        <td>OPS-002</td>
                        <td>Internet Bulanan</td>
                        <td>Komunikasi</td>
                        <td>2023-10-05</td>
                        <td class="text-right">Rp 550.000</td>
                        <td>Wifi IndiHome 100Mbps</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#modalEditOperasional"
                            >
                                Edit    
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalDeleteOperasional">
                                Hapus
                            </button>
                        </td>
                    </tr>

                     {{-- DATA DUMMY 3 --}}
                     <tr>
                        <td>3</td>
                        <td>OPS-003</td>
                        <td>Service AC Kantor</td>
                        <td>Maintenance</td>
                        <td>2023-10-10</td>
                        <td class="text-right">Rp 750.000</td>
                        <td>Cuci AC 5 Unit</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#modalEditOperasional"
                            >
                                Edit    
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalDeleteOperasional">
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ADD OPERATIONAL --}}
    <x-modal-form
    id="modalAddOperasional"
    title="Tambah Biaya Operasional"
    {{-- action="{{ route('master.region.store') }}" --}}
    action="#"
    submitText="Simpan"
>
        <div class="form-group">
            <label>Kode Transaksi</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                id="kode_operasional"
                readonly 
                required 
            />
        </div>
        
        <div class="form-group mt-2">
            <label for="nama_operasional">Nama Biaya</label>
            <input id="nama_operasional" type="text" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Kategori</label>
            <select name="kategori" class="form-control">
                <option value="Utilitas">Utilitas (Listrik/Air/Internet)</option>
                <option value="Maintenance">Maintenance/Perbaikan</option>
                <option value="ATK">Perlengkapan Kantor (ATK)</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Jumlah Biaya (Rp)</label>
            <input type="number" name="jumlah" class="form-control" placeholder="0" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Detail pengeluaran..."></textarea>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT OPERATIONAL --}}
    <x-modal-form
        id="modalEditOperasional"
        title="Edit Operasional"
        action="#"
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Kode Transaksi</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                id=""
                readonly
                required />
        </div>

        <div class="form-group">
            <label>Nama Biaya</label>
            <input type="text" id="" name="nama" class="form-control" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Kategori</label>
            <select name="kategori" class="form-control">
                <option value="Utilitas" selected>Utilitas (Listrik/Air/Internet)</option>
                <option value="Maintenance">Maintenance/Perbaikan</option>
                <option value="ATK">Perlengkapan Kantor (ATK)</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>

        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="2023-10-01" required />
        </div>
        
        <div class="form-group mt-2">
            <label>Jumlah Biaya (Rp)</label>
            <input type="number" name="jumlah" class="form-control" value="2500000" required />
        </div>
        
       <div class="form-group mt-2">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="2">Pembayaran PLN & PDAM Pusat</textarea>
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteOperasional" 
        formId="formDeleteOperasional" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />  
@endsection

{{-- @push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kodeInput = document.getElementById('kode_operasional');

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
        const kodeInput = document.getElementById('kode_operasional'); 

        if(kodeInput) {
            // Kita set manual prefix OPS (Operasional)
            kodeInput.value = "OPS-001"; 
            console.log('Kode otomatis di-set ke OPS-001');
        } else {
            console.warn('Input kode tidak ditemukan! Cek ID input HTML Anda.');
        }
    });
</script>
@endpush