<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleDetail extends Model
{
    use SoftDeletes;

    protected $table = 'sale_details';
    protected $primaryKey = 'id_detalle_venta';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'descuento',
        'impuesto',
        'unidad',
        // OJO: total_linea es STORED GENERATED — no va en fillable
    ];

    protected $casts = [
        'cantidad'        => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'descuento'       => 'decimal:2',
        'impuesto'        => 'decimal:2',
        'total_linea'     => 'decimal:2', // solo para lectura
    ];

    public function sale()    { return $this->belongsTo(Sale::class,    'id_venta',    'id_venta'); }
    public function product() { return $this->belongsTo(Product::class, 'id_producto', 'id_producto'); }
}
