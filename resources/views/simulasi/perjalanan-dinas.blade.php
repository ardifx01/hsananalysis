@extends('layouts.app')

@section('title', 'Set % perjalanan dinas / Perbandingan Per OPD')
@section('page-title', 'Set % perjalanan dinas / Perbandingan Per OPD')

@section('content')
<!-- ✅ Tambahkan DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<style>


  
    table { width: 100%; border-collapse: collapse; background-color: #fff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); border-radius: 5px; overflow: hidden; }
    th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 12px; white-space: normal; word-wrap: break-word; }
    th { background-color: #0056b3 !important; color: white; font-weight: bold; text-align: center; }
    tr:hover { background-color: #f1f1f1; }
    .total-container { margin-top: 15px; font-size: 14px; font-weight: bold; text-align: right; }

    /* Border untuk setiap OPD */
    .opd-border-top { border-top: 2px solid #000 !important; }
    
    /* Tebalkan total pagu */
    .bold { font-weight: bold; }

    /* Warna khusus untuk total keseluruhan */
    .total-row { background-color: #d9edf7 !important; font-weight: bold; }

    /* Custom styling untuk slider */
    .slider-container {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .slider {
        width: 100px;
        height: 4px;
        background: #ddd;
        border-radius: 5px;
        cursor: pointer;
    }

    .slider-value {
        width: 45px;
        text-align: right;
        font-weight: bold;
    }

    /* Tombol simpan */
    .save-all-button { margin-bottom: 10px; }

    /* Sticky Footer */

/* ✅ Kotak Floating untuk Total */
#floatingTotalBox {
    position: fixed;
    bottom: 20px; /* Jarak dari bawah layar */
    right: 20px; /* Jarak dari kanan layar */
    background-color: #0056b3;
    color: white;
    padding: 15px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    width: 300px;
}

/* ✅ Responsif: Jika di layar kecil, geser lebih ke atas */
@media (max-width: 768px) {
    #floatingTotalBox {
        bottom: 60px; /* Geser ke atas agar tidak tertutup elemen lain */
        right: 10px;
        width: 220px;
    }
}

</style>

<div class="table-container">

    <!-- Filter Tahapan -->
    <div class="mb-3 row">
        <div class="col-md-4">
            <label for="tahapanFilter" class="form-label"><strong>Filter Tahapan Anggaran:</strong></label>
            <select id="tahapanFilter" class="form-select" onchange="filterByTahapan()">
                @foreach($tahapans as $tahapan)
                    <option value="{{ $tahapan->id }}" {{ $tahapan->id == $tahapanId ? 'selected' : '' }}>
                        {{ $tahapan->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <div class="mt-4 alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Info:</strong> Data yang ditampilkan berdasarkan tahapan anggaran yang dipilih.
                <br>
                <small class="text-muted">Tahapan saat ini: <strong>{{ $tahapans->where('id', $tahapanId)->first()->name ?? 'Tahapan ' . $tahapanId }}</strong></small>
            </div>
        </div>
    </div>

    <!-- 🔥 Tombol Simpan Semua -->
    <button id="saveAllButton" class="btn btn-primary save-all-button">Simpan Semua</button>
    <button onclick="exportToExcel()" class="btn btn-success">Export ke Excel</button>
    <button onclick="exportToPDF()" class="btn btn-danger">Export ke PDF</button>


    <table id="rekapTable" class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th class="text-center">No</th>
                <th>Nama OPD</th>
                <th>Nama Rekening</th>
                <th class="text-end">{{ $tahapans->where('id', $tahapanId)->first()->name ?? 'Pagu Murni' }}</th>
                <th class="text-end">Total Perjalanan Dinas OPD</th>
                <th class="text-end">Slider Persentase</th>
                <th>Persentase Pengurangan</th>
                
                <th class="text-end">Pagu Pengurangan</th>
                <th class="text-end">Total Pagu Pengurangan OPD</th>
                <th class="text-end">Pagu Setelah Pengurangan</th>
                <th class="text-end">Total Pagu Setelah Pengurangan OPD</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentOpd = null;
                $nomor = 1;
            @endphp
            @foreach($data as $index => $row)
                <tr class="{{ $row->nama_opd !== $currentOpd ? 'opd-border-top' : '' }}">
                    @if ($row->nama_opd !== $currentOpd)
                        <td class="text-center">{{ $nomor }}</td>
                        <td class="bold">{{ $row->nama_opd }}</td>
                        <td>{{ $row->nama_rekening }}</td>
                        <td class="text-end pagu-murni" data-pagu="{{ $row->pagu_original }}">{{ number_format($row->pagu_original, 2, ',', '.') }}</td>
                        <td class="text-end bold total-perjalanan-dinas" data-kode-opd="{{ $row->kode_skpd }}">{{ number_format($row->total_perjalanan_dinas, 2, ',', '.') }}</td>
                        <td class="text-end">
                            <div class="slider-container">
                                <input type="range" class="slider" min="0" max="100" step="1"
                                    data-kode-opd="{{ $row->kode_skpd }}" 
                                    data-kode-rekening="{{ $row->kode_rekening }}" 
                                    value="{{ $row->persentase_penyesuaian }}">
                                
                            </div>
                        </td>
                        <td class="text-end persentase-pengurangan-td"><input type="number" class="slider-value persentase-pengurangan-td" 
                                    data-kode-opd="{{ $row->kode_skpd }}" 
                                    data-kode-rekening="{{ $row->kode_rekening }}" 
                                    value="{{ $row->persentase_penyesuaian }}" min="0" max="100" step="1">
                                <span>%</span></td>
                        
                        <td class="text-end pagu-pengurangan">0</td>
                        <td class="text-end bold total-pagu-pengurangan" data-kode-opd="{{ $row->kode_skpd }}">0</td>
                        <td class="text-end pagu-setelah">0</td>
                        <td class="text-end bold total-pagu-setelah" data-kode-opd="{{ $row->kode_skpd }}">0</td>
                        @php
                            $currentOpd = $row->nama_opd;
                            $nomor++;
                        @endphp
                    @else
                        <td></td>
                        <td></td>
                        <td>{{ $row->nama_rekening }}</td>
                        <td class="text-end pagu-murni" data-pagu="{{ $row->pagu_original }}">{{ number_format($row->pagu_original, 2, ',', '.') }}</td>
                        <td></td>
                        
                        <td class="text-end">
                            <div class="slider-container">
                                <input type="range" class="slider" min="0" max="100" step="1"
                                    data-kode-opd="{{ $row->kode_skpd }}" 
                                    data-kode-rekening="{{ $row->kode_rekening }}" 
                                    value="{{ $row->persentase_penyesuaian }}">
                                
                            </div>
                        </td>
                        <td class="text-end persentase-pengurangan-td">
                        <input type="number" class="slider-value" 
                                    data-kode-opd="{{ $row->kode_skpd }}" 
                                    data-kode-rekening="{{ $row->kode_rekening }}" 
                                    value="{{ $row->persentase_penyesuaian }}" min="0" max="100" step="1">
                                <span>%</span>
                                </td>
                        <td class="text-end pagu-pengurangan">0</td>
                        <td></td>
                        <td class="text-end pagu-setelah">0</td>
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </tbody>

       <tfoot class="table-dark">
    <tr class="total-row">
        <td colspan="4" class="text-end">Total Keseluruhan:</td>
        <td class="text-end" id="totalPaguMurni">0</td>
        <td></td>
        <td class="text-end bold" id="totalPersentasePengurangan">0%</td>
        <td></td>
        <td class="text-end bold" id="totalPaguPengurangan">0</td>
        <td></td>
        <td class="text-end bold" id="totalPaguSetelah">0</td>
    </tr>
</tfoot>
    </table>
</div>
<!-- 🔥 Kotak Floating Total -->
<div id="floatingTotalBox">
    <div>Pagu Murni: <span id="totalPaguMurniFloating">0</span></div>
<div>Persentase Pengurangan: <span id="totalPersentasePenguranganFloating">0%</span></div>
<div>Pagu Pengurangan: <span id="totalPaguPenguranganFloating">0</span></div>
<div>Pagu Setelah: <span id="totalPaguSetelahFloating">0</span></div>

</div>

<!-- ✅ Perbaikan JavaScript -->

<!-- ✅ Tambahkan DataTables & Export Buttons -->



<script>
   $(document).ready(function () {
    function updateValues() {
        let opdTotals = {};
        let totalKeseluruhanMurni = 0;
        let totalKeseluruhanPengurangan = 0;
        let totalKeseluruhanSetelah = 0;

        $(".slider").each(function () {
            let kodeOpd = $(this).data("kode-opd");
            let kodeRekening = $(this).data("kode-rekening");
            let persentase = parseFloat($(this).val()) || 0;
            let row = $(this).closest("tr");
            

            let paguMurni = parseFloat(row.find(".pagu-murni").data("pagu")) || 0;
            let paguPengurangan = (paguMurni * persentase) / 100;
            let paguSetelah = paguMurni - paguPengurangan;

            // 🔥 Perbarui tampilan angka sesuai perubahan slider
            row.find(".slider-value").val(persentase);
            row.find(".pagu-pengurangan").text(paguPengurangan.toLocaleString("id-ID"));
            row.find(".pagu-setelah").text(paguSetelah.toLocaleString("id-ID"));

            // 🔥 Simpan total per OPD
            if (!opdTotals[kodeOpd]) {
                opdTotals[kodeOpd] = {
                    totalPaguMurni: 0,
                    totalPaguPengurangan: 0,
                    totalPaguSetelah: 0
                };
            }

            opdTotals[kodeOpd].totalPaguMurni += paguMurni;
            opdTotals[kodeOpd].totalPaguPengurangan += paguPengurangan;
            opdTotals[kodeOpd].totalPaguSetelah += paguSetelah;

            // 🔥 Tambahkan ke total keseluruhan
            totalKeseluruhanMurni += paguMurni;
            totalKeseluruhanPengurangan += paguPengurangan;
            totalKeseluruhanSetelah += paguSetelah;
        });

        // 🔥 Update total per OPD di kolom pertama tiap OPD
        $(".total-pagu-pengurangan").each(function () {
            let kodeOpd = $(this).data("kode-opd");
            if (opdTotals[kodeOpd]) {
                $(this).text(opdTotals[kodeOpd].totalPaguPengurangan.toLocaleString("id-ID"));
            }
        });

        $(".total-pagu-setelah").each(function () {
            let kodeOpd = $(this).data("kode-opd");
            if (opdTotals[kodeOpd]) {
                $(this).text(opdTotals[kodeOpd].totalPaguSetelah.toLocaleString("id-ID"));
            }
        });

        // 🔥 Perhitungan total persentase pengurangan
        let totalPersentasePengurangan = totalKeseluruhanMurni > 0 
            ? (totalKeseluruhanPengurangan / totalKeseluruhanMurni) * 100 
            : 0;

       // 🔥 Update Total di <tfoot>
        $("#totalPaguMurni").text(totalKeseluruhanMurni.toLocaleString("id-ID"));
        $("#totalPersentasePengurangan").text(totalPersentasePengurangan.toFixed(2) + "%");
        $("#totalPaguPengurangan").text(totalKeseluruhanPengurangan.toLocaleString("id-ID"));
        $("#totalPaguSetelah").text(totalKeseluruhanSetelah.toLocaleString("id-ID"));

        // 🔥 Update Total di Kotak Floating
        $("#totalPaguMurniFloating").text(totalKeseluruhanMurni.toLocaleString("id-ID"));
        $("#totalPersentasePenguranganFloating").text(totalPersentasePengurangan.toFixed(2) + "%");
        $("#totalPaguPenguranganFloating").text(totalKeseluruhanPengurangan.toLocaleString("id-ID"));
        $("#totalPaguSetelahFloating").text(totalKeseluruhanSetelah.toLocaleString("id-ID"));
       
    }

    // 🔥 Event listener untuk slider dan input angka
    $(".slider, .slider-value").on("input", function () {
        let newValue = $(this).val();
        $(this).closest(".slider-container").find(".slider, .slider-value").val(newValue);
        updateValues();
        
    });


    // 🔥 Event Listener untuk Tombol Simpan Semua
    $("#saveAllButton").on("click", function () {



        let changedValues = [];
        $(".slider").each(function () {
            let kodeOpd = $(this).data("kode-opd");
            let kodeRekening = $(this).data("kode-rekening");
            let persentase = $(this).val();

            changedValues.push({
                kode_opd: kodeOpd,
                kode_rekening: kodeRekening,
                persentase_penyesuaian: persentase
            });
        });

        if (changedValues.length > 0) {
            $.ajax({
                url: "{{ route('simulasi.update-massal') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    data: changedValues
                },
                success: function (response) {
                    if (response.success) {
                        alert("Semua perubahan berhasil disimpan!");
                        location.reload();
                    } else {
                        alert("Gagal menyimpan data.");
                    }
                },
                error: function () {
                    alert("Terjadi kesalahan dalam proses penyimpanan.");
                }
            });
        } else {
            alert("Tidak ada perubahan yang perlu disimpan.");
        }
    });

   function exportToExcel() {
    // 🔥 Pastikan nilai slider tersalin ke dalam kolom sebelum ekspor
    $(".slider").each(function () {
        let value = $(this).val();
        $(this).closest("tr").find(".persentase-pengurangan-td").text(value + "%"); // Salin ke sel persentase
    });

    // 🔥 Ambil data dari tabel tanpa kolom slider
    let table = document.getElementById("rekapTable");

    // 🚀 Looping tabel untuk mengekstrak data dalam format array
    let data = [];
    let headers = [];
    
    // 🔥 Ambil header, tanpa kolom slider
    $("#rekapTable thead th").each(function (index) {
        if (index !== 5) { // Hapus kolom slider (indeks ke-6 dalam tabel)
            headers.push($(this).text().trim());
        }
    });
    data.push(headers); // Tambahkan header ke array data

    // 🔥 Ambil isi tabel, tanpa kolom slider
    $("#rekapTable tbody tr").each(function () {
        let rowData = [];
        $(this).find("td").each(function (index) {
            if (index !== 5) { // Hapus kolom slider
                let cellText = $(this).text().trim();
                rowData.push(cellText);
            }
        });
        data.push(rowData);
    });

    // 🔥 Ambil data ringkasan total dari tfoot
    let totalPaguMurni = $("#totalPaguMurni").text().trim();
    let totalPersentasePengurangan = $("#totalPersentasePengurangan").text().trim();
    let totalPaguPengurangan = $("#totalPaguPengurangan").text().trim();
    let totalPaguSetelah = $("#totalPaguSetelah").text().trim();

    // 🔥 Tambahkan ringkasan total di bawah tabel dalam format yang lebih stabil
    data.push([]);
    data.push(["", "", "Ringkasan Total"]);
    data.push(["", "", "Total Pagu Murni", totalPaguMurni]);
    data.push(["", "", "Total Persentase Pengurangan", totalPersentasePengurangan]);
    data.push(["", "", "Total Pagu Pengurangan", totalPaguPengurangan]);
    data.push(["", "", "Total Pagu Setelah Pengurangan", totalPaguSetelah]);

    // 🔥 Buat worksheet dari data array
    let ws = XLSX.utils.aoa_to_sheet(data);

    // 🔥 Perbaiki format angka agar tidak dianggap sebagai teks dalam Excel
    let range = XLSX.utils.decode_range(ws["!ref"]);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        let col = XLSX.utils.encode_col(C);
        for (let R = range.s.r; R <= range.e.r; ++R) {
            let cellAddress = col + XLSX.utils.encode_row(R);
            if (ws[cellAddress] && typeof ws[cellAddress].v === "string" && ws[cellAddress].v.match(/^\d{1,3}(\.\d{3})*(,\d+)?$/)) {
                ws[cellAddress].t = "n"; // Ubah ke format angka
                ws[cellAddress].v = parseFloat(ws[cellAddress].v.replace(/\./g, "").replace(",", ".")); // Konversi angka
            }
        }
    }

    // 🔥 Atur lebar kolom otomatis agar seluruh teks terlihat
    ws["!cols"] = headers.map(() => ({ wch: 20 }));

    // 🔥 Buat workbook dan ekspor ke file
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Rekap Dinas");
    XLSX.writeFile(wb, "rekap_perjalanan_dinas.xlsx");
}



  function exportToPDF() {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF("l", "mm", "a4"); // 📄 Mode Landscape, Ukuran A4

    doc.setFontSize(12);
    doc.text("Rekap Perjalanan Dinas", 14, 10);

    // 🔥 Pastikan nilai slider tersalin ke dalam kolom sebelum ekspor
    $(".slider").each(function () {
        let value = $(this).val();
        $(this).closest("tr").find(".persentase-pengurangan-td").text(value + "%"); // Salin ke sel tabel
    });

    // 🔥 Ambil seluruh tabel kecuali tfoot
    let tempTable = document.createElement("table");
    let tableClone = document.getElementById("rekapTable").cloneNode(true);
    
    let tfootClone = tableClone.getElementsByTagName("tfoot")[0];
    if (tfootClone) {
        tfootClone.remove(); // Hapus footer dari tabel sebelum di-render
    }

    tempTable.innerHTML = tableClone.innerHTML; // Salin isi tabel tanpa footer

    // 🚀 Hapus kolom slider sebelum ekspor
    $(tempTable).find("thead th:nth-child(6), tbody td:nth-child(6)").remove();

    // 🔥 AutoTable dengan perbaikan layout agar semua kolom tampil
    let finalY = doc.autoTable({
        html: tempTable,
        theme: "grid",
        startY: 20,
        margin: { left: 10, right: 10 }, // 🔥 Lebar tabel dimaksimalkan
        styles: { fontSize: 7, cellPadding: 2, overflow: 'linebreak' }, // 🔥 Agar tidak terpotong
        headStyles: { fillColor: [41, 128, 185], textColor: 255, fontSize: 8 },
        columnStyles: { 
            0: { cellWidth: 8 },   // No
            1: { cellWidth: 35 },  // Nama OPD
            2: { cellWidth: 40 },  // Nama Rekening
            3: { cellWidth: 25 },  // Pagu Murni
            4: { cellWidth: 30 },  // Total Perjalanan Dinas
            5: { cellWidth: 20 },  // Persentase Pengurangan
            6: { cellWidth: 30 },  // Pagu Pengurangan
            7: { cellWidth: 30 },  // Total Pagu Pengurangan OPD
            8: { cellWidth: 30 },  // Pagu Setelah Pengurangan
            9: { cellWidth: 30 }   // Total Pagu Setelah Pengurangan OPD
        },
        didDrawPage: function (data) {
            // 🔥 Pastikan tabel tidak terpotong dan ada margin yang cukup di bawah
            doc.setDrawColor(0);
            doc.setLineWidth(0.5);
            doc.line(10, data.cursor.y + 2, 287, data.cursor.y + 2);
        }
    }).lastAutoTable.finalY;

    // 🔥 Pastikan footer hanya ada di halaman terakhir
    let totalPages = doc.getNumberOfPages();
    doc.setPage(totalPages);
    finalY += 10; // Beri jarak dari tabel terakhir

    // 🔥 Ambil data dari tfoot
    let totalPaguMurni = document.getElementById("totalPaguMurni").innerText;
    let totalPersentasePengurangan = document.getElementById("totalPersentasePengurangan").innerText;
    let totalPaguPengurangan = document.getElementById("totalPaguPengurangan").innerText;
    let totalPaguSetelah = document.getElementById("totalPaguSetelah").innerText;

    // 🔥 Tambahkan ringkasan total dalam kotak di bawah tabel di halaman terakhir
    doc.setFontSize(10);
    doc.setFillColor(230, 230, 230); // 🔥 Warna background abu-abu
    doc.rect(170, finalY, 110, 30, 'F'); // 🔥 Kotak ringkasan total
    doc.setTextColor(0);
    doc.text("Ringkasan Total:", 175, finalY + 5);
    doc.setFontSize(9);
    doc.text(`• Pagu Murni: Rp ${totalPaguMurni}`, 175, finalY + 10);
    doc.text(`• Persentase Pengurangan: ${totalPersentasePengurangan}`, 175, finalY + 15);
    doc.text(`• Pagu Pengurangan: Rp ${totalPaguPengurangan}`, 175, finalY + 20);
    doc.text(`• Pagu Setelah: Rp ${totalPaguSetelah}`, 175, finalY + 25);

    // 🔥 Simpan PDF
    doc.save("rekap_perjalanan_dinas.pdf");
}



    // Pastikan fungsi tersedia di global scope
    window.exportToExcel = exportToExcel;
    window.exportToPDF = exportToPDF;
 


     // 🔥 Jalankan update saat halaman pertama kali dimuat
    updateValues();

    // Function untuk filter berdasarkan tahapan
    window.filterByTahapan = function() {
        const tahapanId = document.getElementById('tahapanFilter').value;
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('tahapan_id', tahapanId);
        window.location.href = currentUrl.toString();
    };

});



</script>

@endsection
