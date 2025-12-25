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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم الصلاحية (مثل: manage_screens)
            $table->string('display_name'); // الاسم المعروض (مثل: إدارة الشاشات)
            $table->string('description')->nullable(); // وصف الصلاحية
            $table->string('category')->nullable(); // فئة الصلاحية (مثل: screens, media, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
