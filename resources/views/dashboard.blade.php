@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold">Dashboard Analitik</h1>
            <p class="text-muted">Ringkasan data operasional dapur dan pengajuan.</p>
        </div>
    </div>
</div>
@stop

@section('content')

{{-- ================= SUMMARY CARDS ================= --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box shadow-sm border-0 bg-white">
            <div class="inner">
                <h3 class="text-info">{{ $summary['total_kitchen'] }}</h3>
                <p class="text-muted font-weight-bold">Total Dapur</p>
            </div>
            <div class="icon">
                <i class="fas fa-fire-alt text-info opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box shadow-sm border-0 bg-white">
            <div class="inner">
                <h3 class="text-warning">{{ $summary['diajukan'] }}</h3>
                <p class="text-muted font-weight-bold">Pengajuan Diajukan</p>
            </div>
            <div class="icon">
                <i class="fas fa-paper-plane text-warning opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box shadow-sm border-0 bg-white">
            <div class="inner">
                <h3 class="text-primary">{{ $summary['diproses'] }}</h3>
                <p class="text-muted font-weight-bold">Pengajuan Diproses</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock text-primary opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box shadow-sm border-0 bg-white">
            <div class="inner">
                <h3 class="text-success">{{ $summary['selesai'] }}</h3>
                <p class="text-muted font-weight-bold">Pengajuan Selesai</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle text-success opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- ================= CHART MONTHLY ================= --}}
    <div class="col-md-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white border-0">
                <h3 class="card-title font-weight-bold text-muted">
                    <i class="fas fa-chart-line mr-1"></i> Pengajuan Per Bulan ({{ now()->year }})
                </h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="monthlyChart"
                        style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= CHART STATUS ================= --}}
    <div class="col-md-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-white border-0">
                <h3 class="card-title font-weight-bold text-muted">
                    <i class="fas fa-chart-pie mr-1"></i> Distribusi Status
                </h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart"
                    style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

