<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Schedule; // ## جديد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // ## جديد
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(): Response
    {
        $doctors = Doctor::with('department')
            ->withCount('schedules')
            ->latest()
            ->get()
            ->map(function($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'photo_url' => $doctor->photo_path ? Storage::url($doctor->photo_path) : null,
                    'department_name' => $doctor->department->name,
                    'schedules_count' => $doctor->schedules_count,
                ];
            });

        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => $doctors,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Doctors/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('doctors', 'public');
        }

        Doctor::create([
            'name' => $request->name,
            'department_id' => $request->department_id,
            'photo_path' => $path,
        ]);

        return redirect()->route('admin.doctors.index')->with('success', 'تمت إضافة الطبيب بنجاح.');
    }

    public function edit(Doctor $doctor): Response
    {
        // ## تحديث: تحميل الجداول مع بيانات الطبيب
        $doctor->load('schedules', 'department');

        return Inertia::render('Admin/Doctors/Edit', [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'department_id' => $doctor->department_id,
                'department_name' => $doctor->department->name,
                'photo_url' => $doctor->photo_path ? Storage::url($doctor->photo_path) : null,
                'schedules' => $doctor->schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'clinic_number' => $schedule->clinic_number,
                        'floor' => $schedule->floor,
                    ];
                }),
            ],
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'department_id']);

        if ($request->hasFile('photo')) {
            if ($doctor->photo_path) {
                Storage::disk('public')->delete($doctor->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($data);

        return redirect()->route('admin.doctors.index')->with('success', 'تم تحديث بيانات الطبيب بنجاح.');
    }

    public function destroy(Doctor $doctor)
    {
        if ($doctor->photo_path) {
            Storage::disk('public')->delete($doctor->photo_path);
        }
        $doctor->delete();
        return redirect()->route('admin.doctors.index')->with('success', 'تم حذف الطبيب بنجاح.');
    }

    // ## جديد: دالة لإضافة موعد جديد
    public function storeSchedule(Request $request, Doctor $doctor)
    {
        $request->validate([
            'day_of_week' => ['required', 'string', Rule::in(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->start_time && strtotime($value) <= strtotime($request->start_time)) {
                        $fail('وقت الانتهاء يجب أن يكون بعد وقت البدء.');
                    }
                },
            ],
            'department_id' => 'required|exists:departments,id',
            'floor' => 'required|string|max:255',
        ]);

        // نحصل على اسم القسم لاستخدامه كـ clinic_number
        $department = Department::findOrFail($request->department_id);

        $doctor->schedules()->create([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'clinic_number' => $department->name, // نستخدم اسم القسم
            'floor' => $request->floor,
        ]);

        return back()->with('success', 'تمت إضافة الموعد بنجاح.');
    }

    // ## جديد: دالة لحذف موعد
    public function destroySchedule(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'تم حذف الموعد بنجاح.');
    }
}

