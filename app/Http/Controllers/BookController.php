<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\Book;
use App\Transformers\BookTransformer;
use App\Traits\FractalTrait;
use App\Traits\ValidationTrait;
use App\Traits\ReadDataTrait;
use Illuminate\Support\Facades\DB;

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
        $data = $this->hasSort($data);
        $data = $this->hasInclude($data);
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
}
