<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => Storage::url($this->file_path),
            'type' => $this->file_type,
            'duration' => $this->duration,
            'file_name' => basename($this->file_path),
            'file_size' => Storage::size($this->file_path),
            'mime_type' => Storage::mimeType($this->file_path),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

