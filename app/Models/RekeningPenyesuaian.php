<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningPenyesuaian extends Model
{
    use HasFactory; 

    protected $table = 'rekening_penyesuaian';

    use HasFactory;

    protected $fillable = ['kode_rekening', 'nama_rekening', 'persentase_penyesuaian'];
}
