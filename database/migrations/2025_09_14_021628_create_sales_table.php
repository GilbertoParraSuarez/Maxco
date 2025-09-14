<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sales', function (Blueprint $t) {
            $t->id('id_venta');                                        // PK (ANEXO)

            // FKs (ANEXO: id_cliente, id_vendedor, id_zona)
            $t->foreignId('id_cliente')->constrained('clients', 'id_cliente')->cascadeOnDelete();
            $t->foreignId('id_vendedor')->constrained('vendors', 'id_vendedor')->cascadeOnDelete();
            $t->foreignId('id_zona')->constrained('zones', 'id_zona')->cascadeOnDelete();

            $t->dateTime('fecha');                                     // (ANEXO)
            $t->decimal('monto_total', 14, 2)->default(0);             // (ANEXO)

            // Esenciales (documento y contabilidad)
            $t->string('numero_documento', 50)->nullable();            // factura/nota/etc.
            $t->enum('metodo_pago', ['efectivo','tarjeta','transferencia','otros'])->default('efectivo');
            $t->string('moneda', 3)->default('USD');
            $t->decimal('tasa_cambio', 12, 6)->default(1);
            $t->decimal('impuesto_total', 14, 2)->default(0);
            $t->decimal('descuento_total', 14, 2)->default(0);
            $t->enum('estado', ['borrador','confirmada','anulada'])->default('confirmada');
            $t->text('observaciones')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->index(['fecha','id_zona','id_vendedor']);
            $t->unique(['numero_documento','id_vendedor']); // opcional según negocio
        });
    }
    public function down(): void { Schema::dropIfExists('sales'); }
};
