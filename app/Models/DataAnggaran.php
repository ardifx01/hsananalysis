<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DataAnggaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_skpd',
        'nama_skpd',
        'kode_sub_unit',
        'nama_sub_unit',
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
        'kode_rekening',
        'nama_rekening',
        'pagu',
        'tahapan_id', // Tambahkan tahapan_id
        'tanggal_upload', // Tambahkan tanggal_upload
    ];

    protected $dates = ['tanggal_upload']; // Pastikan tanggal_upload di-cast sebagai date

    public function tahapan()
    {
        return $this->belongsTo(Tahapan::class);
    }
}
