@extends('adminlte::page')

@section('title', 'Pengajuan Operasional')

@section('content_header')
    <h1>Pengajuan Operasional</h1>
@endsection

@section('content')

{{-- ALERT SYSTEM --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terjadi Kesalahan Input:</strong>
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- BUTTON ADD --}}
<button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalAddOperational">
    <i class="fas fa-plus mr-1"></i> Tambah Pengajuan Operasional
</button>

{{-- FILTER SECTION --}}
<div class="card mb-3">
    <div class="card-header bg-light">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Data</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Dapur</label>
                <select id="filterKitchen" class="form-control">
                    <option value="">Semua Dapur</option>
                    @foreach($kitchens as $k)
                        {{-- Menggunakan nama untuk display di filter JS --}}
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="diterima">Diterima</option>
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
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0" id="tableSubmission">
            <thead class="thead-dark">
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Jml Item</th>
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
                    data-date="{{ $item->created_at->format('Y-m-d') }}"
                >
                    <td class="font-weight-bold text-primary">{{ $item->kode }}</td>
                    <td>{{ $item->created_at->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                    <td>{{ $item->details->count() }} Item</td>
                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $badgeClass = match($item->status) {
                                'diterima' => 'success',
                                'ditolak' => 'danger',
                                default => 'warning'
                            };
                        @endphp
                        <span class="badge badge-{{ $badgeClass }} px-2 py-1">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{-- Tombol Detail --}}
                        <button class="btn btn-info btn-sm"
                            data-toggle="modal"
                            data-target="#modalDetail{{ $item->id }}"
                            title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </button>

                        {{-- Tombol Hapus (Hanya jika belum diterima) --}}
                        @if($item->status !== 'diterima')
                        <form action="{{ route('transaction.operational-submission.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan {{ $item->kode }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i><br>
                        Belum ada data pengajuan operasional.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- =========================
    MODAL TAMBAH (DYNAMIC FORM)
========================= --}}
<div class="modal fade" id="modalAddOperational" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('transaction.operational-submission.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Tambah Pengajuan Operasional</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    {{-- HEADER INPUTS --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kode Transaksi</label>
                                <input type="text" class="form-control bg-light" value="OTOMATIS (POPR...)" readonly>
                                <small class="text-muted">Kode akan digenerate otomatis oleh sistem.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Pengajuan</label>
                                <input type="text" class="form-control bg-light" value="{{ date('d-m-Y') }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pilih Dapur <span class="text-danger">*</span></label>
                        {{-- Name: kitchen_kode (Sesuai validasi controller) --}}
                        <select name="kitchen_kode" class="form-control" required>
                            <option value="">-- Pilih Dapur --</option>
                            @foreach($kitchens as $k)
                                <option value="{{ $k->kode }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Daftar Barang</h5>
                        <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                            <i class="fas fa-plus"></i> Tambah Baris
                        </button>
                    </div>
                    
                    {{-- TABEL INPUT ITEMS --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="inputTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Barang Operasional <span class="text-danger">*</span></th>
                                    <th width="150">Harga Satuan (Rp)</th>
                                    <th width="100">Qty <span class="text-danger">*</span></th>
                                    <th width="180">Subtotal (Rp)</th>
                                    <th width="50" class="text-center"><i class="fas fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody id="inputContainer">
                                {{-- Baris Pertama (Default) --}}
                                <tr>
                                    <td>
                                        <select name="items[0][barang_id]" class="form-control item-select" required onchange="updatePrice(this)">
                                            <option value="" data-price="0">Pilih Barang</option>
                                            @foreach($masterBarang as $brg)
                                                <option value="{{ $brg->id }}" data-price="{{ $brg->harga_default }}">
                                                    {{ $brg->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        {{-- Readonly tapi tetap dikirim (untuk validasi items.*.harga_satuan) --}}
                                        <input type="number" name="items[0][harga_satuan]" class="form-control price-input bg-light" readonly value="0">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][qty]" class="form-control qty-input" min="1" value="1" required oninput="updateSubtotal(this)">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal-display bg-light" readonly value="0">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row" disabled>X</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold pt-3">ESTIMASI TOTAL:</td>
                                    <td colspan="2" class="font-weight-bold pt-3 text-primary h5" id="grandTotalDisplay">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Pengajuan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- =========================
    MODAL DETAIL (LOOPING)
========================= --}}
@foreach($submissions as $item)
<div class="modal fade" id="modalDetail{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Detail Pengajuan <span class="font-weight-bold text-primary">{{ $item->kode }}</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- INFO HEADER --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="100" class="text-muted">Dapur</td>
                                <td class="font-weight-bold">: {{ $item->kitchen->nama ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal</td>
                                <td class="font-weight-bold">: {{ $item->created_at->format('d F Y, H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-md-right">
                         <div class="mb-1">Status:</div>
                         <span class="badge badge-{{ $item->status == 'diterima' ? 'success' : ($item->status == 'ditolak' ? 'danger' : 'warning') }} p-2" style="font-size: 1rem;">
                            {{ strtoupper($item->status) }}
                        </span>
                    </div>
                </div>

                {{-- TABEL DETAIL ITEMS --}}
                <table class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->details as $index => $det)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $det->barang->nama ?? 'Barang dihapus' }}</td>
                            <td class="text-center">{{ $det->qty }}</td>
                            <td class="text-right">Rp {{ number_format($det->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-right font-weight-bold">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">TOTAL PENGAJUAN</th>
                            <th class="text-right text-primary h5">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('js') {{-- Menggunakan section js, sesuaikan jika Anda pakai push('js') --}}
<script>
    $(document).ready(function() {

        /**
         * ---------------------------------------
         * 1. FILTER LOGIC
         * ---------------------------------------
         */
        function applyFilter() {
            let kitchen = $('#filterKitchen').val().toLowerCase();
            let status = $('#filterStatus').val().toLowerCase();
            let date = $('#filterDate').val();

            $('#tableSubmission tbody tr').each(function () {
                let rowKitchen = $(this).data('kitchen').toLowerCase();
                let rowStatus = $(this).data('status').toLowerCase();
                let rowDate = $(this).data('date');

                let show = true;
                if (kitchen && rowKitchen !== kitchen) show = false;
                if (status && rowStatus !== status) show = false;
                if (date && rowDate !== date) show = false;

                $(this).toggle(show);
            });
        }

        $('#filterKitchen, #filterStatus, #filterDate').on('change', applyFilter);

        /**
         * ---------------------------------------
         * 2. DYNAMIC FORM (TAMBAH BARANG)
         * ---------------------------------------
         */
        let rowIdx = 1;

        // Simpan Opsi Barang ke Variable JS agar mudah dicopy saat tambah baris
        const barangOptions = `
            <option value="" data-price="0">Pilih Barang</option>
            @foreach($masterBarang as $brg)
                <option value="{{ $brg->id }}" data-price="{{ $brg->harga_default }}">
                    {{ $brg->nama }}
                </option>
            @endforeach
        `;

        // Fungsi Tambah Baris
        $('#addRowBtn').click(function() {
            let html = `
                <tr>
                    <td>
                        <select name="items[${rowIdx}][barang_id]" class="form-control item-select" required onchange="updatePrice(this)">
                            ${barangOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][harga_satuan]" class="form-control price-input bg-light" readonly value="0">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][qty]" class="form-control qty-input" min="1" value="1" required oninput="updateSubtotal(this)">
                    </td>
                    <td>
                        <input type="text" class="form-control subtotal-display bg-light" readonly value="0">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#inputContainer').append(html);
            rowIdx++;
        });

        // Fungsi Hapus Baris
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        // Global functions agar bisa dipanggil via onchange/oninput HTML attributes
        window.updatePrice = function(selectElement) {
            let price = $(selectElement).find(':selected').data('price');
            let row = $(selectElement).closest('tr');
            
            row.find('.price-input').val(price); // Set harga satuan
            updateSubtotal(selectElement); // Trigger hitung ulang subtotal
        }

        window.updateSubtotal = function(element) {
            let row = $(element).closest('tr');
            let price = parseFloat(row.find('.price-input').val()) || 0;
            let qty = parseFloat(row.find('.qty-input').val()) || 0;
            let subtotal = price * qty;

            // Format Rupiah untuk Display Subtotal
            row.find('.subtotal-display').val("Rp " + subtotal.toLocaleString('id-ID'));
            
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            $('#inputContainer tr').each(function() {
                let price = parseFloat($(this).find('.price-input').val()) || 0;
                let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                total += (price * qty);
            });
            
            // Format Rupiah untuk Grand Total
            $('#grandTotalDisplay').text("Rp " + total.toLocaleString('id-ID'));
        }

        // Reset Modal Form saat ditutup (Opsional, agar bersih saat dibuka lagi)
        $('#modalAddOperational').on('hidden.bs.modal', function () {
            // Uncomment baris di bawah jika ingin mereset form setiap kali tutup modal
            // $(this).find('form')[0].reset();
            // $('#inputContainer').find('tr:not(:first)').remove(); // Hapus baris tambahan
            // calculateGrandTotal();
        });
    });
</script>
@endsection