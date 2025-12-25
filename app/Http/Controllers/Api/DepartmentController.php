<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Get all departments with doctors and schedules
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $departments = Department::with(['doctors.schedules'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => DepartmentResource::collection($departments)->collection->all(),
        ]);
    }

    /**
     * Get single department with doctors and schedules
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $department = Department::with(['doctors.schedules'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new DepartmentResource($department),
        ]);
    }
}

