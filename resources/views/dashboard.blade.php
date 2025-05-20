@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<!-- Tambahkan CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<div class="container-fluid">
    <!-- Ringkasan Anggaran -->
    <div class="mb-4 row">
        <div class="col-xl-3 col-md-6">
            <div class="text-white card bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-uppercase">Total Anggaran</h6>
                            <h3 class="mb-0" id="total-anggaran">-</h3>
                        </div>
                        <div class="bg-white icon-shape text-primary rounded-circle">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="text-white card bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-uppercase">Total Realisasi</h6>
                            <h3 class="mb-0" id="total-realisasi">-</h3>
                        </div>
                        <div class="bg-white icon-shape text-success rounded-circle">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="text-white card bg-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-uppercase">Sisa Anggaran</h6>
                            <h3 class="mb-0" id="sisa-anggaran">-</h3>
                        </div>
                        <div class="bg-white icon-shape text-info rounded-circle">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="text-white card bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-uppercase">Persentase Realisasi</h6>
                            <h3 class="mb-0" id="persentase-realisasi">-</h3>
                        </div>
                        <div class="bg-white icon-shape text-warning rounded-circle">
                            <i class="bi bi-percent"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter dan Grafik -->
    <div class="mb-4 row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="filter-form" class="row g-3">
                        <div class="col-md-4">
                            <label for="tahapan" class="form-label">Tahapan</label>
                            <select name="tahapan" id="tahapan" class="form-select">
                                <option value="">Semua Tahapan</option>
                                @foreach($tahapans as $tahapan)
                                    <option value="{{ $tahapan->id }}">{{ $tahapan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="opd" class="form-label">OPD</label>
                            <select name="opd" id="opd" class="form-select">
                                <option value="">Semua OPD</option>
                                @foreach($skpds as $skpd)
                                    <option value="{{ $skpd->kode_skpd }}">{{ $skpd->nama_skpd }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                            <button type="reset" id="reset-filter" class="btn btn-secondary w-100 ms-2">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="row">
        <div class="mb-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Perbandingan Anggaran vs Realisasi per OPD</h5>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
        <div class="mb-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Persentase Realisasi per OPD</h5>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Ringkasan OPD -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ringkasan per OPD</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="opd-table">
                            <thead>
                                <tr>
                                    <th>OPD</th>
                                    <th class="text-end">Anggaran</th>
                                    <th class="text-end">Realisasi</th>
                                    <th class="text-end">Sisa</th>
                                    <th class="text-end">% Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan diisi melalui JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-shape i {
    font-size: 24px;
}
</style>

<script>
$(document).ready(function() {
    var barChart, pieChart;

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    function formatPersentase(angka) {
        return angka.toFixed(2) + '%';
    }

    function updateDashboard(data) {
        // Update ringkasan
        $('#total-anggaran').text(formatRupiah(data.total_anggaran));
        $('#total-realisasi').text(formatRupiah(data.total_realisasi));
        $('#sisa-anggaran').text(formatRupiah(data.sisa_anggaran));
        $('#persentase-realisasi').text(formatPersentase(data.persentase_realisasi));

        // Update tabel
        let tableHtml = '';
        data.opd_data.forEach(function(item) {
            tableHtml += `
                <tr>
                    <td>${item.nama_skpd}</td>
                    <td class="text-end">${formatRupiah(item.anggaran)}</td>
                    <td class="text-end">${formatRupiah(item.realisasi)}</td>
                    <td class="text-end">${formatRupiah(item.sisa)}</td>
                    <td class="text-end">${formatPersentase(item.persentase)}</td>
                </tr>
            `;
        });
        $('#opd-table tbody').html(tableHtml);

        // Update grafik
        updateCharts(data);
    }

    function updateCharts(data) {
        // Bar Chart
        if (barChart) barChart.destroy();
        const ctxBar = document.getElementById('barChart').getContext('2d');
        barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: data.opd_data.map(item => item.nama_skpd),
                datasets: [
                    {
                        label: 'Anggaran',
                        data: data.opd_data.map(item => item.anggaran),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)'
                    },
                    {
                        label: 'Realisasi',
                        data: data.opd_data.map(item => item.realisasi),
                        backgroundColor: 'rgba(75, 192, 192, 0.7)'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${formatRupiah(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                }
            }
        });

        // Pie Chart
        if (pieChart) pieChart.destroy();
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: data.opd_data.map(item => item.nama_skpd),
                datasets: [{
                    data: data.opd_data.map(item => item.persentase),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${formatPersentase(context.raw)}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Event Submit Filter Form
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        let tahapan = $('#tahapan').val();
        let opd = $('#opd').val();
        fetchData(tahapan, opd);
    });

    // Event Reset Filter
    $('#reset-filter').on('click', function(e) {
        e.preventDefault();
        $('#filter-form')[0].reset();
        fetchData();
    });

    // Fetch data
    function fetchData(tahapan = '', opd = '') {
        $.ajax({
            url: "{{ route('dashboard.data') }}",
            type: "GET",
            data: { 
                tahapan: tahapan,
                opd: opd
            },
            success: function(response) {
                updateDashboard(response.data);
            }
        });
    }

    // Initial load
    fetchData();
});
</script>

@endsection
