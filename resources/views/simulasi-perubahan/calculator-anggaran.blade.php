@extends('layouts.app')

@section('title', 'Calculator Anggaran')
@section('page-title', 'Calculator Anggaran')

@section('content')
<div class="container-fluid">
    <!-- Filter -->
    <div class="mb-4 row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="filter-form" class="row g-3">
                        <div class="col-md-4">
                            <label for="tahapan" class="form-label">Tahapan <span class="text-danger">*</span></label>
                            <select name="tahapan" id="tahapan" class="form-select" required>
                                <option value="">Pilih Tahapan</option>
                                @foreach($tahapans as $tahapan)
                                    <option value="{{ $tahapan->id }}" {{ $defaultTahapan && $defaultTahapan->id == $tahapan->id ? 'selected' : '' }}>
                                        {{ $tahapan->name }}
                                    </option>
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

    <!-- Tabel Data -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Data Anggaran</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="data-table">
                            <thead>
                                <tr>
                                    <th>OPD</th>
                                    <th>Kode Rekening</th>
                                    <th>Nama Rekening</th>
                                    <th>Uraian</th>
                                    <th class="text-end">Anggaran</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan diisi melalui JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td class="text-end" id="total-anggaran">-</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script>
$(document).ready(function() {
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

    function updateTable(data) {
        let tableHtml = '';
        let totalAnggaran = 0;

        data.forEach(function(item) {
            totalAnggaran += item.anggaran;

            tableHtml += `
                <tr>
                    <td>${item.nama_skpd}</td>
                    <td>${item.kode_rekening}</td>
                    <td>${item.nama_rekening}</td>
                    <td>${item.nama_standar_harga ?? '-'}</td>
                    <td class="text-end">${formatRupiah(item.anggaran)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" onclick="calculate('${item.kode_rekening}')">
                            <i class="bi bi-calculator"></i> Hitung
                        </button>
                    </td>
                </tr>
            `;
        });

        // Update total
        $('#total-anggaran').text(formatRupiah(totalAnggaran));

        $('#data-table tbody').html(tableHtml);
        // Destroy and re-init DataTable
        if ($.fn.DataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().destroy();
        }
        $('#data-table').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            order: [[1, 'asc']], // default order by kode rekening
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
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
        // Set kembali tahapan default
        $('#tahapan').val('{{ $defaultTahapan ? $defaultTahapan->id : "" }}');
        fetchData();
    });

    // Fetch data
    function fetchData(tahapan = '', opd = '') {
        $.ajax({
            url: "{{ route('calculator-anggaran.data') }}",
            type: "GET",
            data: { 
                tahapan: tahapan || '{{ $defaultTahapan ? $defaultTahapan->id : "" }}',
                opd: opd
            },
            success: function(response) {
                console.log('Response:', response); // Debug log
                if (response.data) {
                    updateTable(response.data);
                } else {
                    console.error('No data in response');
                    alert('Tidak ada data yang ditemukan');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr.responseText); // Debug log
                alert('Terjadi kesalahan saat mengambil data: ' + error);
            }
        });
    }

    // Initial load
    fetchData();
});

// Function untuk kalkulasi (akan diimplementasikan nanti)
function calculate(kodeRekening) {
    alert('Fitur kalkulasi untuk rekening ' + kodeRekening + ' akan diimplementasikan');
}
</script>
@endsection 