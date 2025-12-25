<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء دور "مدير النظام" مع جميع الصلاحيات
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'مدير النظام',
                'description' => 'مدير النظام لديه جميع الصلاحيات',
                'is_system' => true,
            ]
        );
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // إنشاء دور "مدير الشاشات"
        $screenManager = Role::firstOrCreate(
            ['name' => 'screen_manager'],
            [
                'display_name' => 'مدير الشاشات',
                'description' => 'يمكنه إدارة الشاشات فقط',
                'is_system' => false,
            ]
        );
        $screenManager->permissions()->sync(
            Permission::whereIn('name', [
                'view_dashboard',
                'view_screens',
                'create_screens',
                'edit_screens',
                'delete_screens',
                'view_media',
                'upload_media',
                'view_playlists',
                'edit_playlists',
            ])->pluck('id')
        );

        // إنشاء دور "مدير الأطباء"
        $doctorManager = Role::firstOrCreate(
            ['name' => 'doctor_manager'],
            [
                'display_name' => 'مدير الأطباء',
                'description' => 'يمكنه إدارة الأطباء والأقسام فقط',
                'is_system' => false,
            ]
        );
        $doctorManager->permissions()->sync(
            Permission::whereIn('name', [
                'view_dashboard',
                'view_doctors',
                'create_doctors',
                'edit_doctors',
                'delete_doctors',
                'view_departments',
                'create_departments',
                'edit_departments',
                'delete_departments',
            ])->pluck('id')
        );
    }
}
