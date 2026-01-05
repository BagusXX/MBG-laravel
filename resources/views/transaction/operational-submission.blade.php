@extends('adminlte::page')

@section('title', $mode === 'pengajuan' ? 'Pengajuan Operasional' : 'Permintaan Operasional')

@section('css')
<link rel="stylesheet" href="{{ asset('css/notification-pop-up.css') }}">
@endsection

@section('content_header')
<h1>{{ $mode === 'pengajuan' ? 'Pengajuan Operasional' : 'Permintaan Operasional' }}</h1>
@endsection

@section('content')

@if($mode === 'pengajuan')
    <x-button-add idTarget="#modalAddOperational" text="Tambah Pengajuan Operasional"/>
@endif

<x-notification-pop-up/>

<div class="card">
    <div class="card-body">

        {{-- FILTER --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">Semua</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="diproses">Diproses</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Operasional</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="220">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr data-status="{{ $item->status }}">
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->created_at->format('d-m-Y') }}</td>
                    <td>{{ $item->kitchen->nama }}</td>
                    <td>{{ $item->operasional->nama }}</td>
                    <td>Rp {{ number_format($item->total_harga) }}</td>
                    <td>
                        <span class="badge badge-{{
                            $item->status === 'diterima' ? 'success' :
                            ($item->status === 'ditolak' ? 'danger' :
                            ($item->status === 'diproses' ? 'info' : 'warning'))
                        }}">
                            {{ strtoupper($item->status) }}
                        </span>
                    </td>
                    <td>
                        {{-- DETAIL --}}
                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modalDetail{{ $item->id }}">
                            Detail
                        </button>

                        {{-- EDIT STATUS (KOPERASI) --}}
                        @can('transaction.operational-submission.update')
                        @if($mode === 'permintaan' && $item->status !== 'diterima')
                        <button class="btn btn-warning btn-sm btnEdit"
                            data-action="{{ route('transaction.operational-submission.update', $item->id) }}"
                            data-status="{{ $item->status }}"
                            data-toggle="modal"
                            data-target="#modalEditStatus">
                            Edit
                        </button>
                        @endif
                        @endcan

                        {{-- DELETE --}}
                        @can('transaction.operational-submission.delete')
                        @if($item->status === 'diajukan')
                        <x-button-delete
                            idTarget="#modalDelete"
                            formId="formDelete"
                            action="{{ route('transaction.operational-submission.destroy', $item->id) }}"
                            text="Hapus"/>
                        @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $submissions->links('pagination::bootstrap-4') }}
    </div>
</div>

{{-- =========================
MODAL TAMBAH PENGAJUAN
========================= --}}
@if($mode === 'pengajuan')
<x-modal-form id="modalAddOperational"
    title="Tambah Pengajuan Operasional"
    action="{{ route('transaction.operational-submission.store') }}"
    submitText="Simpan">

    <div class="form-group">
        <label>Kode</label>
        <input class="form-control" value="{{ $nextKode }}" readonly>
    </div>

    <div class="form-group">
        <label>Dapur</label>
        <select name="kitchen_id" class="form-control" required>
            @foreach($kitchens as $k)
            <option value="{{ $k->id }}">{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Operasional</label>
        <select name="operasional_id" class="form-control" required>
            @foreach($operationals as $o)
            <option value="{{ $o->id }}">{{ $o->nama }}</option>
            @endforeach
        </select>
    </div>

    <hr>

    <table class="table table-bordered" id="tableItem">
        <thead>
            <tr>
                <th>Barang</th>
                <th width="90">Qty</th>
                <th>Satuan</th>
                <th width="130">Harga</th>
                <th width="130">Subtotal</th>
                <th width="60"></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button type="button" class="btn btn-sm btn-success" id="btnAddItem">
        + Tambah Barang
    </button>

    <h5 class="text-right mt-3">
        Total: Rp <span id="grandTotal">0</span>
    </h5>

</x-modal-form>
@endif

{{-- =========================
MODAL EDIT STATUS
========================= --}}
<x-modal-form id="modalEditStatus" title="Update Status" action="" submitText="Update">
    @method('PUT')
    <div class="form-group">
        <label>Status</label>
        <select name="status" id="editStatus" class="form-control">
            <option value="diajukan">Diajukan</option>
            <option value="diproses">Diproses</option>
            <option value="diterima">Diterima</option>
            <option value="ditolak">Ditolak</option>
        </select>
    </div>
</x-modal-form>

<x-modal-delete id="modalDelete" formId="formDelete"
    title="Hapus Pengajuan"
    message="Yakin ingin menghapus pengajuan ini?"
    confirmText="Hapus"/>

{{-- =========================
MODAL DETAIL
========================= --}}
@foreach($submissions as $s)
<x-modal-detail id="modalDetail{{ $s->id }}" size="modal-lg" title="Detail Operasional">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Barang</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($s->details as $d)
            <tr>
                <td>{{ $d->barang->nama }}</td>
                <td>{{ $d->qty }}</td>
                <td>{{ $d->barang->unit->satuan }}</td>
                <td>Rp {{ number_format($d->harga_satuan) }}</td>
                <td>Rp {{ number_format($d->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-modal-detail>
@endforeach

@endsection

@push('js')
<script>
let items = @json($items);

/* =========================
ADD ROW
========================= */
function addRow() {
    let row = `
    <tr>
        <td>
            <select name="items[][barang_id]" class="form-control barang" required>
                <option value="">Pilih</option>
                ${items.map(i =>
                    `<option value="${i.id}" data-unit="${i.unit.satuan}" data-harga="${i.harga_default}">
                        ${i.nama}
                    </option>`).join('')}
            </select>
        </td>
        <td><input type="number" step="0.01" name="items[][qty]" class="form-control qty" required></td>
        <td class="unit text-center">-</td>
        <td><input type="number" step="0.01" name="items[][harga]" class="form-control harga" required></td>
        <td class="subtotal text-right">0</td>
        <td><button type="button" class="btn btn-danger btn-sm btnRemove">X</button></td>
    </tr>`;
    $('#tableItem tbody').append(row);
}

/* =========================
EVENT
========================= */
$('#btnAddItem').click(addRow);

$(document).on('change', '.barang', function () {
    let opt = $(this).find(':selected');
    let row = $(this).closest('tr');

    row.find('.unit').text(opt.data('unit'));
    row.find('.harga').val(opt.data('harga'));
});

$(document).on('input', '.qty, .harga', function () {
    let row = $(this).closest('tr');
    let qty = parseFloat(row.find('.qty').val()) || 0;
    let harga = parseFloat(row.find('.harga').val()) || 0;

    let subtotal = qty * harga;
    row.find('.subtotal').text(subtotal.toLocaleString());

    let total = 0;
    $('.subtotal').each(function () {
        total += parseFloat($(this).text().replaceAll(',', '')) || 0;
    });
    $('#grandTotal').text(total.toLocaleString());
});

$(document).on('click', '.btnRemove', function () {
    $(this).closest('tr').remove();
    $('.qty:first').trigger('input');
});

/* =========================
EDIT STATUS
========================= */
$('.btnEdit').click(function () {
    $('#modalEditStatus form').attr('action', $(this).data('action'));
    $('#editStatus').val($(this).data('status'));
});

/* =========================
FILTER
========================= */
$('#filterStatus').change(function () {
    let status = $(this).val();
    $('tbody tr').each(function () {
        let rowStatus = $(this).data('status');
        $(this).toggle(!status || status === rowStatus);
    });
});
</script>
@endpush
