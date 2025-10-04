<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'screen_code',
        'orientation',
        'resolution',
        'is_active',
        'background_audio_id'
    ];

    /**
     * كل شاشة لها تخصيص واحد يحدد ما تعرضه.
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(ScreenAssignment::class);
    }
    public function backgroundAudio(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class, 'background_audio_id');
    }
}