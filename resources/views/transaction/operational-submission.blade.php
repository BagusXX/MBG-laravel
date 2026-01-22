@extends('adminlte::page')

@section('title', 'Pengajuan Operasional')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
    <h1>Pengajuan Operasional</h1>
@endsection

@section('content')

<div id="notification-container"></div>


{{-- BUTTON ADD --}}
<x-button-add
    idTarget="#modalAddOperational"
    text="Tambah Pengajuan Operasional"
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
                    <th>Jml Item</th>
                    {{-- <th>Total Biaya</th> --}}
                    <th>Status</th>
                    <th width="180" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr 
                    data-kitchen="{{ $item->kitchen->nama ?? '' }}"
                    data-status="{{ $item->status }}"
                    data-date="{{ $item->created_at->format('Y-m-d') }}"
                >
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->created_at->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama ?? '-' }}</td>
                    <td>{{ $item->details->count() }} Item</td>
                    {{-- <td>Rp {{ number_format($item->total_harga, 2, ',', '.') }}</td> --}}
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
                            Detail
                        </button>

                        {{-- Tombol Hapus (Hanya jika belum diterima) --}}
                        @if(
                        $item->status !== 'diproses' &&
                        $item->status !== 'diterima' &&
                        $item->children->count() === 0)
                        <x-button-delete
                            idTarget="#modalDeleteOperational"
                            formId="formDeleteOperational"
                            action="{{ route('transaction.operational-submission.destroy', $item->id) }}"
                            text="Hapus"
                        />
                        @endif
                        @if($item->status === 'selesai')
                            <a href="{{ route('transaction.operational-submission.invoice-parent', $item->id) }}"
                            target="_blank"
                            class="btn btn-warning btn-sm"
                            title="Cetak Invoice Rekapitulasi">
                                <i class="fas fa-print"></i> Cetak
                            </a>
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


<x-modal-form 
    id="modalAddOperational"
    size="modal-xl"
    title="Tambah Pengajuan Operasional"
    action="{{ route('transaction.operational-submission.store') }}"
    submitText="Simpan Pengajuan"
