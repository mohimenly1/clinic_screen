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
        $fileExists = Storage::exists($this->file_path);
        
        // Convert relative URL to absolute URL
        $url = Storage::url($this->file_path);
        if (!str_starts_with($url, 'http')) {
            $url = $request->schemeAndHttpHost() . $url;
        }
        
        return [
            'id' => $this->id,
            'url' => $url,
            'type' => $this->file_type,
            'duration' => $this->duration,
            'file_name' => basename($this->file_path),
            'file_size' => $fileExists ? Storage::size($this->file_path) : null,
            'mime_type' => $fileExists ? Storage::mimeType($this->file_path) : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

