<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RoomImageController extends Controller
{
    public function index(Room $room): Response
    {
        $images = $room->images()
            ->orderBy('display_order')
            ->get()
            ->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => Storage::url($image->image_path),
                    'description' => $image->description,
                    'ar_instructions' => $image->ar_instructions,
                    'display_order' => $image->display_order,
                    'is_active' => $image->is_active,
                    'created_at' => $image->created_at,
                ];
            });

        return Inertia::render('Admin/RoomImages/Index', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'room_number' => $room->room_number,
                'floor' => [
                    'id' => $room->floor->id,
                    'name' => $room->floor->name,
                ],
            ],
            'images' => $images,
        ]);
    }

    public function create(Room $room): Response
    {
        return Inertia::render('Admin/RoomImages/Create', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
            ],
        ]);
    }

    public function store(Request $request, Room $room)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:10240', // 10MB
            'description' => 'nullable|string|max:1000',
            'ar_instructions' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $path = $request->file('image')->store('room_images', 'public');

        // Get the next display order if not provided
        $displayOrder = $validated['display_order'] ?? $room->images()->max('display_order') + 1;

        RoomImage::create([
            'room_id' => $room->id,
            'image_path' => $path,
            'description' => $validated['description'] ?? null,
            'ar_instructions' => $validated['ar_instructions'] ?? null,
            'display_order' => $displayOrder,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.rooms.images.index', $room)
            ->with('success', 'تم إضافة الصورة بنجاح.');
    }

    public function edit(Room $room, RoomImage $image): Response
    {
        return Inertia::render('Admin/RoomImages/Edit', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
            ],
            'image' => [
                'id' => $image->id,
                'image_url' => Storage::url($image->image_path),
                'description' => $image->description,
                'ar_instructions' => $image->ar_instructions,
                'display_order' => $image->display_order,
                'is_active' => $image->is_active,
            ],
        ]);
    }

    public function update(Request $request, Room $room, RoomImage $image)
    {
        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'description' => 'nullable|string|max:1000',
            'ar_instructions' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('room_images', 'public');
        }

        $image->update($validated);

        return redirect()->route('admin.rooms.images.index', $room)
            ->with('success', 'تم تحديث الصورة بنجاح.');
    }

    public function destroy(Room $room, RoomImage $image)
    {
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();

        return redirect()->route('admin.rooms.images.index', $room)
            ->with('success', 'تم حذف الصورة بنجاح.');
    }

    public function reorder(Request $request, Room $room)
    {
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:room_images,id',
            'images.*.display_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['images'] as $item) {
            RoomImage::where('id', $item['id'])
                ->where('room_id', $room->id)
                ->update(['display_order' => $item['display_order']]);
        }

        return redirect()->route('admin.rooms.images.index', $room)
            ->with('success', 'تم تحديث ترتيب الصور بنجاح.');
    }
}
