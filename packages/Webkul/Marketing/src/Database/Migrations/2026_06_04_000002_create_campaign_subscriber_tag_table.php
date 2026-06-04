<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_subscriber_tag', function (Blueprint $table) {
            $table->unsignedInteger('campaign_id');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('campaign_id')->references('id')->on('marketing_campaigns')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('subscriber_tags')->onDelete('cascade');

            $table->primary(['campaign_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_subscriber_tag');
    }
};
