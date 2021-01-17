<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Transformers\BookTransformer;
use App\Validations\ValidationInterface;
use App\Validations\BookStoreValidation;
use App\Traits\FractalTrait;
use App\Traits\ReadDataTrait;
use Ramsey\Uuid\Uuid;

class BookController extends BaseController
{
    use FractalTrait;
    use ReadDataTrait;

    /**
     * request variable
     *
     * @var Request
     */
    private $request;

    /**
     * The name of resources.
     *
     * @var string
     */
    private $type;

    /**
     * The name of the model.
     *
     * @var string
     */
    private $model;

    /**
     * The name of the fractal tranform.
     *
     * @var string
     */
    private $transformer;

    /**
     * __construct function
     */
    public function __construct()
    {
        $this->request = request();
        $this->type = 'book';
        $this->model = Book::class;
        $this->transformer = BookTransformer::class;
    }

    /**
     * API Read Data
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // DB::enableQueryLog();

        // * Begin query
        $data = new $this->model();
        $data = $this->hasInclude($data);
        $data = $this->hasSort($data);
        $data = $this->hasFilter($data);
        $data = $this->hasResponse($data);

        // dump query sql
        // $data = DB::getQueryLog();

        // return JSON response
        return response()
            ->json($data, 200)
            ->header('Content-Type', 'application/vnd.api+json')
            ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');
    }

    public function store(ValidationInterface $validation): JsonResponse
    {
        // DB::enableQueryLog();

        // * do validation
        // new BookStoreValidation($this->request, $this->type);
        $validation->validate($this->request, $this->type);

        try {
            DB::beginTransaction();

            $data = new Book();
            $data->uuid = Uuid::uuid4()->toString();
            $data->isbn = $this->request->json('data.attributes.isbn');
            $data->title = $this->request->json('data.attributes.title');
            $data->publication_date = $this->request->json('data.attributes.publicationDate');
            $data->weight = $this->request->json('data.attributes.weight');
            $data->wide = $this->request->json('data.attributes.wide');
            $data->long = $this->request->json('data.attributes.long');
            $data->page = $this->request->json('data.attributes.page');
            $data->description = $this->request->json('data.attributes.description');
            $data->save();

            // Commit Transaction
            DB::commit();
            $data = $this->item($data, new $this->transformer());
            $code = 201;
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '500' ,
                'code' => '100' ,
                'title' => 'internal server error',
                'detail' => 'internal server error, please contact your administrator',
                'source' => ['pointer' => ''],
            ];
            $data = $errorMsg;
            $code = 500;
        }

        // dump query sql
        // $data = DB::getQueryLog();

        // return JSON response
        return response()
            ->json($data, $code)
            ->header('Content-Type', 'application/vnd.api+json')
            ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');
    }
}
