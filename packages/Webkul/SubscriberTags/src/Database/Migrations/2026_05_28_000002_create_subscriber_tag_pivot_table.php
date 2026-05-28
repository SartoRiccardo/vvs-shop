<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_tag', function (Blueprint $table) {
            $table->unsignedInteger('subscriber_id');
            $table->unsignedBigInteger('tag_id');
            $table->primary(['subscriber_id', 'tag_id']);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('subscriber_id')->references('id')->on('subscribers_list')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('subscriber_tags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_tag');
    }
};
