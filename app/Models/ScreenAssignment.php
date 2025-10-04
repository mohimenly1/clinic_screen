<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScreenAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_id',
        'assignable_type',
        'assignable_id',
    ];

    /**
     * التخصيص يتبع لشاشة واحدة.
     */
    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * العلاقة متعددة الأشكال التي تجلب المحتوى
     * (سواء كان Playlist أو MediaItem).
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}