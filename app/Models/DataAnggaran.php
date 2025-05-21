<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DataAnggaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'kode_urusan',
        'nama_urusan',
        'kode_skpd',
        'nama_skpd',
        'kode_sub_unit',
        'nama_sub_unit',
        'kode_bidang_urusan',
        'nama_bidang_urusan',
        'kode_program',
        'nama_program',
        'kode_kegiatan',
        'nama_kegiatan',
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
        'kode_sumber_dana',
        'nama_sumber_dana',
        'kode_rekening',
        'nama_rekening',
        'kode_standar_harga',
        'nama_standar_harga',
        'pagu',
        'tahapan_id',
        'tanggal_upload',
    ];

    protected $dates = ['tanggal_upload']; // Pastikan tanggal_upload di-cast sebagai date

    public function tahapan()
    {
        return $this->belongsTo(Tahapan::class);
    }
}
