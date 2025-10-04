<?php

// app/Models/Playlist.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    public function mediaItems(): BelongsToMany
    {
        return $this->belongsToMany(MediaItem::class, 'media_item_playlist')->withPivot('order');
    }
}