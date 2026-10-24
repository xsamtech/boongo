<?php

namespace App\Http\Resources;

use App\Support\FileOrdering;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileGroups extends JsonResource
{
    /**
     * Keys are type IDs, or "untyped" for files whose type_id is null.
     *
     * @return array<int|string, list<File>>
     */
    public function toArray(Request $request): array
    {
        return FileOrdering::sort($this->resource)
            ->groupBy(fn ($file) => $file->type_id ?? 'untyped')
            ->map(fn ($files) => File::collection($files->values())->resolve($request))
            ->all();
    }
}
