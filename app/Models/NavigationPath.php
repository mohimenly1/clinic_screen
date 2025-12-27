<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_room_id',
        'to_room_id',
        'directions',
        'path_coordinates',
        'estimated_time_seconds',
        'distance_meters',
    ];

    protected $casts = [
        'path_coordinates' => 'array',
        'estimated_time_seconds' => 'integer',
        'distance_meters' => 'integer',
    ];

    /**
     * الغرفة المصدر
     */
    public function fromRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    /**
     * الغرفة الهدف
     */
    public function toRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }
}
