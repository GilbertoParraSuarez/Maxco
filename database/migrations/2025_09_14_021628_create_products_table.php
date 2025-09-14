<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $t) {
            $t->id('id_producto');                       // PK (ANEXO)
            $t->string('nombre');                        // (ANEXO)
            $t->text('descripcion')->nullable();         // (ANEXO)
            $t->decimal('precio', 12, 2);                // (ANEXO)
            $t->unsignedInteger('stock')->default(0);    // (ANEXO)
            $t->string('categoria')->nullable();         // (ANEXO)
            // Esenciales:
            $t->string('sku', 50)->nullable()->unique();
            $t->boolean('activo')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->index(['nombre','categoria']);
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
