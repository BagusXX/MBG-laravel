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
                        <th>Dapur</th>
                        <th>Nama Biaya</th>
                        <th>Harga Satuan</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operationals as $index => $operational)
                        <tr>
                            <td>{{ $operationals->firstItem() +  $index}}</td>
                            <td>{{ $operational->kode }}</td>
                            <td>{{ $operational->kitchen->nama ?? '-' }}</td>
                            <td>{{ $operational->nama }}</td>
                            <td>Rp {{ number_format($operational->harga_default, 2, ',', '.') }}</td>
                            <td>{{ $operational->updated_at }}</td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-warning"
                                    onclick="editOperational(this)"
                                    data-id="{{ $operational->id }}"
                                    data-kode="{{ $operational->kode }}"
                                    data-kitchen="{{ $operational->kitchen_kode }}"
                                    data-nama="{{ $operational->nama }}"
                                    data-harga="{{ $operational->harga_default }}" 
                                    data-tanggal="{{ $operational->created_at->format('Y-m-d') }}"
                                >
                                    Edit    
                                </button>
                                {{-- Tombol Hapus --}}
                                <x-button-delete 
                                    idTarget="#modalDeleteOperational" 
                                    formId="formDeleteOperational"
                                    action="{{ route('master.operational.destroy', $operational->id) }}"
                                    text="Hapus" 
                                />

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada Biaya Operational</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-end">
                {{ $operationals->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- MODAL ADD OPERATIONAL --}}
    <x-modal-form
    id="modalAddOperasional"
    title="Tambah Biaya Operasional"
    action="{{ route('master.operational.store') }}"
    submitText="Simpan"
>
        <div class="form-group">
            <label>Kode Transaksi</label>
            <input 
                type="text" 
                name="kode" 
                class="form-control" 
                id="kode_operasional"
                value="{{ $nextKode }}"
                readonly 
                required 
            />
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

        <div class="form-group mt-2">
            <label>Harga Satuan</label>
            <input 
                type="number" 
                name="harga_default" 
                class="form-control"
                min="0"
                step="0.01"
                required
            />
        </div>

        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required />
        </div>
        
    </x-modal-form>

    {{-- MODAL EDIT OPERATIONAL --}}
    <x-modal-form
        id="modalEditOperasional"
        title="Edit Operasional"
        action=""
        submitText="Update"
    >
        @method('PUT')
        <input type="hidden" name="id" id="edit_id">

        <div class="form-group">
            <label>Kode Transaksi</label>
            <input 
                type="text" 
                name="kode" 
                id="edit_kode"
                class="form-control"
                readonly
            />
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
            <input 
                type="text" 
                name="nama" 
                id="edit_nama"
                class="form-control" 
                required
            />
        </div>

        <div class="form-group mt-2">
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
        </div>


        <div class="form-group mt-2">
            <label>Tanggal</label>
            <input 
                type="date" 
                name="tanggal" 
                id="edit_tanggal"
                class="form-control" 
                required
            />
        </div>

    </x-modal-form>

    {{-- MODAL DELETE --}}
    <x-modal-delete 
        id="modalDeleteOperational" 
        formId="formDeleteOperational" 
        title="Konfirmasi Hapus" 
        message="Apakah Anda yakin ingin menghapus data ini?" 
        confirmText="Hapus" 
    />  
@endsection

@section('js')
<script>
function editOperational(button) {
    const id = button.dataset.id;

    const form = document.querySelector('#modalEditOperasional form');
    form.action = `/dashboard/master/operational/${id}`;

    // isi input
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_kode').value = button.dataset.kode;
    document.getElementById('edit_kitchen_kode').value = button.dataset.kitchen;
    document.getElementById('edit_nama').value = button.dataset.nama;
    document.getElementById('edit_harga_default').value = button.dataset.harga;
    document.getElementById('edit_tanggal').value = button.dataset.tanggal;

    // tampilkan modal
    $('#modalEditOperasional').modal('show');
}

function capitalizeWords(text) {
    return text
        .toLowerCase()
        .replace(/\b\w/g, char => char.toUpperCase());
}

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('input[name="nama"]');

    inputs.forEach(input => {
        input.addEventListener('input', function () {
            this.value = capitalizeWords(this.value);
        });
    });
});
</script>
@endsection