>
    @csrf

    <div class="form-group">
        <label>Kode</label>
        <input 
            type="text"
            class="form-control"
            value="(Otomatis dibuat setelah disimpan)"
            readonly
            style="background:#e9ecef"
        >
    </div>

    <div class="form-group">
        <label>Tanggal</label>
        <input type="date"
            name="tanggal"
            class="form-control"
            value="{{ old('tanggal', now()->format('Y-m-d')) }}"
            required>
    </div>


    <div class="form-group">
        <label>Dapur</label>
        <select name="kitchen_kode" id="selectKitchen" class="form-control" required>
            <option disabled selected>Pilih Dapur</option>
            @foreach($kitchens as $k)
                <option value="{{ $k->kode }}">{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>


    {{-- Tabel input barang --}}
    <div class="form-group">
        <div class="form-row mb-2">
            <div class="col-md-3 font-weight-bold">Barang Operasional</div>
            <div class="col-md-1 font-weight-bold">Qty</div>
            {{-- <div class="col-md-2 font-weight-bold">Harga</div> --}}
            <div class="col-md-5 font-weight-bold">Keterangan</div>
            <div class="col-md-1"></div>
        </div>

            <div id="operasional-wrapper">
                <div class="form-row mb-3 operasional-group">
                    <div class="col-md-3">
                        <select name="items[0][barang_id]" class="form-control"required>
                            <option value="" disabled selected>Pilih Barang</option>
                            @foreach ($masterBarang as $barang)
                                <option 
                                    value="{{ $barang->id }}"
                                    data-kitchen="{{ $barang->kitchen_kode }}"
                                    data-harga="{{ $barang->harga_default }}"
                                >
                                    {{ $barang->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <input type="number" name="items[0][qty]" class="form-control qty-input" min="1" required />
                    </div>

                    {{-- <div class="col-md-2">
                        <input type="number" step="0.01" name="items[0][harga_satuan]"class="form-control harga-input"required/>
                    </div> --}}
                    
                    <div class="col-md-5">
                        <textarea name="items[0][keterangan]"
                            class="form-control"
                            rows="1"
                            placeholder="Contoh: untuk gas dapur / perbaikan alat"></textarea>
                    </div>

                    <div class="col-md-1">
                        <button type="button"
                            class="btn btn-outline-danger btn-sm remove-operasional d-none h-100"
                            style="width:35px">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-operasional"
                class="btn btn-outline-primary btn-block mt-2">
                <i class="fas fa-plus mr-1"></i>Tambah Barang Operasional
            </button>
        </div>
</x-modal-form>


{{-- =========================
    MODAL DETAIL (LOOPING)
========================= --}}
@foreach($submissions as $item)
<x-modal-detail 
    id="modalDetail{{ $item->id }}"
    size="modal-lg"
    title="Detail Pengajuan Operasional"
>
    <div class="row mb-3">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th width="140" class="py-1">Kode</th>
                    <td class="py-1">: {{ $item->kode }}</td>
                </tr>
                <tr>
                    <th width="140" class="py-1">Tanggal</th>
                    <td class="py-1">: {{ $item->created_at->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <th width="140" class="py-1">Dapur</th>
                    <td class="py-1">: {{ $item->kitchen->nama ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table>
                <tr>
                    <th width="140" class="py-1">Status</th>
                    <td class="py-1">
                        <span class="badge badge-{{
                            $item->status === 'diterima' ? 'success' :
                            ($item->status === 'selesai' ? 'success' :
                            ($item->status === 'diproses' ? 'info' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning')))
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    @if ($item->status === 'ditolak' && $item->keterangan)
                        <div class="mt-2 p-2 border rounded bg-light">
                            <large class="text-danger font-weight-bold">
                                Alasan Penolakan:
                            </large>
                            <div class="text-strong">
                                {{ $item->keterangan }}
                            </div>
                        </div>
                    @endif
                </tr>
                <!-- <tr>
                    <th width="140" class="py-1">Supplier</th>
                    <td class="py-1">
                        : {{ $item->supplier->nama ?? '-' }}
                    </td>
                </tr> -->

                <tr>
                    <th width="140" class="py-1">Total Biaya</th>
                    <td class="py-1">
                        : Rp {{ number_format($item->total_harga, 2, ',', '.') }}
                    </td>
                </tr>

            </table>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Barang</th>
                <th class="text-center">Jumlah</th>
                <th class="text-right">Harga</th>
                <th>Keterangan</th>
                {{-- <th class="text-right">Subtotal</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($item->details as $det)
                <tr>
                    <td>{{ $det->operational->nama ?? '-' }}</td>
                    <td class="text-center">{{ $det->qty }}</td>
                    <td class="text-right">Rp {{ number_format($det->harga_satuan,2,',','.') }}</td>
                    <td>{{ $det->keterangan ?? '-' }}</td>
                    {{-- <td class="text-right">Rp {{ number_format($det->subtotal,2,',','.') }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- TAMBAHKAN BAGIAN INI: RIWAYAT APPROVAL / SUPPLIER --}}
    @if($item->children->count() > 0)
        <div class="mt-4">
            <h6 class="font-weight-bold text-secondary border-bottom pb-2">Rincian per Supplier (Split Order)</h6>
            
            @foreach($item->children as $child)
            <div class="card card-body bg-light p-3 mb-2 border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>{{ $child->kode }}</strong> 
                        <span class="text-muted mx-2">|</span> 
                        <i class="fas fa-truck"></i> {{ $child->supplier->nama ?? 'Tanpa Supplier' }}
                    </div>
                    <div>
                        <span class="badge badge-{{ $child->status == 'disetujui' ? 'success' : 'secondary' }}">
                            {{ strtoupper($child->status) }}
                        </span>
                        <span class="ml-2 font-weight-bold">
                            Rp {{ number_format($child->total_harga, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- List Barang Child --}}
                <ul class="mb-0 pl-3" style="font-size: 0.9em;">
                    @foreach($child->details as $cDetail)
                        <li>
                            {{ $cDetail->operational->nama ?? '-' }} 
                            ({{ $cDetail->qty }} x {{ number_format($cDetail->harga_satuan) }})
                        </li>
                    @endforeach
                </ul>

                {{-- Tombol Invoice Satuan (Child) --}}
                <div class="text-right mt-2">
                    @if($child->status === 'disetujui')
                        <a href="{{ route('transaction.operational-submission.invoice', $child->id) }}" 
                           class="btn btn-xs btn-outline-secondary" 
                           target="_blank">
                            <i class="fas fa-print"></i> Cetak Invoice Supplier
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-modal-detail>

@endforeach

{{-- MODAL KONFIRMASI DELETE --}}
<x-modal-delete 
    id="modalDeleteOperational"
    formId="formDeleteOperational"
    title="Konfirmasi Hapus" 
    message="Apakah Anda yakin ingin menghapus pengajuan operasional ini?" 
    confirmText="Hapus" 
/>


@endsection

@section('js') {{-- Menggunakan section js, sesuaikan jika Anda pakai push('js') --}}
<script>
    function showNotification(type, message) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    const notif = document.createElement('div');
    notif.className = `notification ${type} show`;
    notif.innerText = message;

    container.appendChild(notif);

    setTimeout(() => {
        notif.classList.remove('show');
        notif.remove();
    }, 3000);
}

    $(document).ready(function() {
    
    // Hitung jumlah baris yang ada saat ini untuk menentukan index selanjutnya
    // Kita mulai dari 1 karena index 0 sudah ada di HTML (form bawaan)
    let itemIndex = 1;

    // --- LOGIC TAMBAH BARANG ---
    $('#add-operasional').on('click', function () {
        let $wrapper = $('#operasional-wrapper');
        let $firstRow = $wrapper.find('.operasional-group:first');
        
        // 1. Clone baris pertama
        let $newRow = $firstRow.clone();

        // 2. Reset nilai input di baris baru agar kosong
        $newRow.find('select, input, textarea').val('');
        
        // 3. Tampilkan tombol hapus (karena di baris pertama tombol ini hidden)
        $newRow.find('.remove-operasional').removeClass('d-none');

        // ============================================================
        // BAGIAN PENTING: UPDATE ATRIBUT NAME
        // Mengubah items[0][...] menjadi items[1][...], items[2][...], dst
        // ============================================================
        $newRow.find('select, input, textarea').each(function() {
            let oldName = $(this).attr('name'); 
            if (oldName) {
                // Regex ini mencari angka di dalam kurung siku [0] dan menggantinya dengan index baru
                let newName = oldName.replace(/\[\d+\]/, '[' + itemIndex + ']');
                $(this).attr('name', newName);
            }
        });

        // 4. Masukkan baris baru ke tampilan
        $wrapper.append($newRow);

        // 5. Jalankan filter dapur untuk baris baru (agar pilihan barang sesuai dapur)
        let kitchenKode = $('#selectKitchen').val();
        if (kitchenKode) {
            // Terapkan filter hanya pada baris baru ini
            let newSelect = $newRow.find('select[name*="[barang_id]"]');
            filterSingleSelect(newSelect, kitchenKode);
        }

        // 6. Naikkan counter index untuk baris berikutnya
        itemIndex++;
    });


    // --- LOGIC HAPUS BARANG ---
    $(document).on('click', '.remove-operasional', function () {
        $(this).closest('.operasional-group').remove();
    });


    // --- LOGIC FILTER DAPUR (HELPER) ---
    // Fungsi ini memfilter satu dropdown spesifik
    function filterSingleSelect($selectElement, kitchenKode) {
        $selectElement.find('option').each(function () {
            let optKitchen = $(this).data('kitchen');
            // Tampilkan jika tidak ada data-kitchen (option default) atau cocok
            if (!optKitchen || optKitchen == kitchenKode) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Fungsi untuk memfilter SEMUA dropdown (dipakai saat ganti dapur utama)
    function filterAllBarangByKitchen(kitchenKode) {
        $('#operasional-wrapper select[name*="[barang_id]"]').each(function () {
            filterSingleSelect($(this), kitchenKode);
            $(this).val(''); // Reset pilihan saat ganti dapur
        });
    }

    // Event saat Dapur dipilih (Header Form)
    $('#selectKitchen').on('change', function () {
        let kitchenKode = $(this).val();
        filterAllBarangByKitchen(kitchenKode);
    });

    // Event saat Barang dipilih (Auto isi Harga)
    $(document).on('change', 'select[name*="[barang_id]"]', function () {
        let harga = $(this).find(':selected').data('harga') || 0;
        let row = $(this).closest('.operasional-group');
        row.find('.harga-input').val(harga);
    });

    // Reset Form saat modal ditutup
    $('#modalAddOperational').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        // Hapus baris tambahan, sisakan baris pertama saja
        $('#operasional-wrapper .operasional-group:not(:first)').remove();
        itemIndex = 1; // Reset index kembali ke 1
    });

    // Helper Filter Tampilan Tabel Utama (Search)
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