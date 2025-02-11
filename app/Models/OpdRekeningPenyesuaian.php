<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpdRekeningPenyesuaian extends Model
{
    use HasFactory;

    protected $table = 'opd_rekening_penyesuaian';

    protected $fillable = [
        'kode_opd',
        'kode_rekening',
        'persentase_penyesuaian'
    ];
    public $timestamps = true;
}
