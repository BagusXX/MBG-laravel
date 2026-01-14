@extends('adminlte::page')

@section('title', 'Pengajuan Menu')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Pengajuan Bahan Baku (Menu)</h1>
@endsection

@section('content')

<div id="notification-container"></div>

{{-- BUTTON ADD --}}
<x-button-add
    idTarget="#modalAddSubmission"
    text="Tambah Pengajuan Menu"
/>

{{-- FILTER SECTION --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Dapur</label>
                <select id="filterKitchen" class="form-control">
                    <option value="">Semua Dapur</option>
                    @foreach($kitchens as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Tanggal</label>
                <input type="date" id="filterDate" class="form-control">
            </div>
        </div>
    </div>
</div>

{{-- TABLE DATA --}}
<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped" id="tableSubmission">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Menu</th>
                    <th>Porsi</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr 
                    data-kitchen="{{ $item->kitchen->nama ?? '' }}"
                    data-status="{{ $item->status }}"
                    data-date="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                >
                    <td>{{ $item->kode }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                    <td>{{ $item->menu->nama ?? '-' }}</td>
                    <td>{{ $item->porsi }}</td>
                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-{{
                            $item->status === 'diterima' ? 'success' :
                            ($item->status === 'selesai' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning')))
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{-- Tombol Detail --}}
                        <button class="btn btn-info btn-sm"
                            data-toggle="modal"
                            data-target="#modalDetail{{ $item->id }}">
                            <i class="fas fa-eye"></i>
                        </button>

                        {{-- Tombol Hapus (Hanya jika status diajukan) --}}
                        @if($item->status === 'diajukan')
                        <x-button-delete
                            idTarget="#modalDeleteSubmission"
                            formId="formDeleteSubmission"
                            action="{{ route('transaction.submission.destroy', $item->id) }}"
                            text=""
                        />
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i><br>
                        Belum ada data pengajuan bahan baku.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $submissions->links() }}
        </div>
    </div>
</div>

{{-- =========================
    MODAL TAMBAH
========================= --}}
<x-modal-form 
    id="modalAddSubmission"
    size="modal-lg"
    title="Tambah Pengajuan Bahan Baku"
    action="{{ route('transaction.submission.store') }}"
    submitText="Simpan Pengajuan"
>
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Kode Pengajuan</label>
                <input type="text" class="form-control" value="{{ $nextKode }}" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Dapur</label>
        <select name="kitchen_id" id="selectKitchenStore" class="form-control" required>
            <option value="" disabled selected>Pilih Dapur</option>
            @foreach($kitchens as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label>Menu</label>
                <select name="menu_id" id="selectMenuStore" class="form-control" required disabled>
                    <option value="" disabled selected>Pilih Menu (Pilih Dapur Terlebih Dahulu)</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Jumlah Porsi</label>
                <input type="number" name="porsi" class="form-control" min="1" placeholder="0" required>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-2">
        <i class="fas fa-info-circle"></i> Sistem akan otomatis menghitung rincian bahan baku berdasarkan resep menu yang dipilih.
    </div>
</x-modal-form>

{{-- =========================
    MODAL DETAIL
========================= --}}
@foreach($submissions as $item)
<x-modal-detail 
    id="modalDetail{{ $item->id }}"
    size="modal-lg"
    title="Detail Pengajuan Bahan Baku"
>
    <div class="row mb-3">
        <div class="col-md-6">
            <table class="table table-sm table-borderless">
                <tr><th width="120">Kode</th><td>: {{ $item->kode }}</td></tr>
                <tr><th>Tanggal</th><td>: {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td></tr>
                <tr><th>Dapur</th><td>: {{ $item->kitchen->nama ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-borderless">
                <tr><th width="120">Menu</th><td>: {{ $item->menu->nama ?? '-' }}</td></tr>
                <tr><th>Porsi</th><td>: {{ $item->porsi }} Porsi</td></tr>
                <tr><th>Total Estimasi</th><td>: <strong>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</strong></td></tr>
            </table>
        </div>
    </div>

    <table class="table table-bordered table-sm">
        <thead class="bg-light">
            <tr>
                <th>Bahan Baku</th>
                <th class="text-center">Qty Digunakan</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($item->details as $det)
            <tr>
                <td>{{ $det->bahanBaku->nama ?? '-' }}</td>
                <td class="text-center">
                    {{ number_format($det->qty_digunakan, 2) }} {{ $det->bahanBaku->unit->nama ?? '' }}
                </td>
                <td class="text-right">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($det->subtotal_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</x-modal-detail>
@endforeach

<x-modal-delete 
    id="modalDeleteSubmission"
    formId="formDeleteSubmission"
    title="Konfirmasi Hapus" 
    message="Apakah Anda yakin ingin menghapus pengajuan ini?" 
/>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        // --- FILTER TABLE ---
        $('#filterKitchen, #filterStatus, #filterDate').on('change', function() {
            let kitchen = $('#filterKitchen').val()?.toLowerCase() || '';
            let status = $('#filterStatus').val()?.toLowerCase() || '';
            let date = $('#filterDate').val();

            $('#tableSubmission tbody tr').each(function () {
                let rKitchen = $(this).data('kitchen')?.toLowerCase() || '';
                let rStatus = $(this).data('status')?.toLowerCase() || '';
                let rDate = $(this).data('date') || '';
                
                let show = true;
                if (kitchen && rKitchen !== kitchen) show = false;
                if (status && rStatus !== status) show = false;
                if (date && rDate !== date) show = false;
                $(this).toggle(show);
            });
        });

        // --- FETCH MENU BERDASARKAN DAPUR ---
        // Karena data menu biasanya banyak, disarankan menggunakan AJAX
        // Namun jika data menu dilempar via variable $menus, bisa gunakan logic filter JS seperti di bawah
        $('#selectKitchenStore').on('change', function() {
            let kitchenId = $(this).val();
            let menuSelect = $('#selectMenuStore');
            
            menuSelect.prop('disabled', false).html('<option value="" disabled selected>Memuat Menu...</option>');

            // Simulasi fetch menu. Jika Anda sudah punya route untuk ambil menu per kitchen:
            $.ajax({
                url: "{{ url('api/get-menu-by-kitchen') }}/" + kitchenId, // Sesuaikan route API Anda
                method: "GET",
                success: function(data) {
                    menuSelect.html('<option value="" disabled selected>Pilih Menu</option>');
                    data.forEach(function(menu) {
                        menuSelect.append(`<option value="${menu.id}">${menu.nama}</option>`);
                    });
                },
                error: function() {
                    menuSelect.html('<option value="" disabled selected>Gagal memuat menu</option>');
                }
            });
        });
    });

    // Helper notification (jika diperlukan untuk response AJAX)
    function showNotification(type, message) {
        const container = document.getElementById('notification-container');
        if (!container) return;
        const notif = document.createElement('div');
        notif.className = `notification ${type} show`;
        notif.innerText = message;
        container.appendChild(notif);
        setTimeout(() => {
            notif.classList.remove('show');
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }
</script>
@endsection