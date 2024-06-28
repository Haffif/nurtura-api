<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'id_device',
        'id_user',
        'tipe_intruksi',
        'volume',
        'durasi',
        'start',
        'end',
        'isActive',
        'isPending',
        'mode',
        'created_at',
        'updated_at'
    ];

    // $guarded bisa digunakan sebagai alternatif untuk melindungi field tertentu
    // protected $guarded = [];
    protected $table = 'device';
}
