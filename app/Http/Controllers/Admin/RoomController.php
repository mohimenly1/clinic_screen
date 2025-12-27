<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $rooms = Room::with('floor')
            ->orderBy('floor_id')
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type,
                    'map_x' => $room->map_x,
                    'map_y' => $room->map_y,
                    'is_active' => $room->is_active,
                    'floor' => [
                        'id' => $room->floor->id,
                        'name' => $room->floor->name,
                    ],
                    'created_at' => $room->created_at,
                ];
            });

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Rooms/Create', [
            'floors' => Floor::orderBy('display_order')->orderBy('floor_number')->get(['id', 'name', 'floor_number']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'room_type' => 'required|in:clinic,pharmacy,lab,reception,restroom,elevator,stairs,other',
            'map_x' => 'nullable|numeric|min:0|max:100',
            'map_y' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'floor_id',
            'name',
            'room_number',
            'room_type',
            'map_x',
            'map_y',
            'description',
            'is_active',
        ]);
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Room::create($data);

        return redirect()->route('admin.rooms.index')->with('success', 'تم إنشاء الغرفة بنجاح.');
    }

    public function edit(Room $room): Response
    {
        $room->load('floor');

        return Inertia::render('Admin/Rooms/Edit', [
            'room' => [
                'id' => $room->id,
                'floor_id' => $room->floor_id,
                'name' => $room->name,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'map_x' => $room->map_x,
                'map_y' => $room->map_y,
                'description' => $room->description,
                'is_active' => $room->is_active,
                'floor' => [
                    'id' => $room->floor->id,
                    'name' => $room->floor->name,
                ],
            ],
            'floors' => Floor::orderBy('display_order')->orderBy('floor_number')->get(['id', 'name', 'floor_number']),
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'room_type' => 'required|in:clinic,pharmacy,lab,reception,restroom,elevator,stairs,other',
            'map_x' => 'nullable|numeric|min:0|max:100',
            'map_y' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'floor_id',
            'name',
            'room_number',
            'room_type',
            'map_x',
            'map_y',
            'description',
            'is_active',
        ]);
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $room->update($data);

        return redirect()->route('admin.rooms.index')->with('success', 'تم تحديث الغرفة بنجاح.');
    }

    public function destroy(Room $room)
    {
        if ($room->schedules()->count() > 0) {
            return redirect()->route('admin.rooms.index')
                ->with('error', 'لا يمكن حذف الغرفة لأنها مرتبطة بمواعيد. يرجى إزالة المواعيد أولاً.');
        }

        $room->delete();

        return redirect()->route('admin.rooms.index')->with('success', 'تم حذف الغرفة بنجاح.');
    }
}
