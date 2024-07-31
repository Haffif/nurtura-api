<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_tinggi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_penanaman',
        'nama_penanaman',
        'tinggi_tanaman',
        'jenis_tanaman',
        'tanggal_pencatatan',
    ];

    protected $primaryKey = 'id';
    protected $table = 'log_tinggi';

}
