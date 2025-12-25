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
        // Migration تم إلغاؤها - نستخدم الأقسام بدلاً من العيادات
        // Schema::table('schedules', function (Blueprint $table) {
        //     $table->foreignId('clinic_id')->nullable()->after('doctor_id')->constrained()->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migration تم إلغاؤها - نستخدم الأقسام بدلاً من العيادات
        // Schema::table('schedules', function (Blueprint $table) {
        //     $table->dropForeign(['clinic_id']);
        //     $table->dropColumn('clinic_id');
        // });
    }
};
