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
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
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
                    <th>Tanggal Pengajuan</th>
                    <th>Dapur</th>
                    <th>Menu</th>
                    <th>Porsi</th>
                    {{-- <th>Total Biaya</th> --}}
                    <th>Status</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr 
                    data-kitchen="{{ $item->kitchen_id }}"
                    data-status="{{ $item->status }}"
                    data-date="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                >
                    <td>{{ $item->kode }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                    <td>{{ $item->menu->nama ?? '-' }}</td>
                    <td>{{ $item->porsi }}</td>
                    {{-- <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td> --}}
                    <td>
                        <span class="badge badge-{{
                            $item->status === 'diterima' ? 'success' :
                            ($item->status === 'selesai' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                            ($item->status === 'diajukan' ? 'warning' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning'))))
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{-- Tombol Detail --}}
                        <button class="btn btn-info btn-sm btn-detail"
                            data-id="{{ $item->id }}"
                            data-toggle="modal"
                            data-target="#modalDetail">
                            Detail
                        </button>

                        {{-- Tombol Hapus (Hanya jika status diajukan) --}}
                        @if($item->status === 'diajukan' || $item->status === 'ditolak')
                        <x-button-delete
                            idTarget="#modalDeleteSubmission"
                            formId="formDeleteSubmission"
                            action="{{ route('transaction.submission.destroy', $item->id) }}"
                            text="Hapus"
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
                <label>Tanggal Pengajuan</label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="selectKitchenStore">Dapur</label>
        {{-- ID INI PENTING UNTUK AJAX --}}
        <select name="kitchen_id" id="selectKitchenStore" class="form-control" required>
            <option value="">Pilih Dapur</option>
            @foreach($kitchens as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label for="selectMenuStore">Menu</label>
                 {{-- ID INI PENTING UNTUK AJAX --}}
                <select name="menu_id" id="selectMenuStore" class="form-control" required disabled>
                    <option value="">Pilih Menu (Pilih Dapur Terlebih Dahulu)</option>
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
    MODAL DETAIL (ONE FILE VERSION)
========================= --}}
<x-modal-detail 
    id="modalDetail"
    size="modal-lg"
    title="Detail Pengajuan Bahan Baku"
>
    {{-- Header Info --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <table class="table table-borderless table-sm">
                <tr><th width="30%">Kode</th><td>: <span id="det-kode" >-</span></td></tr>
                <tr><th width="60%">Tanggal Pengajuan</th><td>: <span id="det-tanggal">-</span></td></tr>
                <tr><th>Status</th><td>: <span id="det-status">-</span></td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-borderless table-sm">
                <tr><th width="30%">Dapur</th><td>: <span id="det-dapur">-</span></td></tr>
                <tr><th>Menu</th><td>: <span id="det-menu">-</span></td></tr>
                <tr><th>Porsi</th><td>: <span id="det-porsi">-</span></td></tr>
            </table>
        </div>
    </div>

    {{-- Table Items --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Satuan</th>
                </tr>
            </thead>
            <tbody id="det-tbody">
                {{-- DATA AKAN DI-INJECT VIA JAVASCRIPT --}}
            </tbody>
            <!-- <tfoot>
                <tr>
                    <td colspan="4" class="text-right font-weight-bold">TOTAL ESTIMASI</td>
                    <td class="text-right font-weight-bold text-primary" id="det-total">0</td>
                </tr>
            </tfoot> -->
        </table>
    </div>
    
    <div id="loading-spinner" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Memuat data...</p>
    </div>

    {{-- RIWAYAT SPLIT ORDER --}}
    <div id="sectionRiwayat" class="mt-4 pt-3 border-top">
        <h6 class="font-weight-bold text-secondary mb-3">Riwayat Approval (Split Order)</h6>
        <div id="wrapperRiwayat">
            {{-- Inject JS --}}
        </div>
    </div>
</x-modal-detail>



{{-- =========================
    MODAL DELETE
========================= --}}
<x-modal-delete 
    id="modalDeleteSubmission"
    formId="formDeleteSubmission"
    title="Konfirmasi Hapus" 
    message="Apakah Anda yakin ingin menghapus pengajuan ini?" 
/>

@endsection

@section('js')
<script>

    // Helper Format Rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', 
            currency: 'IDR',
            minimumFractionDigits: 0 
        }).format(number);
    };

    // Helper Format Tanggal Indo
    const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };

    $(document).ready(function() {
        // --- FILTER TABLE ---
        $('#filterKitchen, #filterStatus, #filterDate').on('change', function() {
            let kitchen = $('#filterKitchen').val();
            let status = $('#filterStatus').val()?.toLowerCase() || '';
            let date = $('#filterDate').val();

            $('#tableSubmission tbody tr').each(function () {
                let rKitchen = $(this).data('kitchen');
                let rStatus = $(this).data('status')?.toLowerCase() || '';
                let rDate = $(this).data('date') || '';
                
                let show = true;
                if (kitchen && String(rKitchen) !== String(kitchen)) show = false;  
                if (status && rStatus !== status) show = false;
                if (date && rDate !== date) show = false;
                $(this).toggle(show);
            });
        });

        // --- FETCH MENU BERDASARKAN DAPUR (FIXED) ---
        $('#selectKitchenStore').on('change', function() {
            let kitchenId = $(this).val();
            let menuSelect = $('#selectMenuStore');

            // Reset kondisi awal
            menuSelect.empty();
            
            if (!kitchenId) {
                menuSelect
                    .prop('disabled', true)
                    .append('<option value="">Pilih Dapur Terlebih Dahulu</option>');
                return;
            }

            menuSelect
                .prop('disabled', true)
                .append('<option value="">Sedang memuat menu...</option>');

            // GUNAKAN FAKE_ID AGAR TIDAK TERKENA URL ENCODE (%3A)
            let url = "{{ route('transaction.submission.menu-by-kitchen', ['kitchenId' => 'FAKE_ID']) }}";
            url = url.replace('FAKE_ID', kitchenId);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json', // Pastikan response dianggap JSON
                success: function(data) {
                    menuSelect.empty().prop('disabled', false);
                    menuSelect.append('<option value="">Pilih Menu</option>');

                    if (Array.isArray(data) && data.length > 0) {
                        $.each(data, function(_, menu) {
                            menuSelect.append(
                                `<option value="${menu.id}">${menu.nama}</option>`
                            );
                        });
                    } else {
                        menuSelect.append('<option value="">Menu tidak tersedia di dapur ini</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching menu:", error);
                    menuSelect
                        .empty()
                        .prop('disabled', true)
                        .append('<option value="">Gagal memuat menu</option>');
                }
            });
        });

        // --- FETCH DATA DETAIL (MODIFIKASI JSON) ---
        $('.btn-detail').on('click', function () {
            let id = $(this).data('id');
            let url = "{{ route('transaction.submission.detail', ['submission' => 'FAKE_ID']) }}".replace('FAKE_ID', id);
            
            // 1. Tampilkan Loading, Sembunyikan Konten
            $('#det-tbody').empty();
            $('#loading-spinner').show();
            $('.table-responsive').hide(); 

            // 2. AJAX Fetch JSON
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Isi Header
                    $('#det-kode').text(data.kode);
                    $('#det-tanggal').text(formatDate(data.tanggal));
                    $('#det-dapur').text(data.kitchen ? data.kitchen.nama : '-');
                    $('#det-menu').text(data.menu ? data.menu.nama : '-');
                    $('#det-porsi').text(data.porsi);
                    // $('#det-total').text(formatRupiah(data.total_harga));

                    // Logic Warna Badge Status
                    let badgeClass = 'secondary';
                    if(data.status === 'diajukan') badgeClass = 'warning';
                    else if(data.status === 'diproses') badgeClass = 'info';
                    else if(data.status === 'selesai' || data.status === 'diterima') badgeClass = 'success';
                    else if(data.status === 'ditolak') badgeClass = 'danger';
                    
                    $('#det-status').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);

                    // Isi Tabel Details
                    let rows = '';
                    if (data.details && data.details.length > 0) {
                        $.each(data.details, function(index, item) {
                            let namaBahan = item.bahan_baku ? item.bahan_baku.nama : 'Bahan Terhapus';
                            let satuan    = item.bahan_baku?.unit?.satuan || '-';
                            let isManual = item.recipe_bahan_baku_id === null ? '<small class="text-info d-block">(Manual)</small>' : '';

                            rows += `
                                <tr>
                                    <td>${namaBahan} ${isManual}</td>
                                    <td class="text-center">${parseFloat(item.qty_digunakan)}</td>
                                    <td class="text-center">${satuan}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="5" class="text-center text-muted">Tidak ada rincian bahan baku</td></tr>';
                    }

                    $('#det-tbody').html(rows);
                    // ==========================================
                    // LOGIC RENDER RIWAYAT SPLIT ORDER (BARU)
                    // ==========================================
                    let historyHtml = '';

                    if (data.history && data.history.length > 0) {
                        $.each(data.history, function(i, h) {
                            // 1. Generate List Item per Split Order
                            let itemsHtml = '';
                            if (h.items && h.items.length > 0) {
                                h.items.forEach(item => {
                                    itemsHtml += `
                                        <li>
                                            ${item.nama}
                                            <span class="text-muted">(${parseFloat(item.qty)} x ${formatRupiah(item.harga)})</span>
                                        </li>
                                    `;
                                });
                            } else {
                                itemsHtml = '<li class="text-muted font-italic">Tidak ada item</li>';
                            }

                            // 2. Buat URL Invoice (Sesuaikan route Anda)
                            // Pastikan route ini bisa diakses oleh role yang membuka halaman ini
                            let invoiceUrl = "{{ url('dashboard/transaksi/approval-menu') }}/" + h.id + "/invoice";

                            // 3. Gabungkan HTML (Diadaptasi dari kode Approval Anda)
                            historyHtml += `
                                <div class="card mb-2 border" style="background-color: #f8f9fa;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong class="text-dark">${h.kode}</strong> 
                                                <span class="text-muted mx-2">|</span> 
                                                <i class="fas fa-truck mr-1 text-secondary"></i> ${h.supplier_nama}
                                            </div>
                                            <div>
                                                <span class="badge badge-success mr-2">DISETUJUI</span>
                                                <strong class="text-dark">${formatRupiah(h.total)}</strong>
                                            </div>
                                        </div>

                                        {{-- List Item Ringkas --}}
                                        <ul class="mb-2 pl-3 small text-secondary" style="list-style-type: circle;">
                                            ${itemsHtml}
                                        </ul>

                                        {{-- Tombol Cetak Invoice --}}
                                        <div class="text-right border-top pt-2">
                                            <a href="${invoiceUrl}" target="_blank" class="btn btn-xs btn-outline-secondary">
                                                <i class="fas fa-print mr-1"></i> Cetak Invoice
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        historyHtml = '<div class="text-muted font-italic text-center py-2 border rounded bg-light">Belum ada riwayat split order / approval.</div>';
                    }

                    // Inject ke dalam div wrapperRiwayat
                    $('#wrapperRiwayat').html(historyHtml);

                    // Selesai Loading
                    $('#loading-spinner').hide();
                    $('.table-responsive').show();
                },
                error: function() {
                    $('#loading-spinner').hide();
                    alert('Gagal mengambil data detail');
                }
            });
        });
        // Helper notification (Opsional)
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
        
        
    });
    
</script>
@endsection