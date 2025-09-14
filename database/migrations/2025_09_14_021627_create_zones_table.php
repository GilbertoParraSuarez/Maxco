<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('zones', function (Blueprint $t) {
            $t->id('id_zona');                    // PK (ANEXO)
            $t->string('nombre_zona');            // requerido (ANEXO)
            $t->string('descripcion')->nullable();// opcional (ANEXO)
            // Esenciales:
            $t->boolean('activo')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->unique('nombre_zona');
        });
    }
    public function down(): void { Schema::dropIfExists('zones'); }
};
