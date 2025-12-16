@extends('adminlte::page')

@section('title', 'Pengajuan Menu')

@section('content_header')
    <h1>Pengajuan Menu</h1>
@endsection

@section('content')

    {{-- BUTTON ADD --}}
    <x-button-add
        idTarget="#modalAddSubmission"
        text="Tambah Pengajuan Menu"
    />

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
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($submission as $item)
                        <tr>
                            <td>{{ $item->kode }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                            <td>{{ $item->kitchen->nama ?? '-' }}</td>
                            <td>{{ $item->menu->nama ?? '-' }}</td>
                            <td>{{ number_format($item->porsi) }}</td>
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
                                    Edit
                                </button>

                                <x-button-delete 
                                    idTarget="#modalDeleteSubmission"
                                    formId="formDeleteSubmission"
                                    action="{{ route('submissions.destroy', $item->id) }}"
                                    text="Hapus"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                Belum ada data pengajuan
                            </td>
                        </tr>
                    @endforelse
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
            <select class="form-control" name="kitchen_id" required>
                <option value="" disabled selected>Pilih Dapur</option>
                @foreach ($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">
                        {{ $kitchen->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Nama Menu</label>
            <select class="form-control" name="menu_id" required>
                <option value="" disabled selected>Pilih Menu</option>
                @foreach ($menus as $menu)
                    <option value="{{ $menu->id }}">
                        {{ $menu->nama }}
                    </option>
                @endforeach
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
        title="Edit Pengajuan Menu"
        action=""
        submitText="Update"
    >
        @method('PUT')
    </x-modal-form>

    {{-- ================= MODAL DETAIL ================= --}}
    <x-modal-detail
        id="modalDetail"
        size="modal-lg"
        title="Detail Pengajuan Menu"
    >
        <p class="text-muted">
            Detail masih contoh, bisa dibuat dinamis dengan JS / AJAX
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

        fetch(`/dashboard/transaksi/pengajuan-menu/generate-kode/${kitchenId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('kode_pengajuan').value = data.kode;
            });
    });
</script>
@endpush
