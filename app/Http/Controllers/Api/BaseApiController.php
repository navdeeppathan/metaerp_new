<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseApiController extends Controller
{
    protected function paginateResponse(LengthAwarePaginator $paginator): array
    {
        return [
            'content' => $paginator->items(),
            'totalElements' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'size' => $paginator->perPage(),
            'number' => $paginator->currentPage() - 1,
            'numberOfElements' => count($paginator->items()),
            'first' => $paginator->onFirstPage(),
            'last' => $paginator->currentPage() === $paginator->lastPage(),
            'empty' => $paginator->isEmpty(),
        ];
    }
}
