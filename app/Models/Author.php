<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Author extends \App\Models\BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'author';

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
        'name',
        'description',
    ];

    public function book()
    {
        return $this->belongsToMany('App\Models\Book', 'book_author', 'author_id', 'book_id');
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
