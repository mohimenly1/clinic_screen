<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'floor_number',
        'map_image_path',
        'description',
        'display_order',
    ];

    /**
     * الغرف في هذا الطابق
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class)->orderBy('room_number');
    }

    /**
     * الحصول على صورة الخريطة كاملة المسار
     */
    public function getMapImageUrlAttribute(): ?string
    {
        if (!$this->map_image_path) {
            return null;
        }
        return \Storage::url($this->map_image_path);
    }
}
