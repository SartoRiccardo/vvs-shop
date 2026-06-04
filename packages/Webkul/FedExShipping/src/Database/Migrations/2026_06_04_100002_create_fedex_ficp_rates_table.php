<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fedex_ficp_rates', function (Blueprint $table) {
            $table->id();
            $table->char('zone_code', 2);
            $table->decimal('weight_max', 5, 2)->nullable(); // NULL = open-ended tail row
            $table->decimal('flat_rate', 8, 2);
            $table->decimal('per_kg_rate', 8, 4)->default(0); // used only on tail row

            $table->index('zone_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fedex_ficp_rates');
    }
};
