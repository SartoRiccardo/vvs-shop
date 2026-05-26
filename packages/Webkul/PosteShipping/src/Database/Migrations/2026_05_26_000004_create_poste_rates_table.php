<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poste_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('poste_zones')->cascadeOnDelete();
            $table->decimal('max_weight_kg', 8, 4);
            $table->decimal('cost_eur', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poste_rates');
    }
};
