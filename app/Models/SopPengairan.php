<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopPengairan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_penanaman',
        'temp_max',
        'temp_min',
        'humidity_max',
        'humidity_min',
        'soil_max',
        'soil_min',
        'created_at',
        'updated_at'
    ];

    // $guarded bisa digunakan sebagai alternatif untuk melindungi field tertentu
    // protected $guarded = [];
    protected $table = 'sop_pengairan';
}
