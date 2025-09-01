# Rekapitulasi Struktur Semua OPD

## Deskripsi
Fitur ini menampilkan rekapitulasi struktur belanja untuk semua OPD dalam format tabel yang memudahkan perbandingan antar OPD.

## Fitur Utama

### 1. Tabel Rekapitulasi Struktur Belanja
- Menampilkan semua OPD dalam satu tabel
- Kolom struktur belanja sesuai dengan kode rekening 3 segmen (contoh: 5.1.01, 5.1.02, dll)
- Total anggaran per OPD
- Total keseluruhan per struktur belanja

### 2. Tabel Rekapitulasi Struktur Belanja
- Menampilkan semua OPD dalam satu tabel tanpa pagination
- Kolom struktur belanja sesuai dengan kode rekening 3 segmen
- Total anggaran per OPD
- Total keseluruhan per struktur belanja

### 3. Filter Tahapan
- Filter berdasarkan tahapan anggaran
- Menampilkan data sesuai tahapan yang dipilih

### 4. Export dan Print
- Export ke Excel (.xlsx) dengan formatting yang rapi
- Export ke PDF dengan orientasi landscape
- Print langsung dari browser
- Tabel tanpa pagination untuk melihat semua data sekaligus

## Cara Akses

### Melalui Sidebar
1. Klik menu "Rekapitulasi Struktur OPD" di sidebar

### Melalui Halaman Simulasi Perubahan
1. Buka halaman "Simulasi Perubahan Anggaran"
2. Klik tombol "Rekapitulasi Struktur Semua OPD"

## URL
```
/simulasi/rekapitulasi-struktur-opd
```

## Route Name
```
simulasi.rekapitulasi-struktur-opd
simulasi.rekapitulasi-struktur-opd.export-excel
```

## Controller Method
```php
SimulasiPerubahanController::rekapitulasiStrukturOpd()
SimulasiPerubahanController::exportExcel()
```

## View
```
resources/views/simulasi-perubahan/rekapitulasi-struktur-opd.blade.php
```

## Struktur Data

### Data yang Ditampilkan
1. **No** - Nomor urut
2. **Nama OPD** - Nama SKPD/OPD
3. **Struktur Belanja** - Kolom untuk setiap kode rekening 3 segmen
4. **Total Anggaran** - Total anggaran per OPD

### Perhitungan
- **Anggaran**: Diambil dari tabel `data_anggarans` berdasarkan tahapan dan OPD
- **Realisasi**: Diambil dari tabel `realisasis` berdasarkan OPD
- **Penyesuaian**: Diambil dari tabel `simulasi_penyesuaian_anggarans` berdasarkan OPD
- **Proyeksi**: (Anggaran - Realisasi) + Penyesuaian

## Kode Rekening yang Ditampilkan
Hanya menampilkan kode rekening yang:
- Diawali dengan angka 5 (belanja)
- Memiliki 3 segmen (contoh: 5.1.01, 5.1.02, dll)

## Contoh Output
```
| No | Nama OPD | 5.1.01 | 5.1.02 | 5.1.03 | Total |
|----|----------|--------|--------|--------|-------|
| 1  | Dinas A  | 1000000| 500000 | 300000 | 1800000|
| 2  | Dinas B  | 800000 | 400000 | 200000 | 1400000|
```

## Dependencies
- Maatwebsite Excel untuk export Excel
- jsPDF untuk export PDF
- Bootstrap untuk styling
- jQuery untuk JavaScript functionality
