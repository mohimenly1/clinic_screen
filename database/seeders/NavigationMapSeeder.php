<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Room;
use App\Models\NavigationPath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NavigationMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف البيانات القديمة إذا كانت موجودة
        // استخدام delete() بدلاً من truncate() لضمان التوافق مع SQLite
        NavigationPath::query()->delete();
        Room::query()->delete();
        Floor::query()->delete();

        // إنشاء الطابق الأول
        $floor = Floor::create([
            'name' => 'الطابق الأول',
            'floor_number' => 1,
            'description' => 'الطابق الرئيسي للعيادة يحتوي على جميع العيادات والخدمات',
            'display_order' => 1,
        ]);

        // إنشاء الغرف حسب المخطط
        // 1. الباب الرئيسي (في الأسفل في المنتصف)
        $entrance = Room::create([
            'floor_id' => $floor->id,
            'name' => 'الباب الرئيسي',
            'room_number' => 'ENT-001',
            'room_type' => 'reception',
            'map_x' => 50.00, // في المنتصف
            'map_y' => 90.00, // في الأسفل
            'description' => 'المدخل الرئيسي للعيادة',
            'is_active' => true,
        ]);

        // 2. مكتب الاستعلامات (مقابل الباب الرئيسي مباشرة)
        $reception = Room::create([
            'floor_id' => $floor->id,
            'name' => 'مكتب الاستعلامات',
            'room_number' => 'REC-001',
            'room_type' => 'reception',
            'map_x' => 50.00, // في المنتصف
            'map_y' => 70.00, // فوق الباب الرئيسي
            'description' => 'مكتب الاستعلامات الرئيسي',
            'is_active' => true,
        ]);

        // 3. الممر الطولي (على الجانب الأيسر من مكتب الاستعلامات)
        $corridor = Room::create([
            'floor_id' => $floor->id,
            'name' => 'الممر الرئيسي',
            'room_number' => 'COR-001',
            'room_type' => 'other',
            'map_x' => 20.00, // على اليسار
            'map_y' => 50.00, // في المنتصف عمودياً
            'description' => 'الممر الطولي المؤدي إلى الصيدلية',
            'is_active' => true,
        ]);

        // 4. الشاشة رقم 1 (قبل الصيدلية على اليسار)
        $screen1 = Room::create([
            'floor_id' => $floor->id,
            'name' => 'الشاشة رقم 1',
            'room_number' => 'SCR-001',
            'room_type' => 'other',
            'map_x' => 15.00, // على اليسار
            'map_y' => 50.00, // في نفس مستوى الممر
            'description' => 'شاشة العرض التفاعلية رقم 1',
            'is_active' => true,
        ]);

        // 5. الصيدلية (في نهاية الممر على اليسار)
        $pharmacy = Room::create([
            'floor_id' => $floor->id,
            'name' => 'الصيدلية',
            'room_number' => 'PHA-001',
            'room_type' => 'pharmacy',
            'map_x' => 10.00, // في أقصى اليسار
            'map_y' => 30.00, // في الأعلى قليلاً
            'description' => 'صيدلية العيادة',
            'is_active' => true,
        ]);

        // 6. السلالم/المصعد (على جانب الصيدلية من جهة اليمين)
        $stairs = Room::create([
            'floor_id' => $floor->id,
            'name' => 'سلالم العيادات',
            'room_number' => 'ST-001',
            'room_type' => 'stairs',
            'map_x' => 30.00, // على يمين الصيدلية
            'map_y' => 30.00, // في نفس مستوى الصيدلية
            'description' => 'سلالم تؤدي إلى منطقة العيادات',
            'is_active' => true,
        ]);

        // 7. العيادات (موزعة في الأعلى)
        $clinics = [];
        $clinicNames = [
            'عيادة الباطنة',
            'عيادة الأطفال',
            'عيادة العظام',
            'عيادة الجلدية',
            'عيادة الأنف والأذن',
        ];

        $clinicXPositions = [50, 60, 70, 80, 90]; // موزعة أفقياً
        $clinicY = 15.00; // في الأعلى

        foreach ($clinicNames as $index => $name) {
            $clinics[] = Room::create([
                'floor_id' => $floor->id,
                'name' => $name,
                'room_number' => 'CLI-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'room_type' => 'clinic',
                'map_x' => $clinicXPositions[$index],
                'map_y' => $clinicY,
                'description' => $name,
                'is_active' => true,
            ]);
        }

        // إنشاء مسارات التنقل
        // من الباب الرئيسي إلى مكتب الاستعلامات
        NavigationPath::create([
            'from_room_id' => $entrance->id,
            'to_room_id' => $reception->id,
            'directions' => 'اتجه مباشرة للأمام حتى تصل إلى مكتب الاستعلامات',
            'path_coordinates' => [
                ['x' => 50, 'y' => 90],
                ['x' => 50, 'y' => 70],
            ],
            'estimated_time_seconds' => 30,
            'distance_meters' => 20,
        ]);

        // من مكتب الاستعلامات إلى الممر
        NavigationPath::create([
            'from_room_id' => $reception->id,
            'to_room_id' => $corridor->id,
            'directions' => 'اتجه يساراً حتى تصل إلى الممر الرئيسي',
            'path_coordinates' => [
                ['x' => 50, 'y' => 70],
                ['x' => 20, 'y' => 50],
            ],
            'estimated_time_seconds' => 45,
            'distance_meters' => 35,
        ]);

        // من الممر إلى الشاشة رقم 1
        NavigationPath::create([
            'from_room_id' => $corridor->id,
            'to_room_id' => $screen1->id,
            'directions' => 'اتجه يساراً قليلاً حتى تصل إلى الشاشة رقم 1',
            'path_coordinates' => [
                ['x' => 20, 'y' => 50],
                ['x' => 15, 'y' => 50],
            ],
            'estimated_time_seconds' => 15,
            'distance_meters' => 5,
        ]);

        // من الممر إلى الصيدلية
        NavigationPath::create([
            'from_room_id' => $corridor->id,
            'to_room_id' => $pharmacy->id,
            'directions' => 'اتجه يساراً ثم للأعلى حتى تصل إلى الصيدلية',
            'path_coordinates' => [
                ['x' => 20, 'y' => 50],
                ['x' => 10, 'y' => 30],
            ],
            'estimated_time_seconds' => 60,
            'distance_meters' => 30,
        ]);

        // من الصيدلية إلى السلالم
        NavigationPath::create([
            'from_room_id' => $pharmacy->id,
            'to_room_id' => $stairs->id,
            'directions' => 'اتجه يميناً حتى تصل إلى سلالم العيادات',
            'path_coordinates' => [
                ['x' => 10, 'y' => 30],
                ['x' => 30, 'y' => 30],
            ],
            'estimated_time_seconds' => 30,
            'distance_meters' => 20,
        ]);

        // من السلالم إلى كل عيادة
        foreach ($clinics as $clinic) {
            NavigationPath::create([
                'from_room_id' => $stairs->id,
                'to_room_id' => $clinic->id,
                'directions' => 'اصعد السلالم ثم اتجه إلى ' . $clinic->name,
                'path_coordinates' => [
                    ['x' => 30, 'y' => 30],
                    ['x' => $clinic->map_x, 'y' => $clinic->map_y],
                ],
                'estimated_time_seconds' => 90,
                'distance_meters' => 50,
            ]);
        }

        // مسارات إضافية للتنقل المباشر
        // من مكتب الاستعلامات إلى السلالم (مسار مباشر)
        NavigationPath::create([
            'from_room_id' => $reception->id,
            'to_room_id' => $stairs->id,
            'directions' => 'اتجه يساراً ثم للأعلى حتى تصل إلى سلالم العيادات',
            'path_coordinates' => [
                ['x' => 50, 'y' => 70],
                ['x' => 30, 'y' => 30],
            ],
            'estimated_time_seconds' => 75,
            'distance_meters' => 55,
        ]);

        // من مكتب الاستعلامات إلى كل عيادة (مسار مباشر)
        foreach ($clinics as $clinic) {
            NavigationPath::create([
                'from_room_id' => $reception->id,
                'to_room_id' => $clinic->id,
                'directions' => 'اتجه للأعلى حتى تصل إلى ' . $clinic->name,
                'path_coordinates' => [
                    ['x' => 50, 'y' => 70],
                    ['x' => $clinic->map_x, 'y' => $clinic->map_y],
                ],
                'estimated_time_seconds' => 120,
                'distance_meters' => 70,
            ]);
        }

        // مسارات عكسية للعودة
        // من مكتب الاستعلامات إلى الباب الرئيسي
        NavigationPath::create([
            'from_room_id' => $reception->id,
            'to_room_id' => $entrance->id,
            'directions' => 'اتجه للخلف حتى تصل إلى الباب الرئيسي',
            'path_coordinates' => [
                ['x' => 50, 'y' => 70],
                ['x' => 50, 'y' => 90],
            ],
            'estimated_time_seconds' => 30,
            'distance_meters' => 20,
        ]);

        // من الممر إلى مكتب الاستعلامات
        NavigationPath::create([
            'from_room_id' => $corridor->id,
            'to_room_id' => $reception->id,
            'directions' => 'اتجه يميناً حتى تصل إلى مكتب الاستعلامات',
            'path_coordinates' => [
                ['x' => 20, 'y' => 50],
                ['x' => 50, 'y' => 70],
            ],
            'estimated_time_seconds' => 45,
            'distance_meters' => 35,
        ]);

        // من الصيدلية إلى الممر
        NavigationPath::create([
            'from_room_id' => $pharmacy->id,
            'to_room_id' => $corridor->id,
            'directions' => 'اتجه للأسفل ثم يميناً حتى تصل إلى الممر',
            'path_coordinates' => [
                ['x' => 10, 'y' => 30],
                ['x' => 20, 'y' => 50],
            ],
            'estimated_time_seconds' => 60,
            'distance_meters' => 30,
        ]);

        // من السلالم إلى الصيدلية
        NavigationPath::create([
            'from_room_id' => $stairs->id,
            'to_room_id' => $pharmacy->id,
            'directions' => 'اتجه يساراً حتى تصل إلى الصيدلية',
            'path_coordinates' => [
                ['x' => 30, 'y' => 30],
                ['x' => 10, 'y' => 30],
            ],
            'estimated_time_seconds' => 30,
            'distance_meters' => 20,
        ]);

        // من كل عيادة إلى السلالم
        foreach ($clinics as $clinic) {
            NavigationPath::create([
                'from_room_id' => $clinic->id,
                'to_room_id' => $stairs->id,
                'directions' => 'اتجه إلى السلالم للعودة',
                'path_coordinates' => [
                    ['x' => $clinic->map_x, 'y' => $clinic->map_y],
                    ['x' => 30, 'y' => 30],
                ],
                'estimated_time_seconds' => 90,
                'distance_meters' => 50,
            ]);
        }

        $this->command->info('✅ تم إنشاء بيانات الخريطة بنجاح!');
        $this->command->info('📊 الطابق: ' . $floor->name);
        $this->command->info('🏠 عدد الغرف: ' . Room::where('floor_id', $floor->id)->count());
        $this->command->info('🛤️  عدد المسارات: ' . NavigationPath::count());
    }
}