@role('superadmin')
{{-- ================= TOP KITCHEN REPORT ================= --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom-0">
        <h3 class="card-title font-weight-bold">
            🏆 Laporan Pengajuan Dapur <span class="text-muted small ml-1">(Nominal Terbesar)</span>
        </h3>
        <div class="card-tools">
            <form action="{{ url()->current() }}" method="GET" id="filterForm">
                <select name="month" class="form-control form-control-sm shadow-sm" onchange="this.form.submit()">
                    <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>📅 Semua Bulan</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                @if(request('kitchen_id'))
                    <input type="hidden" name="kitchen_id" value="{{ request('kitchen_id') }}">
                @endif
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0" style="width: 80px">Ranking</th>
                    <th class="border-0">Nama Dapur</th>
                    <th class="border-0 text-center">Total Pengajuan</th>
                    <th class="border-0" style="width: 40%">Total Nominal & Alokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topKitchen ?? [] as $index => $data)
                    @php
                        $totalAll = $topKitchen->sum('total_nominal');
                        $percentage = $totalAll > 0 ? ($data->total_nominal / $totalAll) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="text-center font-weight-bold">
                            @if($index == 0) <span class="badge badge-warning py-2 px-3">🥇 1</span>
                            @elseif($index == 1) <span class="badge badge-secondary py-2 px-3">🥈 2</span>
                            @elseif($index == 2) <span class="badge badge-light border py-2 px-3">🥉 3</span>
                            @else {{ $index + 1 }} @endif
                        </td>
                        <td class="font-weight-bold text-dark">{{ $data->kitchen->nama ?? '-' }}</td>
                        <td class="text-center"><span
                                class="badge badge-light border px-3">{{ $data->total_submission }}</span></td>
                        <td>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-weight-bold text-success">Rp
                                    {{ number_format($data->total_nominal, 0, ',', '.') }}</span>
                                <small class="text-muted font-weight-bold">{{ round($percentage, 1) }}%</small>
                            </div>
                            <div class="progress progress-xxs shadow-sm">
                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endrole

<!-- <div class="row"> -->
    {{-- ================= TOP BAHAN BAKU ================= --}}
    <div class="col-md-12">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h3 class="card-title font-weight-bold mb-0">📦 Top 10 Bahan Baku</h3>
                
                <div class="d-flex" style="gap: 8px;">
                    <select id="filterMonthBahan" class="form-control form-control-sm" style="width: auto;">
                        <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                            </option>
                        @endforeach
                    </select>

                    @php
                        $currentUser = auth()->user();
                    @endphp

                    <select id="filterKitchenBahan" class="form-control form-control-sm" style="width: auto;">
                        @if($currentUser->hasRole('superadmin'))
                            <option value="all" {{ request('kitchen') == 'all' ? 'selected' : '' }}>Semua Dapur</option>
                        @endif

                        @foreach($allKitchens as $k)
                            @if($currentUser->hasRole('superadmin') || in_array($k->id, $userKitchenIds))
                                <option value="{{ $k->id }}" {{ request('kitchen') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div style="min-height: 400px; position: relative;">
                    <canvas id="bahanBakuChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @role('superadmin')
    {{-- ================= ACTIVITY LOG (FULL WIDTH - 2 COLUMNS) ================= --}}
    <div class="col-md-12 mt-4" style="margin-bottom: 50px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">⚡ Aktivitas Terbaru</h3>
            </div>
            <div class="card-body p-0">
                @if($recentActivity->count() > 0)
                    <div class="row no-gutters">
                        {{-- Bagi data menjadi 2 kolom (misal: total 10 data, jadi 5 di kiri, 5 di kanan) --}}
                        @foreach($recentActivity->chunk(ceil($recentActivity->count() / 2)) as $chunk)
                            <div class="col-md-6 border-right" style="padding: 0 60px 0 60px">
                                <div class="list-group list-group-flush">
                                    @foreach($chunk as $activity)
                                        <div class="list-group-item border-0 border-bottom py-3">
                                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 font-weight-bold text-dark">
                                                    <i class="fas fa-user-circle text-primary mr-2"></i>
                                                    {{ $activity->causer->name ?? 'System' }}
                                                </h6>
                                                <small class="text-muted bg-light px-2 py-1 rounded">
                                                    <i class="far fa-clock mr-1"></i>{{ $activity->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <p class="mb-0 text-muted">
                                                {{ $activity->description }}
                                            </p>
                                            
                                            <div class="mt-2">
                                                @if($activity->properties && isset($activity->properties['attributes']))
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small font-weight-bold text-uppercase text-muted">Detail:</span>
                                                        <button class="btn btn-xs btn-outline-primary" type="button" data-toggle="collapse"
                                                            data-target="#details-{{ $activity->id }}" aria-expanded="false">
                                                            <i class="fas fa-eye mr-1"></i> Lihat
                                                        </button>
                                                    </div>

                                                    <div class="collapse mt-2" id="details-{{ $activity->id }}">
                                                        <div class="bg-light p-2 rounded border">
                                                            <ul class="mb-0 small text-dark list-unstyled">
                                                                @foreach($activity->properties['attributes'] as $key => $value)
                                                                    <li class="mb-1 d-flex flex-wrap align-items-center">
                                                                        <strong class="text-capitalize mr-1">{{ str_replace('_', ' ', $key) }}:</strong>
                                                                        @if(isset($activity->properties['old'][$key]))
                                                                            <span class="badge badge-danger-light text-danger mx-1" style="text-decoration: line-through;">
                                                                                {{ $activity->properties['old'][$key] }}
                                                                            </span>
                                                                            <i class="fas fa-arrow-right mx-1 text-xs text-muted"></i>
                                                                        @endif
                                                                        <span class="text-success font-weight-bold ml-1">
                                                                            {{ is_array($value) ? json_encode($value) : $value }}
                                                                        </span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted small italic">Tidak ada rincian.</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                        <p>Belum ada aktivitas tercatat.</p>
                    </div>
                @endif
            </div>
            
            <!-- @if($recentActivity->count() > 0)
            <div class="card-footer bg-white text-center py-2">
                <a href="#" class="text-sm font-weight-bold text-primary text-uppercase">Lihat Log Audit Lengkap</a>
            </div>
            @endif -->
        </div>
    </div>
    @endrole
<!-- </div> -->


@stop

@section('css')
<style>
    .small-box {
        border-radius: 12px !important;
    }

    .card {
        border-radius: 12px !important;
    }

    .table thead th {
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .progress-xxs {
        height: 4px;
        border-radius: 10px;
    }

    .opacity-50 {
        opacity: 0.3;
    }

    .align-middle td {
        vertical-align: middle !important;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .badge-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
        padding: 2px 4px;
        border-radius: 4px;
    }

    .btn-xs {
        padding: 1px 5px;
        font-size: 0.7rem;
        line-height: 1.5;
        border-radius: 3px;
    }

    .collapse {
        transition: all 0.3s ease;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ================= MONTHLY CHART =================
    const monthlyData = @json($monthlyChart);
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const monthlyLabels = Object.keys(monthlyData).map(m => monthNames[m - 1]);
    const monthlyValues = Object.values(monthlyData);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Submission',
                data: monthlyValues,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#007bff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // ================= STATUS CHART =================
    const statusData = @json($statusChart);
    const colorMap = { 'DIAJUKAN': '#ffc107', 'DIPROSES': '#17a2b8', 'SELESAI': '#28a745' };

    const statusLabels = statusData.map(item => item.status.toUpperCase());
    const statusCounts = statusData.map(item => item.total_count);
    const statusRupiahs = statusData.map(item => item.total_rupiah);
    const dynamicColors = statusLabels.map(label => colorMap[label] || '#cccccc');

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                rupiahData: statusRupiahs,
                backgroundColor: dynamicColors,
                hoverOffset: 15,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let rupiah = new Intl.NumberFormat('id-ID', {
                                style: 'currency', currency: 'IDR', minimumFractionDigits: 0
                            }).format(context.dataset.rupiahData[context.dataIndex]);
                            return ` ${context.label}: ${context.parsed} Data (${rupiah})`;
                        }
                    }
                }
            }
        }
    });

    // ================= TOP BAHAN BAKU CHART =================
    // Data dari Laravel
    const bahanData = @json($topBahanBaku);

    // Fungsi untuk memecah string panjang menjadi array agar teks bertumpuk
    const wrapLabel = (label, maxLength = 6) => {
        const words = label.split(' ');
        const lines = [];
        let currentLine = '';

        words.forEach(word => {
                if (currentLine === '' && word.length > maxLength) {
                lines.push(word);
            } else if ((currentLine + word).length > maxLength) {
                lines.push(currentLine.trim());
                currentLine = word + ' ';
            } else {
                currentLine += word + ' ';
            }
        });
        if (currentLine.trim() !== '') lines.push(currentLine.trim());
        return lines;
    };

    const labels = bahanData.map(item => wrapLabel(item.nama_bahan));
    const values = bahanData.map(item => item.total_qty);

    
    
    // Kita gunakan Horizontal Bar agar nama bahan yang panjang lebih mudah dibaca
    new Chart(document.getElementById('bahanBakuChart'), {
        type: 'bar', 
        data: {
            labels: bahanData.map(item => item.nama_bahan),
            datasets: [{
                label: 'Total Qty',
                data: bahanData.map(item => item.total_qty), // Total Qty di Sumbu Y
                backgroundColor: '#007bff', // Warna ungu transparan
                borderColor: '#007bff',
                borderWidth: 1,
                borderRadius: 5,
                barThickness: 'flex', // Ukuran batang fleksibel mengikuti lebar wadah
                maxBarThickness: 50   // Batas maksimal lebar batang agar tidak terlalu gemuk
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // PENTING: Agar chart mengikuti tinggi container (350px)
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const item = bahanData[context.dataIndex];
                            return ` Qty: ${context.parsed.y} unit (${item.total_penggunaan}x digunakan)`;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    bottom: 20 // Memberi ruang tambahan di bawah untuk teks bertumpuk
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { borderDash: [5, 5], color: '#e9ecef' },
                    ticks: { stepSize: 5 }
                },
                x: { 
                grid: { display: false },
                ticks: {
                    autoSkip: false,
                    maxRotation: 0, // Paksa tetap horizontal
                    minRotation: 0,
                    font: { size: 12 },
                    padding: 6
                }
            }
            }
        }
    });

    
    // Handler Filter (Redirect otomatis saat dropdown diganti)
    document.getElementById('filterMonthBahan').addEventListener('change', refreshFilter);
    document.getElementById('filterKitchenBahan').addEventListener('change', refreshFilter);

    function refreshFilter() {
        const month = document.getElementById('filterMonthBahan').value;
        const kitchen = document.getElementById('filterKitchenBahan').value;
        
        // Ambil URL dasar tanpa query string yang lama
        const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.location.href = `${baseUrl}?month=${month}&kitchen=${kitchen}`;
    }

</script>
@stop