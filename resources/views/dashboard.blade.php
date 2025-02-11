@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<!-- Tambahkan CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<div class="container">

    <!-- Form Filter -->
    <form id="filter-form" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="nama_rekening" class="form-label">Nama Rekening</label>
            <input type="text" name="nama_rekening" id="nama_rekening" class="form-control" placeholder="Masukkan Nama Rekening">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            <button type="reset" id="reset-filter" class="btn btn-secondary w-100 ms-2">Reset</button>
        </div>
    </form>

    <!-- Grafik -->
    <div class="row">
        <div class="col-md-12">
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var barChart;

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        }

        function updateChart(data) {
            let labels = data.map(item => item.nama_skpd);
            let paguOriginal = data.map(item => item.pagu_original);
            let paguRevisi = data.map(item => item.pagu_revisi);
            let selisih = data.map(item => item.pagu_revisi - item.pagu_original);
            let persentase = data.map(item => 
                item.pagu_original > 0 ? ((item.pagu_revisi - item.pagu_original) / item.pagu_original * 100).toFixed(2) : 0
            );

            // Hapus chart jika sudah ada
            if (barChart) barChart.destroy();

            // Grafik Bar Chart - Pagu Original vs Revisi dengan Persentase Selisih
            var ctxBar = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Pagu Original",
                            data: paguOriginal,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)'
                        },
                        {
                            label: "Pagu Revisi",
                            data: paguRevisi,
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            datalabels: {
                                anchor: 'end',
                                align: 'start',
                                formatter: function(value, context) {
                                    return persentase[context.dataIndex] + "%"; // ✅ Persentase selisih di atas Pagu Revisi
                                }
                            }
                        },
                        {
                            label: "Selisih",
                            data: selisih,
                            backgroundColor: 'rgba(255, 99, 132, 0.7)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y', // ✅ Mengubah orientasi menjadi vertikal
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    let datasetIndex = tooltipItem.datasetIndex;
                                    let value = tooltipItem.raw;
                                    if (datasetIndex === 1) { // Pagu Revisi
                                        let persentaseSelisih = persentase[tooltipItem.dataIndex] + "%";
                                        return `Revisi: ${formatRupiah(value)} (${persentaseSelisih})`;
                                    } else if (datasetIndex === 2) { // Selisih
                                        let persentaseSelisih = persentase[tooltipItem.dataIndex] + "%";
                                        return `Selisih: ${formatRupiah(value)} (${persentaseSelisih})`;
                                    }
                                    return `${tooltipItem.dataset.label}: ${formatRupiah(value)}`;
                                }
                            }
                        },
                        datalabels: {
                            display: function(context) { 
                                return context.datasetIndex === 1; // ✅ Hanya tampilkan persentase di Pagu Revisi
                            },
                            color: 'black',
                            font: { weight: 'bold', size: 10 }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatRupiah(value);
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels] // ✅ Aktifkan Data Labels Plugin
            });
        }

        // Ambil data awal tanpa filter
        function fetchData(namaRekening = '') {
            $.ajax({
                url: "{{ route('dashboard.data') }}",
                type: "GET",
                data: { nama_rekening: namaRekening },
                success: function(response) {
                    updateChart(response.data);
                }
            });
        }

        // Event Submit Filter Form
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            let namaRekening = $('#nama_rekening').val();
            fetchData(namaRekening);
        });

        // Event Reset Filter
        $('#reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#filter-form')[0].reset();
            fetchData();
        });

        // Ambil data pertama kali
        fetchData();
    });
</script>

@endsection
