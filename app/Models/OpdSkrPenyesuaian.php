<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpdSkrPenyesuaian extends Model
{
    protected $table = 'opd_skr_penyesuaian';

    protected $fillable = [
        'kode_opd',
        'kode_sub_kegiatan',
        'kode_rekening',
        'persentase'
    ];
}
