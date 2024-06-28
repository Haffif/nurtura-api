<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_user',
        'id_device',
        'isAssigned',
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $table = 'user_device';
}
