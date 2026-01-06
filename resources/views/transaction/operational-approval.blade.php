@extends('adminlte::page')

@section('title', 'Persetujuan Operasional')

@section('content_header')
    <h1>Persetujuan Operasional</h1>
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

{{-- FILTER SECTION --}}
@php
    // Ambil list nama dapur unik dari data submissions untuk filter dropdown
    // (Karena controller tidak mengirim variable $kitchens)
    $uniqueKitchens = $submissions->pluck('kitchen.nama')->unique()->filter();
@endphp

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
                    @foreach($uniqueKitchens as $dapurNama)
                        <option value="{{ $dapurNama }}">{{ $dapurNama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="diajukan">Diajukan (Menunggu)</option>
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
        <table class="table table-striped table-hover mb-0" id="tableApproval">
            <thead class="thead-dark">
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Jml Item</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th width="100" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr 
                    data-kitchen="{{ $item->kitchen->nama ?? '' }}"
                    data-status="{{ $item->status }}"
                    data-date="{{ $item->created_at->format('Y-m-d') }}"
                >
                    <td class="font-weight-bold">{{ $item->kode }}</td>
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
                        <button class="btn btn-primary btn-sm"
                            data-toggle="modal"
                            data-target="#modalApproval{{ $item->id }}"
                            title="Proses Pengajuan">
                            <i class="fas fa-search-plus"></i> Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-check-double fa-3x mb-3"></i><br>
                        Tidak ada data pengajuan yang perlu diproses.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- =========================
    MODAL DETAIL & APPROVAL (LOOPING)
========================= --}}
@foreach($submissions as $item)
<div class="modal fade" id="modalApproval{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header {{ $item->status == 'diajukan' ? 'bg-primary' : 'bg-secondary' }} text-white">
                <h5 class="modal-title">
                    Proses Pengajuan: <span class="font-weight-bold">{{ $item->kode }}</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                {{-- INFO HEADER --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="100">Dapur</td>
                                <td class="font-weight-bold">: {{ $item->kitchen->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal</td>
                                <td class="font-weight-bold">: {{ $item->created_at->format('d F Y, H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-right">
                        Status Saat Ini:<br>
                        <span class="badge badge-{{ $item->status == 'diterima' ? 'success' : ($item->status == 'ditolak' ? 'danger' : 'warning') }} p-2" style="font-size: 1rem;">
                            {{ strtoupper($item->status) }}
                        </span>
                    </div>
                </div>

                {{-- TABEL BARANG --}}
                <h6 class="font-weight-bold text-secondary">Detail Barang</h6>
                <table class="table table-bordered table-sm mb-3">
                    <thead class="bg-light">
                        <tr>
                            <th>Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->details as $det)
                        <tr>
                            <td>{{ $det->barang->nama ?? 'Item dihapus' }}</td>
                            <td class="text-center">{{ $det->qty }}</td>
                            <td class="text-right">{{ number_format($det->harga_satuan) }}</td>
                            <td class="text-right">{{ number_format($det->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total Biaya</th>
                            <th class="text-right font-weight-bold">Rp {{ number_format($item->total_harga) }}</th>
                        </tr>
                    </tfoot>
                </table>

                @if($item->status == 'ditolak' && $item->keterangan)
                    <div class="alert alert-danger">
                        <strong>Alasan Penolakan:</strong><br>
                        {{ $item->keterangan }}
                    </div>
                @endif

                {{-- FORM APPROVAL (Hanya muncul jika status masih DIAJUKAN) --}}
                @if($item->status === 'diajukan')
                    <hr>
                    <h6 class="font-weight-bold mb-3">Tindakan Persetujuan:</h6>
                    
                    {{-- Pastikan route ini sesuai dengan route web.php Anda --}}
                    {{-- Contoh Route: Route::patch('/operational-approval/{id}/status', ...)->name('operational.approval.update_status') --}}
                    <form action="{{ route('transaction.operational-approval.update-status', $item->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            {{-- TOMBOL TERIMA --}}
                            <div class="col-md-6">
                                <button type="submit" name="status" value="diterima" class="btn btn-success btn-block btn-lg" onclick="return confirm('Yakin ingin MENERIMA pengajuan ini?')">
                                    <i class="fas fa-check-circle"></i> TERIMA PENGAJUAN
                                </button>
                            </div>

                            {{-- TOMBOL TOLAK (Toggle Input Keterangan) --}}
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger btn-block btn-lg" onclick="$('#rejectSection{{ $item->id }}').collapse('toggle')">
                                    <i class="fas fa-times-circle"></i> TOLAK PENGAJUAN
                                </button>
                            </div>
                        </div>

                        {{-- SECTION KETERANGAN TOLAK (Default Hidden) --}}
                        <div class="collapse mt-3" id="rejectSection{{ $item->id }}">
                            <div class="card card-body bg-light border-danger">
                                <div class="form-group">
                                    <label class="text-danger">Alasan Penolakan (Wajib diisi):</label>
                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Stok barang di gudang pusat kosong..."></textarea>
                                </div>
                                <button type="submit" name="status" value="ditolak" class="btn btn-danger btn-sm">
                                    Konfirmasi Tolak
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-secondary text-center mb-0">
                        <i class="fas fa-lock"></i> Pengajuan ini sudah selesai diproses.
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('js')
<script>
    $(document).ready(function() {
        
        // --- LOGIKA FILTER TABEL ---
        function applyFilter() {
            let kitchen = $('#filterKitchen').val().toLowerCase();
            let status = $('#filterStatus').val().toLowerCase();
            let date = $('#filterDate').val();

            $('#tableApproval tbody tr').each(function () {
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
    });
</script>
@endsection