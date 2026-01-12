@extends('adminlte::page')

@section('title', 'Laporan Pengajuan Menu')

@section('content_header')
    <h1>Laporan Pengajuan Menu</h1>
@endsection

@section('content')
    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        {{-- FILTER TANGGAL "DARI" --}}
                        <div class="col-md-4">
                            <label>Dari</label>
                            <input type="date" class="form-control ">
                        </div>

                        {{-- FILTER MENU "SAMPAI"--}}
                        <div class="col-md-4">
                            <label>Sampai</label>
                            <input type="date" class="form-control ">
                        </div>

                        {{-- FILTER DAPUR --}}
                        <div class="col-md-4">
                            <label>Dapur</label>
                            <select id="filterKitchen" class="form-control">
                                <option value="">Semua Dapur</option>
                                {{-- @foreach ($kitchens as $kitchen) --}}
                                    {{-- <option value="{{ $kitchen->nama }}">{{ $kitchen->nama }}</option> --}}
                                    <option value="">Dapur A</option>
                                    <option value="">Dapur B</option>
                                    <option value="">Dapur C</option>
                                {{-- @endforeach --}}
                            </select>
                        </div>
                    </div>
                </div>
            </div>

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