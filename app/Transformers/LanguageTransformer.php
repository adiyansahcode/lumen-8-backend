<?php

// declare(strict_types=1);

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Language;

class LanguageTransformer extends TransformerAbstract
{
    public $type = 'language';

    protected $availableIncludes = [
        'book',
    ];

    /**
     * @param App\Models\Language $data
     * @return array
     */
    public function transform(Language $data): array
    {
        return [
            'id' => (string) $data->id,
            'createdAt' => (string) $data->created_at,
            'updatedAt' => (string) $data->updated_at,
            'deletedAt' => (string) $data->deleted_at,
            'name' => (string) $data->name,
            'description' => (string) $data->description,
            'icon' => (string) $data->icon,
        ];
    }

    /**
     * @param App\Models\Language $data
     * @return array
     */
    public function includeBook(Language $data): object
    {
        return $this->collection($data->book, new BookTransformer(), 'book');
    }
}
