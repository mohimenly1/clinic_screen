<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // تعديل عمود نوع الملف ليقبل 'audio'
        Schema::table('media_items', function (Blueprint $table) {
            $table->enum('file_type', ['image', 'video', 'audio'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('media_items', function (Blueprint $table) {
            $table->enum('file_type', ['image', 'video'])->change();
        });
    }
};
