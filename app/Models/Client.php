<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model {
    use SoftDeletes;

    protected $table = 'clients';
    protected $primaryKey = 'id_cliente';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nombre','email','telefono','direccion','identificacion','activo'];

    public function sales() {
        return $this->hasMany(Sale::class, 'id_cliente', 'id_cliente');
    }
}
