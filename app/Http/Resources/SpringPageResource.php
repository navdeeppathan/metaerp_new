<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class SpringPageResource extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'content' => $this->collection,
                'pageable' => [
                    'sort' => ['sorted' => true, 'unsorted' => false, 'empty' => false],
                    'pageNumber' => $this->resource->currentPage() - 1,
                    'pageSize' => $this->resource->perPage(),
                    'offset' => ($this->resource->currentPage() - 1) * $this->resource->perPage(),
                    'paged' => true,
                    'unpaged' => false,
                ],
                'totalPages' => $this->resource->lastPage(),
                'totalElements' => $this->resource->total(),
                'last' => $this->resource->currentPage() === $this->resource->lastPage(),
                'numberOfElements' => $this->resource->count(),
                'first' => $this->resource->currentPage() === 1,
                'size' => $this->resource->perPage(),
                'number' => $this->resource->currentPage() - 1,
                'empty' => $this->resource->isEmpty(),
            ];
        }

        return [
            'content' => $this->collection,
            'totalElements' => $this->collection->count(),
            'totalPages' => 1,
            'size' => $this->collection->count(),
            'number' => 0,
            'first' => true,
            'last' => true,
            'empty' => $this->collection->isEmpty(),
        ];
    }
}
