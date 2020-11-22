<?php

declare(strict_types=1);

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Transformers\BookTransformer;
use App\Models\Author;

class AuthorTransformer extends TransformerAbstract
{
    public $type = 'author';

    protected $availableIncludes = [
        'book'
    ];

    /**
     * @param App\Models\Author $data
     * @return array
     */
    public function transform(Author $data)
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
    public function includeBook(Author $data)
    {
        return $this->collection($data->book, new BookTransformer(), 'book');
    }
}
