<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendors', function (Blueprint $t) {
            $t->id('id_vendedor');                 // PK (ANEXO: id_vendedor)
            $t->string('nombre');                  // requerido
            $t->string('email')->nullable()->unique();
            // Esenciales:
            $t->string('telefono', 50)->nullable();
            $t->boolean('activo')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->index('nombre');
        });
    }
    public function down(): void { Schema::dropIfExists('vendors'); }
};
