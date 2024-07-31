<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_user',
        'nama_lahan',
        'deskripsi',
        'latitude',
        'longitude',
        'isAssigned',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';
    protected $table = 'lahan';

}
