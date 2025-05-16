<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model
{
    use HasFactory;

    protected $table = 'realisasis';

    protected $fillable = [
        'kode_opd',
        'periode',
        'kode_rekening',
        'uraian',
        'anggaran',
        'realisasi',
        'persentase',
        'realisasi_ly'
    ];

    protected $casts = [
        'periode' => 'date',
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
        'realisasi_ly' => 'decimal:2'
    ];
} 