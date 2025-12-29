<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'floor_id',
        'name',
        'room_number',
        'room_type',
        'map_x',
        'map_y',
        'description',
        'is_active',
    ];

    protected $casts = [
        'map_x' => 'decimal:2',
        'map_y' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * الطابق الذي تنتمي إليه الغرفة
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * المواعيد المرتبطة بهذه الغرفة
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * المسارات المنطلقة من هذه الغرفة
     */
    public function pathsFrom(): HasMany
    {
        return $this->hasMany(NavigationPath::class, 'from_room_id');
    }

    /**
     * المسارات المؤدية إلى هذه الغرفة
     */
    public function pathsTo(): HasMany
    {
        return $this->hasMany(NavigationPath::class, 'to_room_id');
    }

    /**
     * صور الواقع المعزز للغرفة
     */
    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('display_order');
    }

    /**
     * الحصول على لون الغرفة حسب نوعها (للعرض في الخريطة)
     */
    public function getColorAttribute(): string
    {
        return match($this->room_type) {
            'clinic' => '#673AB7',      // بنفسجي
            'pharmacy' => '#4CAF50',     // أخضر
            'lab' => '#2196F3',          // أزرق
            'reception' => '#FF9800',    // برتقالي
            'restroom' => '#9E9E9E',     // رمادي
            'elevator' => '#F44336',     // أحمر
            'stairs' => '#795548',       // بني
            default => '#607D8B',        // أزرق رمادي
        };
    }

    /**
     * الحصول على أيقونة الغرفة حسب نوعها
     */
    public function getIconAttribute(): string
    {
        return match($this->room_type) {
            'clinic' => '🏥',
            'pharmacy' => '💊',
            'lab' => '🔬',
            'reception' => '📋',
            'restroom' => '🚻',
            'elevator' => '🛗',
            'stairs' => '🪜',
            default => '📍',
        };
    }
}
