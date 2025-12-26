@extends('adminlte::page')

@section('title', 'Detail Submission')

@section('content_header')
    <h1>Detail Pengajuan Menu</h1>
@endsection

@section('content')

    <a href="{{ route('transaction.submission.index') }}" class="btn btn-secondary mb-3">
        ← Kembali
    </a>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Bahan Baku</th>
                        <th>Qty Digunakan</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submission->details as $detail)
                        <tr>
                            <td>{{ $detail->recipe?->bahan_baku?->nama ?? '-' }}</td>
                            <td>{{ $detail->qty_digunakan }}</td>
                            <td>Rp {{ number_format($detail->harga_satuan_saat_itu) }}</td>
                            <td>Rp {{ number_format($detail->subtotal_harga) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Tidak ada detail bahan
                            </td>
                        </tr>
                    @endforelse

                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total</th>
                        <th>Rp {{ number_format($submission->total_harga) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

@endsection