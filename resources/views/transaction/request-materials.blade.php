@extends('adminlte::page')

@section('title', 'Daftar Permintaan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Daftar Permintaan</h1>
@endsection

@section('content')

    {{-- BUTTON ADD --}}
    {{-- <x-button-add
        idTarget="#modalAddSubmission"
        text="Tambah Pengajuan Menu"
    /> --}}

    <x-notification-pop-up />

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Nama Dapur</th>
                        <th>Nama Menu</th>
                        <th>Porsi</th>
                        <th>Status</th>
                        <th width="250">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- @forelse ($submission as $item) --}}
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm"
                                    data-toggle="modal"
                                    data-target="#modalDetail"
                                >
                                    Detail
                                </button>

                                <button 
                                    type="button" 
                                    class="btn btn-warning btn-sm btnEditSubmission"
                                    data-toggle="modal"
                                    data-target="#modalEditSubmission"
                                >
                                    Update Status
                                </button>

                                {{-- <x-button-delete 
                                    idTarget="#modalDeleteSubmission"
                                    formId="formDeleteSubmission"
                                    action="{{ route('submissions.destroy', $item->id) }}"
                                    text="Hapus"
                                /> --}}
                            </td>
                        </tr>
                    {{-- @empty --}}
                        {{-- <tr>
                            <td colspan="6" class="text-center">
                                Belum ada data pengajuan
                            </td>
                        </tr> --}}
                    {{-- @endforelse --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= MODAL ADD ================= --}}
    <x-modal-form
        id="modalAddSubmission"
        title="Tambah Pengajuan Menu"
        action="{{ route('submissions.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Kode</label>
            <input 
                id="kode_pengajuan" 
                type="text" 
                class="form-control" 
                name="kode" 
                readonly 
                required
            />
        </div>

        <div class="form-group">
            <label>Tanggal</label>
            <input 
                type="date" 
                class="form-control" 
                name="tanggal" 
                required
            >
        </div>

        <div class="form-group">
            <label>Nama Dapur</label>
            <select class="form-control" name="kitchen_id" id="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
                {{-- @foreach ($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">
                        {{ $kitchen->nama }}
                    </option>
                @endforeach --}}
            </select>

        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <select class="form-control" name="menu_id" id="menu_id" required>
                <option value="" disabled selected>Pilih Menu</option>
            </select>
        </div>

        <div class="form-group">
            <label>Porsi</label>
            <input 
                type="number" 
                class="form-control" 
                name="porsi" 
                placeholder="100"
                required
            >
        </div>
    </x-modal-form>

    {{-- ================= MODAL EDIT ================= --}}
    <x-modal-form
        id="modalEditSubmission"
        title="Update Status"
        action=""
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="menu_id" id="menu_id" required>
                <option value="" disabled selected>Pilih Status</option>
                <option value="">Proses</option>
                <option value="">Selesai</option>
            </select>
        </div>
    </x-modal-form>

    {{-- ================= MODAL DETAIL ================= --}}
    <x-modal-detail
        id="modalDetail"
        size="modal-lg"
        title="Detail Permintaan"
    >
        <p class="text-muted">
            Detail ini masih hanya teks biasa, mekanisme segera akan dikembangkan menjadi dinamis.
        </p>
    </x-modal-detail>

    {{-- ================= MODAL DELETE ================= --}}
    <x-modal-delete 
        id="modalDeleteSubmission"
        formId="formDeleteSubmission"
        title="Konfirmasi Hapus"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
    />

@endsection

@push('js')
<script>
    document.getElementById('kitchen_id').addEventListener('change', function () {
        let kitchenId = this.value;
        let menuSelect = document.getElementById('menu_id');

        // reset menu
        menuSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';

        fetch(`/dashboard/transaksi/pengajuan-menu/menu-by-kitchen/${kitchenId}`)
            .then(response => response.json())
            .then(data => {
                menuSelect.innerHTML = '<option value="" disabled selected>Pilih Menu</option>';

                data.forEach(menu => {
                    menuSelect.innerHTML += `
                        <option value="${menu.id}">
                            ${menu.nama}
                        </option>
                    `;
                });
            });
    });
</script>

@endpush
