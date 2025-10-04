<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم وصفي للشاشة، مثل "شاشة مدخل الطابق الأول"
            $table->string('screen_code')->unique(); // الكود البسيط والفريد للشاشة مثل "101"
            $table->enum('orientation', ['landscape', 'portrait']); // اتجاه الشاشة: عرضي أو طولي
            $table->string('resolution')->nullable(); // الدقة مثل "1920x1080"
            $table->boolean('is_active')->default(true); // لتفعيل أو تعطيل الشاشة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};