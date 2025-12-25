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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم الدور (مثل: screen_manager)
            $table->string('display_name'); // الاسم المعروض (مثل: مدير الشاشات)
            $table->string('description')->nullable(); // وصف الدور
            $table->boolean('is_system')->default(false); // هل هو دور نظام (لا يمكن حذفه)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
