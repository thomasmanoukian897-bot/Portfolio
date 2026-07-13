<?php

namespace App\Services;

use App\Models\Category;

class PostCategoryResolver
{
    /**
     * @param  list<int>  $categoryIds
     * @return list<int>
     */
    public function resolve(array $categoryIds, bool $hasVideo): array
    {
        $videoCategoryId = Category::video()->id;

        if ($hasVideo) {
            return array_values(array_unique([...$categoryIds, $videoCategoryId]));
        }

        return array_values(array_filter(
            $categoryIds,
            fn (int $id): bool => $id !== $videoCategoryId
        ));
    }
}
