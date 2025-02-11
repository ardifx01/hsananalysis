@extends('layouts.app')

@section('title', 'Simulasi Penyesuaian Per OPD')
@section('page-title', 'Simulasi Penyesuaian Per OPD')

@section('content')

<!-- Import DataTables & Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<style>
    .input-small { width: 80px; text-align: center; }
    .table-sm th, .table-sm td { padding: 6px 10px; font-size: 12px; }
    .btn-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .alert-info { font-size: 14px; text-align: center; padding: 10px; }
</style>

<div class="container">

    <!-- Form Filter -->
    <form id="filter-form" class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="kode_opd" class="form-label">Pilih OPD</label>
            <select name="kode_opd" id="kode_opd" class="form-select">
                <option value="">Silakan pilih OPD</option>
                @foreach($opds as $opd)
                    <option value="{{ $opd->kode_skpd }}" {{ request('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                        {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            <button type="reset" id="reset-filter" class="btn btn-secondary w-100 ms-2">Reset</button>
        </div>
    </form>

    @if(request('kode_opd'))
        <!-- Button Container -->
        <div class="btn-container">
            <div>
                <button type="submit" form="update-form" class="btn btn-success">Simpan Perubahan</button>
                <button id="reset-nilai" class="btn btn-warning">Reset Nilai OPD Ini</button>
            </div>
        </div>

        <!-- Form Update Persentase -->
        <form id="update-form" action="{{ route('simulasi.set-opd-rek.update') }}" method="POST">
            @csrf
            <input type="hidden" name="kode_opd" value="{{ request('kode_opd') }}">

            <div class="table-responsive">
                <table id="rekapTable" class="table table-striped table-bordered table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode Rekening</th>
                            <th>Nama Rekening</th>
                            <th>Pagu Original</th>
                            <th>Persentase Penyesuaian</th>
                            <th>Jumlah Penyesuaian</th>
                            <th>Pagu Setelah Penyesuaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $row)
                            @php
                                $nilai_penyesuaian = ($row->pagu_original * $row->persentase_penyesuaian) / 100;
                                $pagu_setelah = $row->pagu_original - $nilai_penyesuaian;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <input type="hidden" name="kode_rekening[]" value="{{ $row->kode_rekening }}">
                                    {{ $row->kode_rekening }}
                                </td>
                                <td>{{ Str::limit($row->nama_rekening, 30) }}</td>
                                <td class="text-end pagu-original" data-value="{{ $row->pagu_original }}">
                                    {{ number_format($row->pagu_original, 0, ',', '.') }}
                                </td>
                                <td>
                                    <input type="number" class="form-control persentase-penyesuaian" 
                                           name="persentase_penyesuaian[]" 
                                           value="{{ $row->persentase_penyesuaian }}" min="0" max="100" step="0.01">
                                </td>
                                <td class="text-end nilai-penyesuaian">{{ number_format($nilai_penyesuaian, 0, ',', '.') }}</td>
                                <td class="text-end pagu-setelah">{{ number_format($pagu_setelah, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th id="total-pagu-original" class="text-end">0</th>
                            <th id="total-persentase" class="text-end">0%</th>
                            <th id="total-nilai-penyesuaian" class="text-end">0</th>
                            <th id="total-pagu-setelah" class="text-end">0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
    @else
        <div class="alert alert-info">Silakan pilih OPD untuk menampilkan data.</div>
    @endif
</div>

<!-- jQuery untuk DataTables & Perhitungan -->
<script>
    $(document).ready(function() {
        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(value);
        }

        function hitungTotal() {
            let totalPaguOriginal = 0;
            let totalNilaiPenyesuaian = 0;
            let totalPaguSetelah = 0;

            $('tbody tr').each(function() {
                let row = $(this);
                let paguOriginal = parseFloat(row.find('.pagu-original').data('value')) || 0;
                let persentase = parseFloat(row.find('.persentase-penyesuaian').val()) || 0;
                let nilaiPenyesuaian = (paguOriginal * persentase) / 100;
                let paguSetelah = paguOriginal - nilaiPenyesuaian;

                row.find('.nilai-penyesuaian').text(formatNumber(nilaiPenyesuaian));
                row.find('.pagu-setelah').text(formatNumber(paguSetelah));

                totalPaguOriginal += paguOriginal;
                totalNilaiPenyesuaian += nilaiPenyesuaian;
                totalPaguSetelah += paguSetelah;
            });

            $('#total-pagu-original').text(formatNumber(totalPaguOriginal));
            $('#total-nilai-penyesuaian').text(formatNumber(totalNilaiPenyesuaian));
            $('#total-pagu-setelah').text(formatNumber(totalPaguSetelah));
        }

        $('.persentase-penyesuaian').on('input', function() {
            hitungTotal();
        });

        hitungTotal();

        /** ✅ Tambahkan DataTables dengan fitur Export */
        $('#rekapTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: 'Copy' },
                { extend: 'csv', text: 'CSV' },
                { extend: 'excel', text: 'Excel' },
                { extend: 'pdf', text: 'PDF', orientation: 'landscape' },
                { extend: 'print', text: 'Print' }
            ],
            paging: false,
            searching: true,
            responsive: true
        });

        /** ✅ Perbaikan tombol RESET OPD **/
        $('#reset-nilai').on('click', function() {
            let kodeOpd = $('#kode_opd').val();

            if (!kodeOpd) {
                alert('Silakan pilih OPD terlebih dahulu.');
                return;
            }

            if (confirm('Apakah Anda yakin ingin mereset nilai penyesuaian untuk OPD ini?')) {
                $.ajax({
                    url: "{{ route('simulasi.set-opd-rek.reset') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        kode_opd: kodeOpd
                    },
                    success: function(response) {
                        if (response.success) {
                            alert("Nilai penyesuaian berhasil direset.");
                            location.reload(); // Reload halaman hanya jika berhasil
                        } else {
                            alert("Gagal mereset nilai: " + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat mereset data.');
                    }
                });
            }
        });
    });
</script>

@endsection
