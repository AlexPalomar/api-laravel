<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Empleados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('empleados', function (Blueprint $table) {
            $table->increments('ididentificacion');
            $table->string('identificacion', 10);
            $table->string('nombre', 100);
            $table->string('cargo', 100);
            $table->string('correo', 100);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('empleados');
    }
}
