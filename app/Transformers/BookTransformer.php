<?php

declare(strict_types=1);

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Book;

class BookTransformer extends TransformerAbstract
{
    public $type = 'book';

    protected $availableIncludes = [
        'author',
        'bookImg',
        'category',
        'language',
        'publisher',
    ];

    /**
     * @param App\Models\Book $data
     * @return array
     */
    public function transform(Book $data): array
    {
        return [
            'id' => $data->uuid,
            'createdAt' => $data->created_at,
            'updatedAt' => $data->updated_at,
            'deletedAt' => $data->deleted_at,
            'isbn' => $data->isbn,
            'title' => $data->title,
            'publicationDate' => $data->publication_date,
            'weight' => $data->weight,
            'wide' => $data->wide,
            'long' => $data->long,
            'page' => $data->page,
            'description' => $data->description,
        ];
    }

    /**
     * @param App\Models\Book $data
     * @return array
     */
    public function includeAuthor(Book $data): object
    {
        return $this->collection($data->author, new AuthorTransformer(), 'author');
    }

    /**
     * @param App\Models\Book $data
     * @return array
     */
    public function includeBookImg(Book $data): object
    {
        return $this->collection($data->bookImg, new BookImgTransformer(), 'bookImg');
    }

    /**
     * @param App\Models\Book $data
     * @return array
     */
    public function includeCategory(Book $data): object
    {
        return $this->item($data->category, new CategoryTransformer(), 'category');
    }

    /**
     * @param App\Models\Book $data
     * @return array
     */
    public function includeLanguage(Book $data): object
    {
        return $this->item($data->language, new LanguageTransformer(), 'language');
    }

    /**
     * @param App\Models\Book $data
     * @return array
     */
    public function includePublisher(Book $data): object
    {
        return $this->item($data->publisher, new PublisherTransformer(), 'publisher');
    }
}
