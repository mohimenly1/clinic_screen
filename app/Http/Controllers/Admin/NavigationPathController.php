<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationPath;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NavigationPathController extends Controller
{
    public function index(): Response
    {
        $paths = NavigationPath::with(['fromRoom.floor', 'toRoom.floor'])
            ->latest()
            ->get()
            ->map(function ($path) {
                return [
                    'id' => $path->id,
                    'from_room' => [
                        'id' => $path->fromRoom->id,
                        'name' => $path->fromRoom->name,
                        'floor_name' => $path->fromRoom->floor->name,
                    ],
                    'to_room' => [
                        'id' => $path->toRoom->id,
                        'name' => $path->toRoom->name,
                        'floor_name' => $path->toRoom->floor->name,
                    ],
                    'directions' => $path->directions,
                    'estimated_time_seconds' => $path->estimated_time_seconds,
                    'distance_meters' => $path->distance_meters,
                    'created_at' => $path->created_at,
                ];
            });

        return Inertia::render('Admin/NavigationPaths/Index', [
            'paths' => $paths,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/NavigationPaths/Create', [
            'rooms' => Room::with('floor')
                ->where('is_active', true)
                ->orderBy('floor_id')
                ->orderBy('room_number')
                ->get()
                ->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'room_number' => $room->room_number,
                        'floor_name' => $room->floor->name,
                        'label' => "{$room->name} ({$room->floor->name})",
                    ];
                }),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_room_id' => 'required|exists:rooms,id',
            'to_room_id' => 'required|exists:rooms,id|different:from_room_id',
            'directions' => 'required|string',
            'estimated_time_seconds' => 'nullable|integer|min:0',
            'distance_meters' => 'nullable|integer|min:0',
        ]);

        // Check if path already exists
        $existingPath = NavigationPath::where('from_room_id', $request->from_room_id)
            ->where('to_room_id', $request->to_room_id)
            ->first();

        if ($existingPath) {
            return redirect()->back()
                ->withErrors(['to_room_id' => 'يوجد مسار بالفعل بين هاتين الغرفتين. يمكنك تعديله بدلاً من ذلك.'])
                ->withInput();
        }

        NavigationPath::create($request->only([
            'from_room_id',
            'to_room_id',
            'directions',
            'estimated_time_seconds',
            'distance_meters',
        ]));

        return redirect()->route('admin.navigation-paths.index')->with('success', 'تم إنشاء المسار بنجاح.');
    }

    public function edit(NavigationPath $navigationPath): Response
    {
        $navigationPath->load(['fromRoom.floor', 'toRoom.floor']);

        return Inertia::render('Admin/NavigationPaths/Edit', [
            'path' => [
                'id' => $navigationPath->id,
                'from_room_id' => $navigationPath->from_room_id,
                'to_room_id' => $navigationPath->to_room_id,
                'directions' => $navigationPath->directions,
                'estimated_time_seconds' => $navigationPath->estimated_time_seconds,
                'distance_meters' => $navigationPath->distance_meters,
                'from_room' => [
                    'id' => $navigationPath->fromRoom->id,
                    'name' => $navigationPath->fromRoom->name,
                    'floor_name' => $navigationPath->fromRoom->floor->name,
                ],
                'to_room' => [
                    'id' => $navigationPath->toRoom->id,
                    'name' => $navigationPath->toRoom->name,
                    'floor_name' => $navigationPath->toRoom->floor->name,
                ],
            ],
            'rooms' => Room::with('floor')
                ->where('is_active', true)
                ->orderBy('floor_id')
                ->orderBy('room_number')
                ->get()
                ->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'room_number' => $room->room_number,
                        'floor_name' => $room->floor->name,
                        'label' => "{$room->name} ({$room->floor->name})",
                    ];
                }),
        ]);
    }

    public function update(Request $request, NavigationPath $navigationPath)
    {
        $request->validate([
            'from_room_id' => 'required|exists:rooms,id',
            'to_room_id' => 'required|exists:rooms,id|different:from_room_id',
            'directions' => 'required|string',
            'estimated_time_seconds' => 'nullable|integer|min:0',
            'distance_meters' => 'nullable|integer|min:0',
        ]);

        // Check if path already exists (excluding current path)
        $existingPath = NavigationPath::where('from_room_id', $request->from_room_id)
            ->where('to_room_id', $request->to_room_id)
            ->where('id', '!=', $navigationPath->id)
            ->first();

        if ($existingPath) {
            return redirect()->back()
                ->withErrors(['to_room_id' => 'يوجد مسار بالفعل بين هاتين الغرفتين.'])
                ->withInput();
        }

        $navigationPath->update($request->only([
            'from_room_id',
            'to_room_id',
            'directions',
            'estimated_time_seconds',
            'distance_meters',
        ]));

        return redirect()->route('admin.navigation-paths.index')->with('success', 'تم تحديث المسار بنجاح.');
    }

    public function destroy(NavigationPath $navigationPath)
    {
        $navigationPath->delete();

        return redirect()->route('admin.navigation-paths.index')->with('success', 'تم حذف المسار بنجاح.');
    }
}
