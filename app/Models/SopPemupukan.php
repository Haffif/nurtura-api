<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopPemupukan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_penanaman',
        'hst',
        'tinggi_tanaman_minimal_mm',
        'tinggi_tanaman_maksimal_mm',
        'created_at',
        'updated_at'
    ];

    // $guarded bisa digunakan sebagai alternatif untuk melindungi field tertentu
    // protected $guarded = [];
    protected $table = 'sop_pemupukan';
}
