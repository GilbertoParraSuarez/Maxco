<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        DB::table('vendors')->insert([
            ['nombre'=>'Vendedor A','email'=>'vend.a@example.com','telefono'=>null,'activo'=>true],
            ['nombre'=>'Vendedor B','email'=>'vend.b@example.com','telefono'=>null,'activo'=>true],
        ]);

        DB::table('zones')->insert([
            ['nombre_zona'=>'Norte','descripcion'=>null,'activo'=>true],
            ['nombre_zona'=>'Centro','descripcion'=>null,'activo'=>true],
            ['nombre_zona'=>'Sur','descripcion'=>null,'activo'=>true],
        ]);

        DB::table('clients')->insert([
            ['nombre'=>'Cliente 1','email'=>'c1@example.com','telefono'=>null,'direccion'=>null,'identificacion'=>null,'activo'=>true],
            ['nombre'=>'Cliente 2','email'=>'c2@example.com','telefono'=>null,'direccion'=>null,'identificacion'=>null,'activo'=>true],
        ]);

        DB::table('products')->insert([
            ['nombre'=>'Prod 1','descripcion'=>null,'precio'=>10,'stock'=>100,'categoria'=>null,'sku'=>'P1','activo'=>true],
            ['nombre'=>'Prod 2','descripcion'=>null,'precio'=>20,'stock'=>50,'categoria'=>null,'sku'=>'P2','activo'=>true],
        ]);
    }
}
