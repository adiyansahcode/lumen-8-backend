<?php

namespace App\Http\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookStoreValidation extends BasePostValidation
{
    public function __construct(object $request, string $type)
    {
        // validate parent attributes
        parent::__construct($request, $type);

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
    }
}
