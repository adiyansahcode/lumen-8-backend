<?php

declare(strict_types=1);

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Publisher;

class PublisherTransformer extends TransformerAbstract
{
    public $type = 'publisher';

    protected $availableIncludes = [
        'book',
    ];

    /**
     * @param App\Models\Publisher $data
     * @return array
     */
    public function transform(Publisher $data): array
    {
        return [
            'id' => $data->id,
            'createdAt' => $data->created_at,
            'updatedAt' => $data->updated_at,
            'deletedAt' => $data->deleted_at,
            'name' => $data->name,
            'description' => $data->description,
            'city' => $data->city,
        ];
    }

    /**
     * @param App\Models\Publisher $data
     * @return array
     */
    public function includeBook(Publisher $data): object
    {
        return $this->collection($data->book, new BookTransformer(), 'book');
    }
}
