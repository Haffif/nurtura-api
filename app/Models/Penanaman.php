<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penanaman extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_user',
        'id_lahan',
        'id_device',
        'nama_penanaman',
        'jenis_tanaman',
        'keterangan',
        'tanggal_tanam',
        'tanggal_panen',
        'hst',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';
    protected $table = 'penanaman';

}
