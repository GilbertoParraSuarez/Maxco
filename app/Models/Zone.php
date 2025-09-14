<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';
    protected $primaryKey = 'id_zona';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre_zona',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
