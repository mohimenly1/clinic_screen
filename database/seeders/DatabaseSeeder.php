<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // إنشاء مستخدم مدير للنظام
        // User::factory()->create([
        //     'name' => 'مدير النظام',
        //     'username' => 'mohimen1',
        //     'email' => 'mohimen1@example.com',
        //     'password' => Hash::make('password'), // كلمة المرور هي 'password'
        //     'is_admin' => true, // تحديد المستخدم كمدير
        // ]);

        // // إنشاء مستخدم عادي للاختبار
        // User::factory()->create([
        //     'name' => 'مستخدم تجريبي',
        //     'username' => 'test_user1',
        //     'email' => 'sss1@example.com',
        //     'password' => Hash::make('password'),
        // ]);

        // استدعاء الـ Seeders الأخرى
        $this->call([
            // PermissionsSeeder::class,
            // RolesSeeder::class,
            // DepartmentSeeder::class,
            NavigationMapSeeder::class,
        ]);
    }
}
