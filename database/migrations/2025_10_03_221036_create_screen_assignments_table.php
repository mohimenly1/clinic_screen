<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screen_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screen_id')->unique()->constrained()->onDelete('cascade'); // كل شاشة لها تخصيص واحد فقط
            $table->morphs('assignable'); // سيضيف عمودين: assignable_id و assignable_type
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_assignments');
    }
};