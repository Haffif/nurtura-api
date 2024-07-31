<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fertilizer extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'id_device',
        'isOptimal',
        'message',
        'created_at',
        'updated_at'
    ];

    // $guarded bisa digunakan sebagai alternatif untuk melindungi field tertentu
    // protected $guarded = [];
    protected $table = 'fertilizer';
}
