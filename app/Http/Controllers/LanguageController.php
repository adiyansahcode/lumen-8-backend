<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\Language;
use App\Models\Book;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use App\Transformers\LanguageTransformer;
use App\Transformers\BookTransformer;

class LanguageController extends Controller
{
    public function index()
    {
        $data = Language::paginate(5);
        $responseData = $this->paginate($data, new LanguageTransformer());

        // return Response()->json([
        //     'error' => [
        //         'message' => 'There are no incidents in the database.',
        //         'code' => 100
        //     ]
        // ], 404);

        return Response()
            ->json($responseData, 200)
            ->header('Content-Type', 'application/vnd.api+json');
    }
}
