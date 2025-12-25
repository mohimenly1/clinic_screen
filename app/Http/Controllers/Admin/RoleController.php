<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->latest()
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'is_system' => $role->is_system,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                    'created_at' => $role->created_at,
                ];
            });

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        $permissions = Permission::orderBy('category')
            ->orderBy('display_name')
            ->get()
            ->groupBy('category')
            ->map(function ($perms, $category) {
                return $perms->map(fn($perm) => [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'display_name' => $perm->display_name,
                    'description' => $perm->description,
                ]);
            });

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
            'categories' => Permission::distinct()->pluck('category')->filter()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_system' => false,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'تم إنشاء الدور بنجاح.');
    }

    public function edit(Role $role): Response
    {
        $permissions = Permission::orderBy('category')
            ->orderBy('display_name')
            ->get()
            ->groupBy('category')
            ->map(function ($perms, $category) {
                return $perms->map(fn($perm) => [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'display_name' => $perm->display_name,
                    'description' => $perm->description,
                ]);
            });

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'is_system' => $role->is_system,
                'permissions' => $role->permissions->pluck('id')->toArray(),
            ],
            'permissions' => $permissions,
            'categories' => Permission::distinct()->pluck('category')->filter()->values(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        // منع تعديل الأدوار النظامية
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')->with('error', 'لا يمكن تعديل دور النظام.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles.index')->with('success', 'تم تحديث الدور بنجاح.');
    }

    public function destroy(Role $role)
    {
        // منع حذف الأدوار النظامية
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')->with('error', 'لا يمكن حذف دور النظام.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'تم حذف الدور بنجاح.');
    }
}
