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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Relación con la publicación vendida
            $table->unsignedBigInteger('publication_id');

            // Usuario que compra (rol: customer en Auth Service)
            $table->unsignedBigInteger('customer_id');

            // Usuario que vende (rol: seller en Auth Service)
            $table->unsignedBigInteger('seller_id');

            // Datos de la venta
            $table->decimal('sale_price', 12, 2);
            $table->timestamp('sale_date')->useCurrent();

            // FK local (solo para publications)
            $table->foreign('publication_id')
                ->references('id')
                ->on('publications')
                ->onDelete('cascade');

            // Índices para búsquedas rápidas
            $table->index('customer_id');
            $table->index('seller_id');

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
