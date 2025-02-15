@extends('layouts.app')

@section('title', 'Rekap Perjalanan Dinas')
@section('page-title', 'Rekap Perjalanan Dinas')

@section('content')

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
tfoot {
  position: sticky;
  bottom: 0;
  border-top: 2px solid #ccc;
}
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

    <!-- 🔥 Tombol Simpan Semua -->
    <button id="saveAllButton" class="btn btn-primary save-all-button">Simpan Semua</button>

    <table id="rekapTable" class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th class="text-center">No</th>
                <th>Nama OPD</th>
                <th>Nama Rekening</th>
                <th class="text-end">Pagu Murni</th>
                <th class="text-end">Total Perjalanan Dinas OPD</th>
                <th class="text-end">Persentase Pengurangan</th>
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
                                <input type="number" class="slider-value" 
                                    data-kode-opd="{{ $row->kode_skpd }}" 
                                    data-kode-rekening="{{ $row->kode_rekening }}" 
                                    value="{{ $row->persentase_penyesuaian }}" min="0" max="100" step="1">
                                <span>%</span>
                            </div>
                        </td>
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
                                <input type="number" class="slider-value" 
                                    data-kode-opd="{{ $row->kode_skpd }}" 
                                    data-kode-rekening="{{ $row->kode_rekening }}" 
                                    value="{{ $row->persentase_penyesuaian }}" min="0" max="100" step="1">
                                <span>%</span>
                            </div>
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
 updateValues();
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

    // 🔥 Jalankan update saat halaman pertama kali dimuat
    updateValues();
});



</script>

@endsection
