@extends('adminlte::page')

@section('title', 'Bahan Baku')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Data Bahan Baku Masakan</h1>
@endsection

@section('content')


    <div class="row mb-3">
        <div class="col-md-6">
            @if($canManage)
                <x-button-add idTarget="#modalAddMaterials" text="Tambah Bahan Baku" />  
            @endif
        </div>
        <div class="col-md-6">
            <form action="{{ route('dashboard.master.bahan-baku.index') }}" method="GET">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari nama bahan atau kode..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('dashboard.master.bahan-baku.index') }}" class="btn btn-danger">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
        </div>
    </div>
    {{-- @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif --}}
    <x-notification-pop-up />

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama Bahan</th>
                        <th>Satuan</th>
                        {{-- <th>Harga Satuan</th> --}}
                        <th>Dapur</th>
                        @if($canManage)
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->unit->satuan ?? '-' }}</td>
                            {{-- <td>Rp {{ number_format($item->harga, 2, ',', '.') }}</td> --}}
                            <td>{{ $item->kitchen->nama ?? '-' }}</td>
                            @if($canManage)
                            <td>
                                {{-- BUTTON EDIT --}}
                                <button type="button" class="btn btn-warning btn-sm btnEditMaterials"
                                    data-id="{{ $item->id }}" data-kode="{{ $item->kode }}"
                                    data-nama="{{ $item->nama }}" data-satuan-id="{{ $item->satuan_id }}"
                                    data-harga="{{ $item->harga }}" data-dapur-id="{{ $item->kitchen_id }}"
                                    data-old-kode="{{ $item->kode }}" data-old-dapur-id="{{ $item->kitchen_id }}"
                                    data-toggle="modal" data-target="#modalEditMaterials">
                                    Edit
                                </button>

                                {{-- <x-button-delete idTarget="#modalDeleteMaterials" formId="formDeleteMaterials"
                                    action="{{ route('dashboard.master.bahan-baku.destroy', $item->id) }}"
                                    text="Hapus" /> --}}
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data bahan baku</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $items->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <form id="reloadForm" method="GET" action="">
        <input type="hidden" name="kitchen_id" id="reloadKitchenId">
    </form>

    {{-- MODAL ADD MATERIALS --}}
    <x-modal-form id="modalAddMaterials" title="Tambah Bahan Baku"
        action="{{ route('dashboard.master.bahan-baku.index') }}" submitText="Simpan">
        <div class="form-group">
            <label>Kode</label>
            <input id="kode_bahan" type="text" class="form-control" name="kode" readonly required>
        </div>
        <div class="form-group">
            <label>Nama Bahan</label>
            <input type="text" placeholder="Bawang Merah" class="form-control" name="nama" required>
        </div>
        <div class="form-group">
            <label>Satuan</label>
            <select name="satuan_id" class="form-control" required>
                <option value="">-- Pilih Satuan --</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">
                        {{ $unit->satuan }}
                    </option>
                @endforeach
            </select>

        </div>

        {{-- <div class="form-group">
            <label>Harga</label>
            <input type="number" step="0.01" placeholder="10000" class="form-control" name="harga" required>
        </div> --}}

        <div class="form-group mt-2">
            <label>Dapur</label>
            <select name="kitchen_id" class="form-control" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach ($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }} ({{ $kitchen->kode }})</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form id="modalEditMaterials" title="Edit Bahan Baku" action="" submitText="Update">
        @method('PUT')

        <div class="form-group">
            <label>Kode</label>
            <input id="editKodeBahan" type="text" class="form-control" name="kode" readonly required>
        </div>

        <div class="form-group">
            <label>Nama Bahan</label>
            <input id="editBahan" type="text" placeholder="Bawang Merah" class="form-control" name="nama" required>
        </div>

        <div class="form-group">
            <label>Satuan</label>
            <select id="editSatuan" class="form-control" name="satuan_id" required>
                <option value="" disabled selected>Pilih Satuan</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->satuan }}</option>
                @endforeach
            </select>
        </div>

        {{-- <div class="form-group">
            <label>Harga</label>
            <input id="editHarga" type="number" step="0.01" class="form-control" name="harga" required>
        </div> --}}

        <div class="form-group mt-2">
            <label>Dapur</label>
            <select id="editDapur" name="kitchen_id" class="form-control" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach ($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }} ({{ $kitchen->kode }})</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete id="modalDeleteMaterials" formId="formDeleteMaterials" title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?" confirmText="Hapus" />
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const kodeInput = document.getElementById('kode_bahan');
            const kitchenSelect = document.querySelector('select[name="kitchen_id"]');
            const generatedCodes = @json($generatedCodes);

            // Logic Generate Kode saat Tambah
            if(kitchenSelect){
                kitchenSelect.addEventListener('change', function() {
                    const kitchenId = this.value;
                    kodeInput.value = generatedCodes[kitchenId] || "";
                });
            }

            let oldKitchenId = null;
            let oldKode = null;

            // --- PERBAIKAN DI SINI ---
            document.querySelectorAll('.btnEditMaterials').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    
                    // 1. UPDATE ACTION FORM (Lakukan ini paling awal agar aman)
                    // Pastikan URL sesuai dengan route update Anda
                    let urlUpdate = "{{ route('dashboard.master.bahan-baku.index') }}/" + id;
                    document.querySelector('#modalEditMaterials form').action = urlUpdate;

                    // 2. Ambil data dari tombol
                    oldKitchenId = this.dataset.oldDapurId;
                    oldKode = this.dataset.oldKode;

                    // 3. Isi Field Input (Gunakan pengecekan if agar tidak error jika elemen hilang)
                    if(document.getElementById('editKodeBahan')) {
                        document.getElementById('editKodeBahan').value = oldKode;
                    }
                    
                    if(document.getElementById('editBahan')) {
                        document.getElementById('editBahan').value = this.dataset.nama;
                    }

                    if(document.getElementById('editSatuan')) {
                        document.getElementById('editSatuan').value = this.dataset.satuanId;
                    }

                    // PENTING: Cek dulu apakah editHarga ada di HTML sebelum di-set value-nya
                    if(document.getElementById('editHarga')) {
                        document.getElementById('editHarga').value = this.dataset.harga;
                    }

                    if(document.getElementById('editDapur')) {
                        document.getElementById('editDapur').value = oldKitchenId;
                    }
                });
            });

            // Logic Ubah Kode saat Edit Dapur diganti
            const editDapur = document.getElementById('editDapur');
            if(editDapur) {
                editDapur.addEventListener('change', function() {
                    const selectedKitchenId = this.value;
                    if (selectedKitchenId == oldKitchenId) {
                        document.getElementById('editKodeBahan').value = oldKode;
                        return;
                    }
                    const kodeBaru = generatedCodes[selectedKitchenId] || "";
                    document.getElementById('editKodeBahan').value = kodeBaru;
                });
            }
        });
    </script>
@endpush
