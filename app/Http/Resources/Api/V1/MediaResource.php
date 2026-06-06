<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fileName' => $this->file_name,
            'mimeType' => $this->mime_type,
            'collectionName' => $this->collection_name,
            'url' => $this->getUrl(),
            'size' => $this->size,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
