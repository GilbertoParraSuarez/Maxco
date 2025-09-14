<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $table = 'vendors';
    protected $primaryKey = 'id_vendedor';
    public $timestamps = true;

    protected $fillable = [
        'nombre', 'email', 'telefono', 'direccion', 'identificacion', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
