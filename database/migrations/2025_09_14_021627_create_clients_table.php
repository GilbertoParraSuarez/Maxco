<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $t) {
            $t->id('id_cliente');                // PK (ANEXO)
            $t->string('nombre');                // requerido (ANEXO)
            $t->string('email')->nullable()->unique();     // (ANEXO)
            $t->string('telefono', 50)->nullable();        // (ANEXO)
            $t->string('direccion')->nullable();           // (ANEXO)
            // Esenciales:
            $t->string('identificacion', 20)->nullable();  // CI/RUC
            $t->boolean('activo')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->index('nombre');
        });
    }
    public function down(): void { Schema::dropIfExists('clients'); }
};
