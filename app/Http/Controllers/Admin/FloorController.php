<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FloorController extends Controller
{
    public function index(): Response
    {
        $floors = Floor::withCount('rooms')
            ->orderBy('display_order')
            ->orderBy('floor_number')
            ->get()
            ->map(function ($floor) {
                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'floor_number' => $floor->floor_number,
                    'map_image_url' => $floor->map_image_url,
                    'description' => $floor->description,
                    'display_order' => $floor->display_order,
                    'rooms_count' => $floor->rooms_count,
                    'created_at' => $floor->created_at,
                ];
            });

        return Inertia::render('Admin/Floors/Index', [
            'floors' => $floors,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Floors/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|unique:floors,floor_number',
            'map_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'floor_number', 'description', 'display_order']);
        $data['display_order'] = $data['display_order'] ?? 0;

        if ($request->hasFile('map_image')) {
            $data['map_image_path'] = $request->file('map_image')->store('floors/maps', 'public');
        }

        Floor::create($data);

        return redirect()->route('admin.floors.index')->with('success', 'تم إنشاء الطابق بنجاح.');
    }

    public function edit(Floor $floor): Response
    {
        return Inertia::render('Admin/Floors/Edit', [
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'floor_number' => $floor->floor_number,
                'map_image_url' => $floor->map_image_url,
                'description' => $floor->description,
                'display_order' => $floor->display_order,
            ],
        ]);
    }

    public function update(Request $request, Floor $floor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|unique:floors,floor_number,' . $floor->id,
            'map_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'floor_number', 'description', 'display_order']);
        $data['display_order'] = $data['display_order'] ?? 0;

        if ($request->hasFile('map_image')) {
            if ($floor->map_image_path) {
                Storage::disk('public')->delete($floor->map_image_path);
            }
            $data['map_image_path'] = $request->file('map_image')->store('floors/maps', 'public');
        }

        $floor->update($data);

        return redirect()->route('admin.floors.index')->with('success', 'تم تحديث الطابق بنجاح.');
    }

    public function destroy(Floor $floor)
    {
        if ($floor->rooms()->count() > 0) {
            return redirect()->route('admin.floors.index')
                ->with('error', 'لا يمكن حذف الطابق لأنه يحتوي على غرف. يرجى حذف الغرف أولاً.');
        }

        if ($floor->map_image_path) {
            Storage::disk('public')->delete($floor->map_image_path);
        }

        $floor->delete();

        return redirect()->route('admin.floors.index')->with('success', 'تم حذف الطابق بنجاح.');
    }
}
