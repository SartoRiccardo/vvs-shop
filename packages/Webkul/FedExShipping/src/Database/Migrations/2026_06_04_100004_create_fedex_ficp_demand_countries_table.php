<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fedex_ficp_demand_countries', function (Blueprint $table) {
            $table->id();
            $table->char('country_code', 2)->unique();
            $table->foreignId('group_id')->constrained('fedex_ficp_demand_groups');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fedex_ficp_demand_countries');
    }
};
