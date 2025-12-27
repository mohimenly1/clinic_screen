<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\NavigationPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NavigationController extends Controller
{
    /**
     * Get all floors with rooms
     * 
     * @return JsonResponse
     */
    public function floors(): JsonResponse
    {
        $floors = Floor::with(['rooms' => function($query) {
            $query->where('is_active', true)->orderBy('room_number');
        }])
            ->orderBy('display_order')
            ->orderBy('floor_number')
            ->get()
            ->map(function($floor) {
                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'floor_number' => $floor->floor_number,
                    'map_image_url' => $floor->map_image_url,
                    'description' => $floor->description,
                    'rooms' => $floor->rooms->map(function($room) {
                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                            'room_number' => $room->room_number,
                            'room_type' => $room->room_type,
                            'map_x' => $room->map_x,
                            'map_y' => $room->map_y,
                            'color' => $room->color,
                            'icon' => $room->icon,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $floors,
        ]);
    }

    /**
     * Get single floor with rooms
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function floor(int $id): JsonResponse
    {
        $floor = Floor::with(['rooms' => function($query) {
            $query->where('is_active', true)->orderBy('room_number');
        }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'floor_number' => $floor->floor_number,
                'map_image_url' => $floor->map_image_url,
                'description' => $floor->description,
                'rooms' => $floor->rooms->map(function($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'room_number' => $room->room_number,
                        'room_type' => $room->room_type,
                        'map_x' => $room->map_x,
                        'map_y' => $room->map_y,
                        'color' => $room->color,
                        'icon' => $room->icon,
                        'description' => $room->description,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get single room details
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function room(int $id): JsonResponse
    {
        $room = Room::with('floor')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $room->id,
                'name' => $room->name,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'map_x' => $room->map_x,
                'map_y' => $room->map_y,
                'color' => $room->color,
                'icon' => $room->icon,
                'description' => $room->description,
                'floor' => [
                    'id' => $room->floor->id,
                    'name' => $room->floor->name,
                    'floor_number' => $room->floor->floor_number,
                    'map_image_url' => $room->floor->map_image_url,
                ],
            ],
        ]);
    }

    /**
     * Search for rooms by name or number
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchRooms(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $rooms = Room::with('floor')
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('room_number', 'like', "%{$query}%");
            })
            ->orderBy('room_number')
            ->limit(20)
            ->get()
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type,
                    'icon' => $room->icon,
                    'floor' => [
                        'id' => $room->floor->id,
                        'name' => $room->floor->name,
                        'floor_number' => $room->floor->floor_number,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rooms,
        ]);
    }

    /**
     * Get navigation path between two rooms
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPath(Request $request): JsonResponse
    {
        $request->validate([
            'from_room_id' => 'required|exists:rooms,id',
            'to_room_id' => 'required|exists:rooms,id',
        ]);

        $fromRoomId = $request->get('from_room_id');
        $toRoomId = $request->get('to_room_id');

        // If same room, return simple message
        if ($fromRoomId == $toRoomId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'أنت بالفعل في المكان المطلوب',
                    'directions' => null,
                    'estimated_time_seconds' => 0,
                    'distance_meters' => 0,
                ],
            ]);
        }

        // Try to find direct path
        $path = NavigationPath::with(['fromRoom.floor', 'toRoom.floor'])
            ->where('from_room_id', $fromRoomId)
            ->where('to_room_id', $toRoomId)
            ->first();

        if ($path) {
            return response()->json([
                'success' => true,
                'data' => [
                    'directions' => $path->directions,
                    'estimated_time_seconds' => $path->estimated_time_seconds,
                    'distance_meters' => $path->distance_meters,
                    'path_coordinates' => $path->path_coordinates,
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
                ],
            ]);
        }

        // If no direct path exists, generate simple directions based on room positions
        $fromRoom = Room::with('floor')->findOrFail($fromRoomId);
        $toRoom = Room::with('floor')->findOrFail($toRoomId);

        // Same floor - simple directions
        if ($fromRoom->floor_id == $toRoom->floor_id) {
            $directions = $this->generateSimpleDirections($fromRoom, $toRoom);
        } else {
            // Different floors - need to mention floor change
            $directions = "يجب الانتقال من {$fromRoom->floor->name} إلى {$toRoom->floor->name}. استخدم المصعد أو السلالم للوصول للطابق المطلوب.";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'directions' => $directions,
                'estimated_time_seconds' => null,
                'distance_meters' => null,
                'path_coordinates' => null,
                'from_room' => [
                    'id' => $fromRoom->id,
                    'name' => $fromRoom->name,
                    'floor_name' => $fromRoom->floor->name,
                ],
                'to_room' => [
                    'id' => $toRoom->id,
                    'name' => $toRoom->name,
                    'floor_name' => $toRoom->floor->name,
                ],
            ],
        ]);
    }

    /**
     * Generate simple directions between two rooms on the same floor
     * 
     * @param Room $fromRoom
     * @param Room $toRoom
     * @return string
     */
    private function generateSimpleDirections(Room $fromRoom, Room $toRoom): string
    {
        if (!$fromRoom->map_x || !$fromRoom->map_y || !$toRoom->map_x || !$toRoom->map_y) {
            return "اتبع اللوحات الإرشادية للوصول من {$fromRoom->name} إلى {$toRoom->name}";
        }

        $directions = [];
        
        // Horizontal direction
        if ($fromRoom->map_x < $toRoom->map_x) {
            $directions[] = "اتجه شرقاً";
        } elseif ($fromRoom->map_x > $toRoom->map_x) {
            $directions[] = "اتجه غرباً";
        }

        // Vertical direction
        if ($fromRoom->map_y < $toRoom->map_y) {
            $directions[] = "ثم اتجه جنوباً";
        } elseif ($fromRoom->map_y > $toRoom->map_y) {
            $directions[] = "ثم اتجه شمالاً";
        }

        if (empty($directions)) {
            return "اتبع اللوحات الإرشادية للوصول من {$fromRoom->name} إلى {$toRoom->name}";
        }

        return implode(' ', $directions) . " للوصول إلى {$toRoom->name}";
    }
}
