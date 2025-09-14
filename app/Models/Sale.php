<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $table = 'sales';
    protected $primaryKey = 'id_venta';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_cliente','id_vendedor','id_zona','fecha',
        'monto_total','numero_documento','metodo_pago','moneda','tasa_cambio',
        'impuesto_total','descuento_total','estado','observaciones'
    ];

    protected $casts = [
        'fecha'           => 'datetime',
        'monto_total'     => 'decimal:2',
        'impuesto_total'  => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'tasa_cambio'     => 'decimal:4',
    ];

    public function customer() { return $this->belongsTo(Client::class,  'id_cliente',  'id_cliente'); }
    public function vendor()   { return $this->belongsTo(Vendor::class,  'id_vendedor', 'id_vendedor'); }
    public function zone()     { return $this->belongsTo(Zone::class,    'id_zona',     'id_zona'); }
    public function details()  { return $this->hasMany(SaleDetail::class,'id_venta',     'id_venta'); }
}
