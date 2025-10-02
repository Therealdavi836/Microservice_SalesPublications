<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();

            // Usuario dueño de la publicación (FK hacia Microservice_Authentication)
            $table->unsignedBigInteger('user_id');

            // Referencia al vehículo (se valida contra el micro de Catálogo)
            $table->string('vehicle_id');

            // Datos de la publicación
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);

            // Estado de la publicación
            $table->enum('status', ['activo', 'inactivo', 'vendido'])->default('activo');

            // Indexes y FK (solo local)
            $table->index('user_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
