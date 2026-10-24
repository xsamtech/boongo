<?php

namespace App\Support;

use Illuminate\Support\Collection;

class FileOrdering
{
    /** Group types together, then sort known positions first, with a stable ID tie-breaker. */
    public static function sort(Collection $files): Collection
    {
        $key = fn ($file) => [
            $file->type_id === null ? 1 : 0,
            $file->type_id,
            $file->position === null ? 1 : 0,
            $file->position,
            $file->id,
        ];

        return $files->sort(fn ($left, $right) => $key($left) <=> $key($right))->values();
    }
}
