@extends('layouts.app')

@section('title', 'Rekap Per OPD Per Rekening')
@section('page-title', 'Rekap Per OPD Per Rekening')

@section('content')

 
<!-- jQuery dan DataTables -->



<style>
    .table-container {
        width: 100%;
        overflow-x: auto; /* Agar tetap responsif tanpa scroll horizontal */
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        overflow: hidden;
        table-layout: fixed; /* Menghindari kolom melebar */
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 12px;
        white-space: normal; /* Biarkan teks wrap */
        word-wrap: break-word; /* Pastikan teks panjang tidak membuat kolom melebar */
    }

    th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .total-container {
        margin-top: 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: right;
    }

   .dt-buttons {
        margin-bottom: 10px;
    }
    .dt-buttons .btn {
        margin-right: 5px;
        display: inline-block;
    }
</style>
   <!-- DataTables CSS -->
   <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
 <div class="card" data-aos="fade-up" data-aos-delay="800">
        <div class="flex-wrap card-header d-flex justify-content-between align-items-center">
 <div class="col-md-12">
 
 <div class="table-container">
 
    <table id="rekapTable">
    <thead>
        <tr>
            <th>Kode OPD</th>
            <th>Nama OPD</th>
            <th>Kode Rekening</th>
            <th>Nama Rekening</th>
            <th>Pagu Original</th>
            <th>Pagu Revisi</th>
            <th>Selisih</th>
            <th>Persentase Selisih (%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rekap as $data)
        <tr>
            <td>{{ $data['kode_opd'] }}</td>
            <td>{{ Str::limit($data['nama_opd'], 40) }}</td>
            <td>{{ $data['kode_rekening'] }}</td>
            <td>{{ Str::limit($data['nama_rekening'], 50) }}</td>
            <td class="pagu-original">{{ number_format($data['pagu_original'], 2, ',', '.') }}</td>
            <td class="pagu-revisi">{{ number_format($data['pagu_revisi'], 2, ',', '.') }}</td>
            <td class="pagu-selisih">{{ number_format($data['selisih'], 2, ',', '.') }}</td>
            <td class="persentase-selisih">0%</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" style="text-align:right">Total:</th>
            <th id="totalOriginal">0</th>
            <th id="totalRevisi">0</th>
            <th id="totalSelisih">0</th>
            <th id="totalPersentase">0%</th>
        </tr>
    </tfoot>
</table>


    <div class="total-container">
        Total Pagu Original: <span id="totalOriginal">0</span> | 
        Total Pagu Revisi: <span id="totalRevisi">0</span> | 
        Total Selisih: <span id="totalSelisih">0</span>
        Persentase Selisih: <span id="totalPersentase">0%</span>
    </div>
    <a href="{{ url('/import') }}">Kembali ke Upload</a>
</div>
</div>
</div>
</div>
    <!-- Tambahkan jQuery dan DataTables JS -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}
    

 <script>
    $(document).ready(function() {
    console.log("DataTables is initializing...");

    var table = $('#rekapTable').DataTable({
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
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📊 Download Excel',
                className: 'btn btn-success',
                footer: true, // Memastikan footer ikut diekspor
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row:last', sheet).attr('s', '2'); // Format teks footer
                }
            },
            {
                extend: 'pdfHtml5',
                text: '📄 Download PDF',
                className: 'btn btn-danger',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    modifier: { page: 'all' }
                },
                customize: function (doc) {
                    doc.content[1].table.body.push([
                        { text: "Total", bold: true, alignment: "right", colSpan: 4 }, {}, {}, {},
                        { text: $('#totalOriginal').text(), bold: true },
                        { text: $('#totalRevisi').text(), bold: true },
                        { text: $('#totalSelisih').text(), bold: true },
                        { text: $('#totalPersentase').text(), bold: true }
                    ]);
                }
            }
        ],
        "footerCallback": function(row, data, start, end, display) {
            var api = this.api();
            
            var totalOriginal = 0, totalRevisi = 0, totalSelisih = 0, totalPersentase = 0, countValidPersentase = 0;

            api.rows({ search: 'applied' }).every(function() {
                var row = $(this.node());
                
                var paguOriginal = parseFloat(row.find('.pagu-original').text().replace(/\./g, '').replace(',', '.')) || 0;
                var paguRevisi = parseFloat(row.find('.pagu-revisi').text().replace(/\./g, '').replace(',', '.')) || 0;
                var paguSelisih = paguRevisi - paguOriginal;

                totalOriginal += paguOriginal;
                totalRevisi += paguRevisi;
                totalSelisih += paguSelisih;

                var persentaseSelisih = paguOriginal !== 0 ? ((paguSelisih / paguOriginal) * 100).toFixed(2) : 0;
                totalPersentase += parseFloat(persentaseSelisih);
                countValidPersentase++;
            });

            var avgPersentase = countValidPersentase > 0 ? (totalPersentase / countValidPersentase) : 0;

            $(api.column(4).footer()).text(totalOriginal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $(api.column(5).footer()).text(totalRevisi.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $(api.column(6).footer()).text(totalSelisih.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $(api.column(7).footer()).text(avgPersentase.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%');

            console.log("Total updated:", { totalOriginal, totalRevisi, totalSelisih, avgPersentase });
        }
    });
});

</script>





    
@endsection
