<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_item_playlist', function (Blueprint $table) {
            $table->foreignId('media_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('order')->default(0); // لترتيب ظهور الإعلانات
            $table->primary(['media_item_id', 'playlist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_item_playlist');
    }
};