<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sale_details', function (Blueprint $t) {
            $t->id('id_detalle_venta');                                 // PK (ANEXO)

            // FKs (ANEXO: id_venta, id_producto)
            $t->foreignId('id_venta')->constrained('sales', 'id_venta')->cascadeOnDelete();
            $t->foreignId('id_producto')->constrained('products', 'id_producto')->restrictOnDelete();

            $t->unsignedInteger('cantidad');                            // (ANEXO)
            $t->decimal('precio_unitario', 12, 2);                      // (ANEXO)
            $t->decimal('subtotal', 14, 2);                             // (ANEXO)

            // Esenciales de línea:
            $t->decimal('descuento', 14, 2)->default(0);
            $t->decimal('impuesto', 14, 2)->default(0);
            $t->decimal('total_linea', 14, 2)->storedAs('subtotal - descuento + impuesto'); // cálculo
            $t->string('unidad', 20)->default('UND');

            $t->timestamps();
            $t->softDeletes();

            $t->index(['id_venta','id_producto']);
            // Si no quieres repetir el mismo producto en una venta:
            // $t->unique(['id_venta','id_producto']);
        });
    }
    public function down(): void { Schema::dropIfExists('sale_details'); }
};
