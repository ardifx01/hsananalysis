<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'kode_akun',
        'nama_akun',
        'kode_opd',
        'nama_opd',
        'uraian',
        'keterangan',
        'pagu',
        'tahapan_id',
        'tanggal_upload',
    ];

    protected $dates = ['tanggal_upload'];

    public function tahapan()
    {
        return $this->belongsTo(Tahapan::class);
    }
}
