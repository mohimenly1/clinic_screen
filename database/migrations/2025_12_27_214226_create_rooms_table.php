<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->onDelete('cascade');
            $table->string('name'); // اسم الغرفة/العيادة مثل "عيادة 101" أو "صيدلية"
            $table->string('room_number')->nullable(); // رقم الغرفة مثل "101" أو "A12"
            $table->enum('room_type', ['clinic', 'pharmacy', 'lab', 'reception', 'restroom', 'elevator', 'stairs', 'other'])->default('clinic');
            $table->decimal('map_x', 8, 2)->nullable(); // الإحداثي X في الخريطة (0-100 نسبة مئوية)
            $table->decimal('map_y', 8, 2)->nullable(); // الإحداثي Y في الخريطة (0-100 نسبة مئوية)
            $table->text('description')->nullable(); // وصف الغرفة (اختياري)
            $table->boolean('is_active')->default(true); // تفعيل/تعطيل الغرفة
            $table->timestamps();
            
            // فهرس مركب للبحث السريع
            $table->index(['floor_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
