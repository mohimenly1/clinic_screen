<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        $departments = Department::withCount('doctors')->latest()->get()->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'doctors_count' => $department->doctors_count,
                'created_at' => $department->created_at,
            ];
        });

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Departments/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create($request->only('name'));

        return redirect()->route('admin.departments.index')->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function edit(Department $department): Response
    {
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($request->only('name'));

        return redirect()->route('admin.departments.index')->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'تم حذف القسم بنجاح.');
    }
}
