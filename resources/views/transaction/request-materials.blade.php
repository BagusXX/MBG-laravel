@extends('adminlte::page')

@section('title', 'Daftar Permintaan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Daftar Permintaan</h1>
@endsection

@section('content')

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
                    @forelse ($submissions as $item)
                        <tr>
                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->tanggal }}</td>
                            <td>{{ $item->kitchen->nama ?? '-' }}</td>
                            <td>{{ $item->menu->nama ?? '-' }}</td>
                            <td>{{ $item->porsi }}</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $item->status ?? 'Proses' }}
                                </span>
                            </td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm btnDetailRequest"
                                    data-toggle="modal"
                                    data-target="#modalDetail"
                                    data-kode="{{ $item->kode }}"
                                    data-tanggal="{{ $item->tanggal }}"
                                    data-dapur="{{ $item->kitchen->nama ?? '-' }}"
                                    data-menu="{{ $item->menu->nama ?? '-' }}"
                                    data-porsi="{{ $item->porsi }}"
                                >
                                    Detail
                                </button>

                                <button 
                                    type="button" 
                                    class="btn btn-warning btn-sm btnEditSubmission"
                                    data-toggle="modal"
                                    data-target="#modalEditSubmission"
                                    data-id="{{ $item->id }}"
                                >
                                    Update Status
                                </button>

                                <form 
                                    action="{{ route('transaction.submission.destroy', $item->id) }}" 
                                    method="POST" 
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus data ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
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
        action="{{ route('transaction.submission.store') }}"
        submitText="Simpan"
    >
        <div class="form-group">
            <label>Kode</label>
            <input 
                type="text" 
                class="form-control" 
                name="kode" 
                value="{{ 'SUB-' . now()->format('YmdHis') }}"
                readonly
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
                @foreach ($kitchens as $kitchen)
                    <option value="{{ $kitchen->id }}">
                        {{ $kitchen->nama }}
                    </option>
                @endforeach
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
                min="1"
                required
            >
        </div>
    </x-modal-form>

    {{-- ================= MODAL EDIT ================= --}}
    <x-modal-form
        id="modalEditSubmission"
        title="Update Status"
        action="#"
        submitText="Update"
    >
        @method('PUT')

        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status" required>
                <option value="Proses">Proses</option>
                <option value="Selesai">Selesai</option>
            </select>
        </div>
    </x-modal-form>

    {{-- ================= MODAL DETAIL ================= --}}
    <x-modal-detail
        id="modalDetail"
        size="modal-lg"
        title="Detail Permintaan"
    >
        <div style="font-size: 16px; line-height: 2;">
            <div><strong>Kode</strong>: <span id="detail-kode">-</span></div>
            <div><strong>Tanggal</strong>: <span id="detail-tanggal">-</span></div>
            <div><strong>Dapur</strong>: <span id="detail-dapur">-</span></div>
            <div><strong>Menu</strong>: <span id="detail-menu">-</span></div>
            <div><strong>Porsi</strong>: <span id="detail-porsi">-</span></div>
        </div>
    </x-modal-detail>

@endsection

@push('js')
<script>
    document.getElementById('kitchen_id').addEventListener('change', function () {
        let kitchenId = this.value;
        let menuSelect = document.getElementById('menu_id');

        menuSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';

        fetch(`/dashboard/transaksi/submission/menu/${kitchenId}`)
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

    // Populate modal detail when Detail button is clicked
    $(document).on('click', '.btnDetailRequest', function () {
        let kode = $(this).data('kode');
        let tanggal = $(this).data('tanggal');
        let dapur = $(this).data('dapur');
        let menu = $(this).data('menu');
        let porsi = $(this).data('porsi');

        // Format tanggal dari YYYY-MM-DD ke DD-MM-YYYY
        let tanggalFormatted = '-';
        if (tanggal) {
            let date = new Date(tanggal);
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            tanggalFormatted = `${day}-${month}-${year}`;
        }

        // Populate modal
        $('#detail-kode').text(kode || '-');
        $('#detail-tanggal').text(tanggalFormatted);
        $('#detail-dapur').text(dapur || '-');
        $('#detail-menu').text(menu || '-');
        $('#detail-porsi').text(porsi || '-');
    });
</script>
@endpush
