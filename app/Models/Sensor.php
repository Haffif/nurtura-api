<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_device',
        'id_plant',
        'suhu',
        'kelembapan_udara',
        'kelembapan_tanah',
        'ph_tanah',
        'nitrogen',
        'fosfor',
        'kalium',
        'timestamp_pengukuran',
        'updated_at'
    ];

    // $guarded bisa digunakan sebagai alternatif untuk melindungi field tertentu
    // protected $guarded = [];
    protected $table = 'data_sensor';
}
