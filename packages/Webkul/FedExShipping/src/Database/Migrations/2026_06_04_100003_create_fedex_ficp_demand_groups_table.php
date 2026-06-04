<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fedex_ficp_demand_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 64)->unique();
            $table->decimal('base_rate', 6, 2);
            $table->decimal('per_kg_rate', 6, 4);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fedex_ficp_demand_groups');
    }
};
