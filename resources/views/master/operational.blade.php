@extends('adminlte::page')

@section('title', 'Operational')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Operasional Dapur</h1>
@endsection

@section('content')

    {{-- BUTTON ADD --}}
    
        <div class="row mb-3">
            <div class="col-md-6">

                @can('master.operational.create')
                    <x-button-add idTarget="#modalAddOperasional" text="Tambah Biaya Operasional" />
                @endcan

            </div>
            <div class="col-md-6">
                <form action="{{ route('master.operational.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari barang operasional atau kode..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                            @if (request('search'))
                                <a href="{{ route('master.operational.index') }}" class="btn btn-danger">
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
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Dapur</th>
                        <th>Nama Biaya</th>
                        {{-- <th>Harga Satuan</th> --}}
                        <th>Tanggal</th>

                        @canany(['master.operational.update', 'master.operational.delete'])
                            <th>Aksi</th>
                        @endcanany

                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->kitchen->nama ?? '-' }}</td>
                            <td>{{ $item->nama }}</td>
                            {{-- <td>Rp {{ number_format($item->harga_default, 2, ',', '.') }}</td> --}}
                            <td>{{ $item->updated_at }}</td>

                            @canany(['master.operational.update', 'master.operational.delete'])
                                <td>
                                    @can('master.operational.update')
                                        <button type="button" class="btn btn-sm btn-warning btnEditOperational"
                                            data-id="{{ $item->id }}" data-kode="{{ $item->kode }}"
                                            data-kitchen="{{ $item->kitchen_kode }}" data-nama="{{ $item->nama }}"
                                            data-harga="{{ $item->harga_default }}"
                                            data-tanggal="{{ $item->created_at->format('Y-m-d') }}">
                                            Edit
                                        </button>
                                    @endcan

                                    {{-- Tombol Hapus --}}
                                    @can('master.operational.delete')
                                        <x-button-delete idTarget="#modalDeleteOperational" formId="formDeleteOperational"
                                            action="{{ route('master.operational.destroy', $item->id) }}" text="Hapus" />
                                    @endcan
                                </td>
                            @endcanany

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada Biaya Operational</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                {{-- Per Page (KIRI) --}}
                <form method="GET" class="mb-0">
                    <input type="hidden" name="search" value="{{ request('search') }}">
            
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            Tampilkan
                        </span>
            
                        <select
                            name="per_page"
                            class="form-select form-select-sm"
                            onchange="this.form.submit()"
                            style="max-width: 90px"
                        >
                            @foreach([10,25,50,100] as $size)
                                <option
                                    value="{{ $size }}"
                                    {{ request('per_page',10) == $size ? 'selected' : '' }}
                                >
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
            
                        <span class="input-group-text">
                            data
                        </span>
                    </div>
                </form>
                <div class="mt-3 d-flex justify-content-end">
                    {{ $items->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ADD OPERATIONAL --}}
    <x-modal-form id="modalAddOperasional" title="Tambah Biaya Operasional"
        action="{{ route('master.operational.store') }}" submitText="Simpan">
        <div class="form-group">
            <label>Kode Transaksi</label>
            <input type="text" name="kode" class="form-control" id="kode_operasional" value="{{ $nextKode }}"
                readonly required />
        </div>

        <div class="form-group mt-2">
            <label>Dapur</label>
            <select name="kitchen_kode" class="form-control" required>
                <option value="">-- Pilih Dapur --</option>
                @foreach ($kitchens as $kode => $nama)
                    <option value="{{ $kode }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group mt-2">
            <label for="nama_operasional">Nama Biaya</label>
            <input id="nama_operasional" type="text" name="nama" class="form-control" required />
        </div>

        {{-- <div class="form-group mt-2">
            <label>Harga Satuan</label>
            <input 
                type="number" 
                name="harga_default" 
                class="form-control"
                min="0"
                step="0.01"
                required
            />
        </div> --}}

        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required />
        </div>

    </x-modal-form>

    {{-- MODAL EDIT OPERATIONAL --}}
    <x-modal-form id="modalEditOperasional" title="Edit Operasional" action="" submitText="Update">
        @method('PUT')
        <input type="hidden" name="id" id="edit_id">

        <div class="form-group">
            <label>Kode Transaksi</label>
            <input type="text" name="kode" id="edit_kode" class="form-control" readonly />
        </div>

        <div class="form-group mt-2">
            <label>Dapur</label>
            <select name="kitchen_kode" id="edit_kitchen_kode" class="form-control" required>
                @foreach ($kitchens as $kode => $nama)
                    <option value="{{ $kode }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-2">
            <label>Nama Biaya</label>
            <input type="text" name="nama" id="edit_nama" class="form-control" required />
        </div>

        {{-- <div class="form-group mt-2">
            <label>Harga Satuan</label>
            <input 
                type="number" 
                name="harga_default"
                id="edit_harga_default"
                class="form-control"
                min="0"
                step="0.01"
                required
            />
        </div> --}}


        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required />
        </div>

    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeleteOperational" formId="formDeleteOperational" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />
@endsection

@section('js')
    <script>
        // Fungsi Capitalize
        function capitalizeWords(text) {
            return text
                .toLowerCase()
                .replace(/\b\w/g, char => char.toUpperCase());
        }

        document.addEventListener('DOMContentLoaded', function() {

            // 1. LOGIC CAPITALIZE INPUT (Tetap)
            const inputs = document.querySelectorAll('input[name="nama"]');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = capitalizeWords(this.value);
                });
            });

            // 2. LOGIC TOMBOL EDIT (PERBAIKAN)
            const editButtons = document.querySelectorAll('.btnEditOperational');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Ambil data dari tombol
                    const id = this.dataset.id;
                    const kode = this.dataset.kode;
                    const kitchen = this.dataset.kitchen;
                    const nama = this.dataset.nama;
                    const harga = this.dataset.harga;
                    const tanggal = this.dataset.tanggal;

                    // Set Action Form
                    // Pastikan selector ID Modal sesuai dengan HTML (#modalEditOperasional)
                    const form = document.querySelector('#modalEditOperasional form');
                    if (form) {
                        form.action = `/dashboard/master/operational/${id}`;
                    }

                    // Isi Input Value (Dengan Pengecekan agar tidak Error)
                    if (document.getElementById('edit_id'))
                        document.getElementById('edit_id').value = id;

                    if (document.getElementById('edit_kode'))
                        document.getElementById('edit_kode').value = kode;

                    if (document.getElementById('edit_kitchen_kode'))
                        document.getElementById('edit_kitchen_kode').value = kitchen;

                    if (document.getElementById('edit_nama'))
                        document.getElementById('edit_nama').value = nama;

                    if (document.getElementById('edit_tanggal'))
                        document.getElementById('edit_tanggal').value = tanggal;

                    // KHUSUS HARGA: Cek dulu elemennya ada atau tidak (karena di HTML dikomentari)
                    if (document.getElementById('edit_harga_default')) {
                        document.getElementById('edit_harga_default').value = harga;
                    }

                    // Tampilkan Modal
                    $('#modalEditOperasional').modal('show');
                });
            });
        });
    </script>
@endsection
