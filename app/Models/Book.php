<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'book';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'isbn',
        'title',
        'publication_date',
        'weight',
        'wide',
        'long',
        'page',
        'description',
        'publisher_id',
        'language_id',
        'category_id',
    ];

    /**
     * The attributes for sorting API.
     *
     * @var array
     */
    public $sortable = [
        'created_at',
        'updated_at',
        'isbn',
        'title',
        'publication_date',
    ];

    public function bookImg(): object
    {
        return $this->hasMany('App\Models\BookImg', 'book_id');
    }

    public function publisher(): object
    {
        return $this->belongsTo('App\Models\Publisher', 'publisher_id');
    }

    public function language(): object
    {
        return $this->belongsTo('App\Models\Language', 'language_id');
    }

    public function category(): object
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    public function author(): object
    {
        return $this->belongsToMany('App\Models\Author', 'book_author', 'book_id', 'author_id');
    }

    public function getCreatedAtAttribute(string $value): string
    {
        return date("Y-m-d H:i:s", strtotime($value));
    }

    public function getUpdatedAtAttribute(string $value): string
    {
        return date("Y-m-d H:i:s", strtotime($value));
    }
}
