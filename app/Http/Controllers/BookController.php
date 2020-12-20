<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Book;
use App\Transformers\BookTransformer;
use App\Traits\FractalTrait;
use App\Traits\ValidationTrait;
use App\Traits\ReadDataTrait;
use Ramsey\Uuid\Uuid;

class BookController extends BaseController
{
    use FractalTrait;
    use ValidationTrait;
    use ReadDataTrait;

    /**
     * The name of the model.
     *
     * @var string
     */
    public $model = Book::class;

    /**
     * The name of the fractal tranform.
     *
     * @var string
     */
    public $transformer = BookTransformer::class;

    /**
     * API Read Data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        DB::enableQueryLog();

        // * get a request
        $requestData = $request->all();

        // * validate request
        $this->validatePaginate();

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

    public function store(Request $request): JsonResponse
    {
        DB::enableQueryLog();

        $this->validateStore();

        // validate attributes
        $validator = Validator::make($request->json('data.attributes'), [
            'isbn' => [
                'required',
                'string',
                'max:13',
                'unique:App\Models\Book,isbn',
            ],
            'title' => [
                'required',
                'string',
                'max:100',
            ],
            'publicationDate' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'weight' => [
                'required',
                'integer',
            ],
            'wide' => [
                'required',
                'integer',
            ],
            'long' => [
                'required',
                'integer',
            ],
            'page' => [
                'required',
                'integer',
            ],
            'description' => [
                'required',
                'string',
                'max:200',
            ],
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $response = response()
                ->json($errors, 400)
                ->header('Content-Type', 'application/vnd.api+json')
                ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

            throw new ValidationException(null, $response);
        }

        $uuid = Uuid::uuid4()->toString();

        try {
            DB::beginTransaction();

            $data = new Book();
            $data->uuid = $uuid;
            $data->isbn = $request->json('data.attributes.isbn');
            $data->title = $request->json('data.attributes.title');
            $data->publication_date = $request->json('data.attributes.publicationDate');
            $data->weight = $request->json('data.attributes.weight');
            $data->wide = $request->json('data.attributes.wide');
            $data->long = $request->json('data.attributes.long');
            $data->page = $request->json('data.attributes.page');
            $data->description = $request->json('data.attributes.description');
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
