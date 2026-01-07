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
<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped" id="tableSubmission">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Jml Item</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th width="230" class="text-center">Aksi</th>
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
                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-{{
                            $item->status === 'diterima' ? 'success' :
                            ($item->status === 'ditolak' ? 'danger' : 'warning')
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
                        @if($item->status !== 'diterima')
                        <x-button-delete
                            idTarget="#modalDeleteOperational"
                            formId="formDeleteOperational"
                            action="{{ route('transaction.operational-submission.destroy', $item->id) }}"
                            text="Hapus"
                        />
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
            <div class="col-md-2 font-weight-bold">Harga</div>
            <div class="col-md-5 font-weight-bold">Keterangan</div>
            <div class="col-md-1"></div>
        </div>

            <div id="operasional-wrapper">
                <div class="form-row mb-3 operasional-group">
                    <div class="col-md-3">
                        <select name="items[0][barang_id]" class="form-control" required>
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
                        <input type="number" name="items[0][qty]" class="form-control" min="1" required />
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="items[0][harga_satuan]"class="form-control harga-input"required/>
                    </div>
                    
                    <div class="col-md-5">
                        <textarea name="items[0][keterangan]"
                            class="form-control"
                            rows="1"
                            placeholder="Contoh: untuk gas dapur / perbaikan alat">
                        </textarea>
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
    <table class="table table-borderless">
        <tr>
            <th width="140">Kode</th>
            <td>: {{ $item->kode }}</td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td>: {{ $item->created_at->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Dapur</th>
            <td>: {{ $item->kitchen->nama ?? '-' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge badge-{{
                    $item->status === 'diterima' ? 'success' :
                    ($item->status === 'ditolak' ? 'danger' : 'warning')
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
    </table>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Barang</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th>Keterangan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($item->details as $det)
                <tr>
                    <td>{{ $det->operational->nama ?? '-' }}</td>
                    <td class="text-center">{{ $det->qty }}</td>
                    <td class="text-right">Rp {{ number_format($det->harga_satuan,0,',','.') }}</td>
                    <td>{{ $det->keterangan ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($det->subtotal,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
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

        let index = 1;

    // ADD BARANG
    $('#add-operasional').on('click', function () {
        let $wrapper = $('#operasional-wrapper');
        let $firstRow = $wrapper.find('.operasional-group:first');
        let $newRow = $firstRow.clone();

        $newRow.find('select, input, textarea').each(function () {
            let name = $(this).attr('name');
            if (name) {
                name = name.replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', name).val('');
            }
        });

        $newRow.find('.remove-operasional').removeClass('d-none');

        $wrapper.append($newRow);

        let kitchenKode = $('#selectKitchen').val();
        if (kitchenKode) {
            filterBarangByKitchen(kitchenKode, false);
        }

        index++;
    });


        // REMOVE BARANG
        $(document).on('click', '.remove-operasional', function () {
            $(this).closest('.operasional-group').remove();
        });

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

        // Fungsi Hapus Baris
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });


        $(document).on('change', 'select[name*="[barang_id]"]', function () {
            let harga = $(this).find(':selected').data('harga') || 0;
            let row = $(this).closest('.operasional-group');

            row.find('.harga-input').val(harga);
        });

        function filterBarangByKitchen(kitchenKode) {
            $('#operasional-wrapper select[name*="[barang_id]"]').each(function () {
                let select = $(this);

                select.find('option').each(function () {
                    let optKitchen = $(this).data('kitchen');

                    // option default
                    if (!optKitchen) {
                        $(this).show();
                        return;
                    }

                    if (optKitchen === kitchenKode) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // reset pilihan barang
               if (reset) {
                    select.val('');
                }
            });
        }


        // Event saat dapur dipilih
        $('#selectKitchen').on('change', function () {
            let kitchenKode = $(this).val();
            filterBarangByKitchen(kitchenKode, true);
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