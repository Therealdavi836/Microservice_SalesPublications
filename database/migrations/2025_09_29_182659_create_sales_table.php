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

            // Datos de la venta
            $table->decimal('sale_price', 12, 2);
            $table->timestamp('sale_date')->useCurrent();
            // FK local (publication_id → publications)
            $table->foreign('publication_id')
                  ->references('id')
                  ->on('publications')
                  ->onDelete('cascade');

            // customer_id viene del micro de Auth, así que solo indexamos
            $table->index('customer_id');

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
