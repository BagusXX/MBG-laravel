@extends('adminlte::page')

@section('title', 'Detail Submission')

@section('content_header')
    <h1>Detail Pengajuan Menu</h1>
@endsection

@section('content')

    <a href="{{ route('transaction.submission.index') }}" class="btn btn-secondary mb-3">
        ← Kembali
    </a>


    <div class="card mb-3">
        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <th width="180">Kode</th>
                    <td>: {{ $submission->kode }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>: {{ date('d-m-Y', strtotime($submission->tanggal)) }}</td>
                </tr>
                <tr>
                    <th>Dapur</th>
                    <td>: {{ $submission->kitchen->nama }}</td>
                </tr>
                <tr>
                    <th>Menu</th>
                    <td>: {{ $submission->menu->nama }}</td>
                </tr>
                <tr>
                    <th>Porsi</th>
                    <td>: {{ $submission->porsi }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Bahan Baku</th>
                        <th class="text-center">Qty Digunakan</th>
                        <th class="text-center">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submission->details as $detail)
                        <tr>
                            <td>{{ $detail->recipe?->bahan_baku?->nama ?? '-' }}</td>
                            <td class="text-center">{{ $detail->qty_digunakan }}</td>
                            <td class="text-center">
                                {{ $detail->recipe?->bahan_baku?->unit?->satuan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Tidak ada detail bahan baku
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


@endsection