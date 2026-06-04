<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fedex_ficp_zones', function (Blueprint $table) {
            $table->id();
            $table->string('country_name');
            $table->char('country_code', 2)->unique();
            $table->char('zone_code', 2);
            $table->boolean('is_european_zone')->default(false);
            $table->boolean('is_eu')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fedex_ficp_zones');
    }
};
