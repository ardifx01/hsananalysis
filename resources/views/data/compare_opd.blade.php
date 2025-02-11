@extends('layouts.app')

@section('title', 'Rekap Perbandingan Belanja OPD')
@section('page-title', 'Perbandingan Belanja OPD')

@section('content')
<style>
table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .tooltip .tooltip-text {
            visibility: hidden;
            width: 300px;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            text-align: center;
            padding: 5px;
            border-radius: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        .total-container {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: right;
        }

   </style>
   <!-- DataTables CSS -->
   <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
 <div class="card" data-aos="fade-up" data-aos-delay="800">
        <div class="flex-wrap card-header d-flex justify-content-between align-items-center">
 <div class="col-md-12">
    <table id="rekapTable">
        <thead>
            <tr>
               
                <th>Kode OPD</th>
                <th>Nama OPD</th>
                <th>Pagu Original</th>
                <th>Pagu Revisi</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $data)
            <tr>
               
                <td>{{ $data['kode_skpd'] }}</td>
                <td>{{ Str::limit($data['nama_skpd'], 50) }}</td>
                <td class="pagu-original">{{ number_format($data['pagu_original'], 2, ',', '.') }}</td>
                <td class="pagu-revisi">{{ number_format($data['pagu_revisi'], 2, ',', '.') }}</td>
                <td class="pagu-selisih">{{ number_format($data['selisih'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-container">
        Total Pagu Original: <span id="totalOriginal">0</span> | 
        Total Pagu Revisi: <span id="totalRevisi">0</span> | 
        Total Selisih: <span id="totalSelisih">0</span>
    </div>
    <a href="{{ url('/import') }}">Kembali ke Upload</a>
</div>
</div>
</div>
    <!-- Tambahkan jQuery dan DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

     <script>
    $(document).ready(function() {
        var tableElement = $('#rekapTable');
        
        if (tableElement.length) {
            var table = tableElement.DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthMenu": [100, 250, 500, 1000],
                "language": {
                    "search": "Cari Data:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "initComplete": function() {
                    updateTotal(); // Jalankan saat pertama kali halaman dimuat
                },
                "drawCallback": function() {
                    updateTotal(); // Jalankan setiap kali filter diterapkan
                }
            });
        }

        function updateTotal() {
            var totalOriginal = 0;
            var totalRevisi = 0;
            var totalSelisih = 0;

            $('#rekapTable tbody tr').each(function() {
                var paguOriginal = parseFloat($(this).find('.pagu-original').text().replace(/\./g, '').replace(',', '.')) || 0;
                var paguRevisi = parseFloat($(this).find('.pagu-revisi').text().replace(/\./g, '').replace(',', '.')) || 0;
                var paguSelisih = parseFloat($(this).find('.pagu-selisih').text().replace(/\./g, '').replace(',', '.')) || 0;

                totalOriginal += paguOriginal;
                totalRevisi += paguRevisi;
                totalSelisih += paguSelisih;
            });

            $('#totalOriginal').text(totalOriginal.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
            $('#totalRevisi').text(totalRevisi.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
            $('#totalSelisih').text(totalSelisih.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
        }
    });
</script>
    
@endsection
