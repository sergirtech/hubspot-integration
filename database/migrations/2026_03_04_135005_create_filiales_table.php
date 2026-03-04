<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filiales', function (Blueprint $table) {
            $table->id();

            // Nombre identificativo de la filial (mexico, colombia, espana...)
            $table->string('nombre')->unique();

            // URL base de la API de BMG para esta filial
            $table->string('url');

            // Credenciales de acceso a BMG
            $table->string('usuario');
            $table->string('password');

            // Si la filial está activa o no
            $table->boolean('activa')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filiales');
    }
};
