<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulasiPenyesuaianAnggaran extends Model
{
    protected $table = 'simulasi_penyesuaian_anggaran';
    protected $fillable = [
        'kode_opd', 'kode_rekening', 'operasi', 'nilai', 'keterangan'
    ];
}
