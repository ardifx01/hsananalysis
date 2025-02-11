<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataAnggaran extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'kode_skpd', 'nama_skpd',
        'kode_sub_kegiatan', 'nama_sub_kegiatan',
        'kode_rekening', 'nama_rekening',
        'pagu', 'tipe_data'
    ];
}
