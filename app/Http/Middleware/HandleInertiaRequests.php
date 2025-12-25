<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];

        // تحديث is_admin ليعكس حالة المستخدم (is_admin أو super_admin)
        // هذا يضمن أن Vue components ترى is_admin = true للمستخدمين الذين لديهم دور super_admin
        if ($user) {
            // تحديث is_admin attribute مباشرة على user object
            $user->is_admin = $user->isSystemAdmin();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'permissions' => $permissions,
            ],
            // >> إضافة هذا الجزء لمشاركة رسائل النجاح والخطأ
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
