<?php

declare(strict_types=1);

namespace App\MyClass;

use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Pagination\CursorInterface;

class MySerializer extends JsonApiSerializer
{
    /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function paginator(PaginatorInterface $paginator): array
    {
        $currentPage = (int)$paginator->getCurrentPage();
        $lastPage = (int)$paginator->getLastPage();

        $pagination = [
            'total' => (int)$paginator->getTotal(),
            'count' => (int)$paginator->getCount(),
            'perPage' => (int)$paginator->getPerPage(),
            'currentPage' => $currentPage,
            'totalPages' => $lastPage,
        ];

        $pagination['links'] = [];

        $pagination['links']['self'] = $paginator->getUrl($currentPage);
        $pagination['links']['first'] = $paginator->getUrl(1);

        if ($currentPage > 1) {
            $pagination['links']['prev'] = $paginator->getUrl($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
        }

        $pagination['links']['last'] = $paginator->getUrl($lastPage);

        return ['pagination' => $pagination];
    }

    /**
     * Serialize the cursor.
     *
     * @param CursorInterface $cursor
     *
     * @return array
     */
    public function cursor(CursorInterface $cursor): array
    {
        $cursor = [
            'current' => $cursor->getCurrent(),
            'prev' => $cursor->getPrev(),
            'next' => $cursor->getNext(),
            'count' => (int) $cursor->getCount(),
        ];

        return ['cursor' => $cursor];
    }
}
