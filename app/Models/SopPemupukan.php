<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopPemupukan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_penanaman',
        'hari_setelah_tanam',
        'tinggi_tanaman_minimal_mm',
        'tinggi_tanaman_maksimal_mm',
        'id_user',
        'id_penanaman',
        'jumlah_pupuk_ml',
        'jumlah_air_ml',
        'created_at',
        'updated_at'
    ];

    // $guarded bisa digunakan sebagai alternatif untuk melindungi field tertentu
    // protected $guarded = [];
    protected $table = 'sop_pemupukan';
}
