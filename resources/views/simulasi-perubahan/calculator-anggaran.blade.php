@extends('layouts.app')

@section('title', 'Rincian Belanja Per OPD')
@section('page-title', 'Rincian Belanja Per OPD')

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
                                    <th>Kode Sub Kegiatan</th>
                                    <th>Nama Sub Kegiatan</th>
                                    <th>Kode Rekening</th>
                                    <th>Nama Rekening</th>
                                    <th>Kode Standar Harga</th>
                                    <th>Uraian</th>
                                    <th class="text-end">Pagu</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><input type="text" placeholder="Cari Nama Rekening" class="form-control form-control-sm column-search" data-col="3"></th>
                                    <th></th>
                                    <th>
                                        <input type="text" placeholder="Cari Uraian" class="form-control form-control-sm column-search" data-col="5" id="uraian-filter">
                                        <label class="small ms-1"><input type="checkbox" id="uraian-exclude"> Jangan tampilkan data ini</label>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan diisi melalui JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="6">Total</td>
                                    <td class="text-end" id="total-anggaran">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating total container -->
<div id="floating-total" class="p-3 text-white rounded shadow bg-primary" style="position:fixed;left:20px;bottom:20px;z-index:9999;min-width:220px;font-size:1.2em;opacity:0.95;">
    Total Pagu: <span id="floating-total-value">Rp0</span>
    <button id="copy-total-btn" class="btn btn-light btn-sm ms-2" title="Copy ke clipboard">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
            <path d="M10 1.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1A1.5 1.5 0 0 0 4.5 3H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-.5A1.5 1.5 0 0 0 10 1.5zm-4 0A.5.5 0 0 1 6.5 1h3a.5.5 0 0 1 .5.5v1h-4v-1z"/>
        </svg>
    </button>
    <span id="copy-feedback" class="ms-2" style="display:none;">Copied!</span>
</div>

<style>
#floating-total {
    transition: background 0.2s;
}
#data-table {
    font-size: 12px;
}
#data-table th, #data-table td {
    padding: 4px 8px !important;
    vertical-align: middle;
}
.max-ellipsis {
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: middle;
}
</style>

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

    let dataTableInstance = null;
    function updateTable(data) {
        // Siapkan data array untuk DataTables
        let dataArray = data.map(function(item) {
            // Trim nama sub kegiatan dan uraian
            let namaSubKeg = (item.nama_sub_kegiatan ?? '-');
            let namaSubKegShort = namaSubKeg.length > 40 ? namaSubKeg.slice(0, 40) + '…' : namaSubKeg;
            let uraian = (item.nama_standar_harga ?? '-');
            let uraianShort = uraian.length > 40 ? uraian.slice(0, 40) + '…' : uraian;
            return [
                item.kode_sub_kegiatan ?? '-',
                `<span class='max-ellipsis' title='${namaSubKeg.replace(/'/g, '&apos;')}'>${namaSubKegShort}</span>`,
                item.kode_rekening ?? '-',
                item.nama_rekening ?? '-',
                item.kode_standar_harga ?? '-',
                `<span class='max-ellipsis' title='${uraian.replace(/'/g, '&apos;')}'>${uraianShort}</span>`,
                '<span class="text-end">' + formatRupiah(item.anggaran) + '</span>'
            ];
        });
        if (!dataTableInstance) {
            dataTableInstance = $('#data-table').DataTable({
                data: dataArray,
                paging: false,
                searching: true,
                ordering: true,
                order: [[0, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                initComplete: function () {
                    this.api().columns().every(function () {
                        var that = this;
                        $('input', this.header()).on('keyup change clear', function () {
                            if (that.search() !== this.value) {
                                that.search(this.value).draw();
                            }
                        });
                    });
                }
            });
            dataTableInstance.on('draw', function() {
                updateFooterTotal();
            });
        } else {
            dataTableInstance.clear();
            dataTableInstance.rows.add(dataArray).draw();
        }
        updateFooterTotal();
    }
    function updateFooterTotal() {
        let total = 0;
        if (dataTableInstance) {
            dataTableInstance.rows({ search: 'applied' }).every(function() {
                let data = this.data();
                // Kolom pagu ada di index ke-6
                let pagu = $(data[6]).text().replace(/[^0-9,-]+/g,"").replace(",",".");
                total += parseFloat(pagu) || 0;
            });
        }
        $('#total-anggaran').text(formatRupiah(total));
        $('#floating-total-value').text(formatRupiah(total));
    }

    // Event Submit Filter Form
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        let tahapan = $('#tahapan').val();
        let opd = $('#opd').val();
        if (!opd) {
            // Kosongkan tabel jika OPD belum dipilih
            updateTable([]);
            return;
        }
        fetchData(tahapan, opd);
    });

    // Event Reset Filter
    $('#reset-filter').on('click', function(e) {
        e.preventDefault();
        $('#filter-form')[0].reset();
        // Kosongkan tabel jika reset
        updateTable([]);
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

    // Initial load: kosongkan tabel
    updateTable([]);

    // DataTables custom filter untuk exclude uraian
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var uraianFilter = $('#uraian-filter').val().toLowerCase();
        var uraianExclude = $('#uraian-exclude').is(':checked');
        var uraianValue = (data[5] || '').toLowerCase();
        if (uraianFilter) {
            if (uraianExclude) {
                // Exclude: jika uraianValue mengandung filter, hide row
                return uraianValue.indexOf(uraianFilter) === -1;
            } else {
                // Include: default behavior
                return uraianValue.indexOf(uraianFilter) !== -1;
            }
        }
        return true;
    });
    // Cegah sorting pada input filter (hanya event mousedown dan click)
    $(document).on('mousedown click', 'thead input', function(e) {
        e.stopImmediatePropagation();
        e.stopPropagation();
    });
    // Jika pengecualian dicentang, kosongkan filter kolom DataTables untuk kolom Uraian
    $(document).on('change', '#uraian-exclude', function() {
        if (this.checked && dataTableInstance) {
            dataTableInstance.column(5).search('').draw();
        }
        updateFooterTotal();
    });
    // Trigger redraw dan update total saat filter kolom lain digunakan
    $(document).on('keyup change', '.column-search', function() {
        if (dataTableInstance) {
            dataTableInstance.draw();
            updateFooterTotal();
        }
    });

    $('#copy-total-btn').on('click', function() {
        let value = $('#floating-total-value').text();
        // Ambil hanya angka dan koma/desimal, hilangkan Rp, titik, spasi
        value = value.replace(/[^0-9,]/g, '').replace(',', '.');
        navigator.clipboard.writeText(value).then(function() {
            $('#copy-feedback').fadeIn(200).delay(800).fadeOut(400);
        });
    });
});

// Function untuk kalkulasi (akan diimplementasikan nanti)
function calculate(kodeRekening) {
    alert('Fitur kalkulasi untuk rekening ' + kodeRekening + ' akan diimplementasikan');
}
</script>
@endsection 