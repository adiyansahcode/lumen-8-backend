<?php

declare(strict_types=1);

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\BookImg;

class BookImgTransformer extends TransformerAbstract
{
    public $type = 'bookImg';

    protected $availableIncludes = [
        'book',
    ];

    /**
     * @param App\Models\BookImg $data
     * @return array
     */
    public function transform(BookImg $data): array
    {
        return [
            'id' => $data->id,
            'createdAt' => $data->created_at,
            'updatedAt' => $data->updated_at,
            'deletedAt' => $data->deleted_at,
            'name' => $data->name,
            'description' => $data->description,
        ];
    }

    /**
     * @param App\Models\Author $data
     * @return array
     */
    public function includeBook(BookImg $data): object
    {
        return $this->collection($data->book, new BookTransformer(), 'book');
    }
}
