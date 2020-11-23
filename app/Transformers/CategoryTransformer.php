<?php

declare(strict_types=1);

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Category;

class CategoryTransformer extends TransformerAbstract
{
    public $type = 'category';

    protected $availableIncludes = [
        'book',
    ];

    /**
     * @param App\Models\Category $data
     * @return array
     */
    public function transform(Category $data): array
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
     * @param App\Models\Category $data
     * @return array
     */
    public function includeBook(Category $data): object
    {
        return $this->item($data->book, new BookTransformer(), 'book');
    }
}
