<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // لوحة التحكم
            [
                'name' => 'view_dashboard',
                'display_name' => 'عرض لوحة التحكم',
                'description' => 'القدرة على عرض لوحة التحكم الرئيسية',
                'category' => 'dashboard',
            ],

            // إدارة الشاشات
            [
                'name' => 'view_screens',
                'display_name' => 'عرض الشاشات',
                'description' => 'القدرة على عرض قائمة الشاشات',
                'category' => 'screens',
            ],
            [
                'name' => 'create_screens',
                'display_name' => 'إنشاء شاشات',
                'description' => 'القدرة على إنشاء شاشات جديدة',
                'category' => 'screens',
            ],
            [
                'name' => 'edit_screens',
                'display_name' => 'تعديل الشاشات',
                'description' => 'القدرة على تعديل الشاشات الموجودة',
                'category' => 'screens',
            ],
            [
                'name' => 'delete_screens',
                'display_name' => 'حذف الشاشات',
                'description' => 'القدرة على حذف الشاشات',
                'category' => 'screens',
            ],
            [
                'name' => 'manage_screens',
                'display_name' => 'إدارة الشاشات',
                'description' => 'جميع صلاحيات إدارة الشاشات (عرض، إنشاء، تعديل، حذف)',
                'category' => 'screens',
            ],

            // مكتبة الوسائط
            [
                'name' => 'view_media',
                'display_name' => 'عرض الوسائط',
                'description' => 'القدرة على عرض مكتبة الوسائط',
                'category' => 'media',
            ],
            [
                'name' => 'upload_media',
                'display_name' => 'رفع الوسائط',
                'description' => 'القدرة على رفع وسائط جديدة',
                'category' => 'media',
            ],
            [
                'name' => 'edit_media',
                'display_name' => 'تعديل الوسائط',
                'description' => 'القدرة على تعديل الوسائط الموجودة',
                'category' => 'media',
            ],
            [
                'name' => 'delete_media',
                'display_name' => 'حذف الوسائط',
                'description' => 'القدرة على حذف الوسائط',
                'category' => 'media',
            ],
            [
                'name' => 'manage_media',
                'display_name' => 'إدارة الوسائط',
                'description' => 'جميع صلاحيات إدارة الوسائط',
                'category' => 'media',
            ],

            // قوائم التشغيل
            [
                'name' => 'view_playlists',
                'display_name' => 'عرض قوائم التشغيل',
                'description' => 'القدرة على عرض قوائم التشغيل',
                'category' => 'playlists',
            ],
            [
                'name' => 'create_playlists',
                'display_name' => 'إنشاء قوائم التشغيل',
                'description' => 'القدرة على إنشاء قوائم تشغيل جديدة',
                'category' => 'playlists',
            ],
            [
                'name' => 'edit_playlists',
                'display_name' => 'تعديل قوائم التشغيل',
                'description' => 'القدرة على تعديل قوائم التشغيل',
                'category' => 'playlists',
            ],
            [
                'name' => 'delete_playlists',
                'display_name' => 'حذف قوائم التشغيل',
                'description' => 'القدرة على حذف قوائم التشغيل',
                'category' => 'playlists',
            ],
            [
                'name' => 'manage_playlists',
                'display_name' => 'إدارة قوائم التشغيل',
                'description' => 'جميع صلاحيات إدارة قوائم التشغيل',
                'category' => 'playlists',
            ],

            // أقسام العيادة
            [
                'name' => 'view_departments',
                'display_name' => 'عرض الأقسام',
                'description' => 'القدرة على عرض أقسام العيادة',
                'category' => 'departments',
            ],
            [
                'name' => 'create_departments',
                'display_name' => 'إنشاء أقسام',
                'description' => 'القدرة على إنشاء أقسام جديدة',
                'category' => 'departments',
            ],
            [
                'name' => 'edit_departments',
                'display_name' => 'تعديل الأقسام',
                'description' => 'القدرة على تعديل الأقسام',
                'category' => 'departments',
            ],
            [
                'name' => 'delete_departments',
                'display_name' => 'حذف الأقسام',
                'description' => 'القدرة على حذف الأقسام',
                'category' => 'departments',
            ],
            [
                'name' => 'manage_departments',
                'display_name' => 'إدارة الأقسام',
                'description' => 'جميع صلاحيات إدارة الأقسام',
                'category' => 'departments',
            ],

            // الأطباء
            [
                'name' => 'view_doctors',
                'display_name' => 'عرض الأطباء',
                'description' => 'القدرة على عرض قائمة الأطباء',
                'category' => 'doctors',
            ],
            [
                'name' => 'create_doctors',
                'display_name' => 'إنشاء أطباء',
                'description' => 'القدرة على إضافة أطباء جدد',
                'category' => 'doctors',
            ],
            [
                'name' => 'edit_doctors',
                'display_name' => 'تعديل الأطباء',
                'description' => 'القدرة على تعديل بيانات الأطباء ومواعيدهم',
                'category' => 'doctors',
            ],
            [
                'name' => 'delete_doctors',
                'display_name' => 'حذف الأطباء',
                'description' => 'القدرة على حذف الأطباء',
                'category' => 'doctors',
            ],
            [
                'name' => 'manage_doctors',
                'display_name' => 'إدارة الأطباء',
                'description' => 'جميع صلاحيات إدارة الأطباء ومواعيدهم',
                'category' => 'doctors',
            ],

            // البث العام
            [
                'name' => 'view_broadcast',
                'display_name' => 'عرض البث العام',
                'description' => 'القدرة على عرض صفحة البث العام',
                'category' => 'broadcast',
            ],
            [
                'name' => 'manage_broadcast',
                'display_name' => 'إدارة البث العام',
                'description' => 'القدرة على إدارة البث العام (بدء وإيقاف)',
                'category' => 'broadcast',
            ],

            // إدارة المستخدمين والصلاحيات
            [
                'name' => 'view_users',
                'display_name' => 'عرض المستخدمين',
                'description' => 'القدرة على عرض قائمة المستخدمين',
                'category' => 'users',
            ],
            [
                'name' => 'create_users',
                'display_name' => 'إنشاء مستخدمين',
                'description' => 'القدرة على إنشاء مستخدمين جدد',
                'category' => 'users',
            ],
            [
                'name' => 'edit_users',
                'display_name' => 'تعديل المستخدمين',
                'description' => 'القدرة على تعديل بيانات المستخدمين وأدوارهم',
                'category' => 'users',
            ],
            [
                'name' => 'delete_users',
                'display_name' => 'حذف المستخدمين',
                'description' => 'القدرة على حذف المستخدمين',
                'category' => 'users',
            ],
            [
                'name' => 'manage_roles',
                'display_name' => 'إدارة الأدوار',
                'description' => 'القدرة على إدارة الأدوار والصلاحيات',
                'category' => 'users',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
