@extends('adminlte::page')

@section('title', 'Laporan Pengajuan Menu')

@section('content_header')
    <h1>Laporan Pengajuan Menu</h1>
@endsection

@section('content')
    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Menu</th>
                        <th>Porsi</th>
                        <th>Dapur</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <button 
                                type="button"
                                class="btn btn-sm btn-primary"
                                data-toggle="modal"
                                data-target="#modalDetailSubmission"
                            >
                                Detail
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <x-modal-detail
        id="modalDetailSubmission"
        size="modal-lg"
        title="Detail Pengajuan Menu"
    >
        <div>
            <div>
                <p class="font-weight-bold mb-0">Tanggal:</p>
                <p>Rabu, 10 Desember 2025</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Nama Menu:</p>
                <p>Nasi Goreng</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Porsi:</p>
                <p>1000</p>
            </div>
            <div>
                <p class="font-weight-bold mb-0">Dapur:</p>
                <p>Dapur A Tembalang</p>
            </div>
            <div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bawang Merah</td>
                            <td>10 kg</td>
                        </tr>
                        <tr>
                            <td>Bawang Putih</td>
                            <td>10 kg</td>
                        </tr>
                        <tr>
                            <td>Beras</td>
                            <td>100 kg</td>
                        </tr>
                        <tr>
                            <td>Kecap</td>
                            <td>15 L</td>
                        </tr>
                        <tr>
                            <td>Minyak Goreng</td>
                            <td>20 L</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal-detail>
@endsection