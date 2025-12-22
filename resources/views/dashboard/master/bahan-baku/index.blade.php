@extends('adminlte::page')

@section('title', 'Bahan Baku')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Bahan Baku</h1>
@endsection

@section('content')

    <x-button-add idTarget="#modalAddMaterials" text="Tambah Bahan Baku" />

    {{-- @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif --}}
    <x-notification-pop-up />

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th> 
                        <th>Nama Bahan</th>
                        <th>Satuan</th>
                        <th>Harga Dapur</th>
                        <th>Harga Mitra</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td>Rp {{ number_format($item->harga_dapur, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_mitra, 0, ',', '.') }}</td>
                            <td>
                                {{-- BUTTON EDIT --}}
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm btnEditMaterials"
                                    data-id="{{ $item->id }}"
                                    data-kode="{{ $item->kode }}"
                                    data-nama="{{ $item->nama }}"
                                    data-satuan="{{ $item->satuan }}"
                                    data-harga-dapur="{{ $item->harga_dapur }}"
                                    data-harga-mitra="{{ $item->harga_mitra }}"
                                    data-dapur-id="{{ $item->kitchen_id }}"
                                    data-old-kode="{{ $item->kode }}"
                                    data-old-dapur-id="{{ $item->kitchen_id }}"
                                    data-toggle="modal"
                                    data-target="#modalEditMaterials"
                                >
                                    Edit
                                </button>

                                <x-button-delete 
                                    idTarget="#modalDeleteMaterials"
                                    formId="formDeleteMaterials"
                                    action="{{ route('dashboard.master.bahan-baku.index.destroy', $item->id) }}"
                                    text="Hapus"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data bahan baku</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form id="reloadForm" method="GET" action="">
        <input type="hidden" name="kitchen_id" id="reloadKitchenId">
    </form>

    {{-- MODAL ADD MATERIALS --}}
    <x-modal-form 
        id="modalAddMaterials" 
        title="Tambah Bahan Baku" 
        action="{{ route('dashboard.master.bahan-baku.index.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Kode</label>
            <input 
                id="kode_bahan" 
                type="text" 
                class="form-control" 
                name="kode" 
                readonly 
                required>
        </div>
        <div class="form-group">
            <label>Nama Bahan</label>
            <input type="text" placeholder="Bawang Merah" class="form-control" name="nama" required>
        </div>
        <div class="form-group">
            <label>Satuan</label>
            <select class="form-control" name="satuan" required>
                <option value="" disabled selected>Pilih Satuan</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->satuan }}">{{ $unit->satuan }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Harga Dapur</label>
            <input type="number" placeholder="10000" class="form-control" name="harga_dapur" required>
        </div>

        <div class="form-group">
            <label>Harga Mitra</label>
            <input type="number" placeholder="10000" class="form-control" name="harga_mitra" required>
        </div>

        <div class="form-group mt-2">
            <label>Dapur</label>
            <select name="kitchen_id" class="form-control" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }} ({{ $kitchen->kode }})</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL EDIT --}}
    <x-modal-form
        id="modalEditMaterials"
        title="Edit Bahan Baku"
        action=""
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Kode</label>
            <input 
                id="editKodeBahan" 
                type="text" 
                class="form-control" 
                name="kode" 
                readonly 
                required>
        </div>

        <div class="form-group">
            <label>Nama Bahan</label>
            <input id="editBahan" type="text" placeholder="Bawang Merah" class="form-control" name="nama" required>
        </div>

        <div class="form-group">
            <label>Satuan</label>
            <select id="editSatuan" class="form-control" name="satuan" required>
                <option value="" disabled selected>Pilih Satuan</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->satuan }}">{{ $unit->satuan }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Harga Dapur</label>
            <input id="editHargaDapur" type="number" class="form-control" name="harga_dapur" required>
        </div>

        <div class="form-group">
            <label>Harga Mitra</label>
            <input id="editHargaMitra" type="number" class="form-control" name="harga_mitra" required>
        </div>

        <div class="form-group mt-2">
            <label>Dapur</label>
            <select id="editDapur" name="kitchen_id" class="form-control" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">{{ $kitchen->nama }} ({{ $kitchen->kode }})</option>
                @endforeach
            </select>
        </div>
    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteMaterials"
        formId="formDeleteMaterials"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
    />
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const kodeInput = document.getElementById('kode_bahan');
            const kitchenSelect = document.querySelector('select[name="kitchen_id"]');

            const generatedCodes = @json($generatedCodes);

            kitchenSelect.addEventListener('change', function () {
                const kitchenId = this.value;
                kodeInput.value = generatedCodes[kitchenId] || "";
            });

            let oldKitchenId = null;
            let oldKode = null;

            document.querySelectorAll('.btnEditMaterials').forEach(btn => {
                btn.addEventListener('click', function () {

                    const id = this.dataset.id;

                    // Simpan dapur lama & kode lama
                    oldKitchenId = this.dataset.oldDapurId;
                    oldKode = this.dataset.oldKode;

                    // Isi field pertama kali
                    document.getElementById('editKodeBahan').value = oldKode;
                    document.getElementById('editBahan').value = this.dataset.nama;
                    document.getElementById('editSatuan').value = this.dataset.satuan;
                    document.getElementById('editHarga').value = this.dataset.harga;
                    document.getElementById('editDapur').value = oldKitchenId;

                    // Update action
                    document.querySelector('#modalEditMaterials form').action =
                        "{{ url('/dashboard/master/bahan-baku') }}/" + id;
                });
            });

            // Ubah kode ketika dapur berubah
            document.getElementById('editDapur').addEventListener('change', function () {
                const selectedKitchenId = this.value;

                // Jika user memilih kembali dapur awal → kembalikan kode lama
                if (selectedKitchenId == oldKitchenId) {
                    document.getElementById('editKodeBahan').value = oldKode;
                    return;
                }

                // Jika dapur berbeda → generate kode baru
                const kodeBaru = generatedCodes[selectedKitchenId] || "";
                document.getElementById('editKodeBahan').value = kodeBaru;
            });
        });
    </script>
@endpush
