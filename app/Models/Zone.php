<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model {
    use SoftDeletes;

    protected $table = 'zones';
    protected $primaryKey = 'id_zona';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nombre_zona','descripcion','activo'];

    public function sales() {
        return $this->hasMany(Sale::class, 'id_zona', 'id_zona');
    }
}
