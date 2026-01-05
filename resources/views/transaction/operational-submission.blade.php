@extends('adminlte::page')

@section('title', 'Pengajuan Operasional')

@section('content_header')
    <h1>Pengajuan Operasional (Statis)</h1>
@endsection

@section('content')

@php
    // =========================
    // DATA STATIS
    // =========================
    $submissions = collect([
        (object)[
            'id' => 1,
            'kode' => 'OPR001',
            'tanggal' => '2026-01-05',
            'dapur' => 'Dapur Pusat',
            'operasional' => 'Gas LPG',
            'total' => 150000,
            'status' => 'diajukan',
        ],
        (object)[
            'id' => 2,
            'kode' => 'OPR002',
            'tanggal' => '2026-01-06',
            'dapur' => 'Dapur Cabang',
            'operasional' => 'Listrik',
            'total' => 300000,
            'status' => 'diproses',
        ],
    ]);

    $items = [
        ['nama' => 'Gas 12 Kg', 'unit' => 'Tabung', 'harga' => 150000],
        ['nama' => 'Token Listrik', 'unit' => 'kWh', 'harga' => 300000],
    ];
@endphp

{{-- BUTTON ADD --}}
<button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalAdd">
    + Tambah Pengajuan
</button>

<div class="card">
    <div class="card-body">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Dapur</th>
                    <th>Operasional</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $s)
                <tr>
                    <td>{{ $s->kode }}</td>
                    <td>{{ $s->tanggal }}</td>
                    <td>{{ $s->dapur }}</td>
                    <td>{{ $s->operasional }}</td>
                    <td>Rp {{ number_format($s->total) }}</td>
                    <td>
                        <span class="badge badge-{{
                            $s->status === 'diajukan' ? 'warning' :
                            ($s->status === 'diproses' ? 'info' : 'success')
                        }}">
                            {{ strtoupper($s->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modalDetail{{ $s->id }}">
                            Detail
                        </button>
                        <button class="btn btn-danger btn-sm">
                            Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

{{-- =========================
MODAL TAMBAH
========================= --}}
<div class="modal fade" id="modalAdd">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengajuan Operasional</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label>Dapur</label>
                    <input class="form-control" value="Dapur Pusat">
                </div>

                <div class="form-group">
                    <label>Operasional</label>
                    <input class="form-control" value="Gas LPG">
                </div>

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
                        <tr>
                            <td>Gas 12 Kg</td>
                            <td>1</td>
                            <td>Tabung</td>
                            <td>150.000</td>
                            <td>150.000</td>
                        </tr>
                    </tbody>
                </table>

                <h5 class="text-right">
                    Total: Rp 150.000
                </h5>

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- =========================
MODAL DETAIL
========================= --}}
@foreach($submissions as $s)
<div class="modal fade" id="modalDetail{{ $s->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail {{ $s->kode }}</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

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
                        <tr>
                            <td>Gas 12 Kg</td>
                            <td>1</td>
                            <td>Tabung</td>
                            <td>150.000</td>
                            <td>150.000</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
