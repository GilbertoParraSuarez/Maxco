<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model {
    use SoftDeletes;

    protected $table = 'vendors';
    protected $primaryKey = 'id_vendedor';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nombre','email','telefono','activo'];

    public function sales() {
        return $this->hasMany(Sale::class, 'id_vendedor', 'id_vendedor');
    }
}
