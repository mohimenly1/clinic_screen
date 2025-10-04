<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'file_type',
        'duration',
    ];

    /**
     * ملف الوسائط يمكن أن ينتمي إلى عدة قوائم تشغيل.
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'media_item_playlist')->withPivot('order');
    }
}