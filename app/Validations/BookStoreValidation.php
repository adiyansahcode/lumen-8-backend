<?php

namespace App\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class BookStoreValidation implements ValidationInterface
{
    public function validate(object $request, string $type)
    {
        // validate data
        $validator = Validator::make($request->json()->all(), [
            'data' => [
                'required',
                'array',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => "missing 'data' parameter at request.",
                'source' => ['pointer' => ''],
            ];

            $response = response()
                ->json($errorMsg, 422)
                ->header('Content-Type', 'application/vnd.api+json')
                ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

            throw new ValidationException(null, $response);
        }

        // validate attributes
        $validator = Validator::make($request->json('data'), [
            'attributes' => [
                'required',
                'array',
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '422',
                'code' => '422',
                'title' => 'invalid request',
                'detail' => "missing 'attributes' parameter at request.",
                'source' => ['pointer' => ''],
            ];

            $response = response()
                ->json($errorMsg, 422)
                ->header('Content-Type', 'application/vnd.api+json')
                ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

            throw new ValidationException(null, $response);
        }

        // validate data type
        $validator = Validator::make($request->json('data'), [
            'type' => [
                'required',
                'filled',
                'string',
                Rule::in([$type]),
            ],
        ]);
        if ($validator->fails()) {
            $errorMsg['errors'][] = [
                'id' => (int) mt_rand(1000, 9999),
                'status' => '409',
                'code' => '409',
                'title' => 'invalid request',
                'detail' => "type resource is invalid.",
                'source' => ['pointer' => 'data/type'],
            ];
            $response = response()
                ->json($errorMsg, 409)
                ->header('Content-Type', 'application/vnd.api+json')
                ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

            throw new ValidationException(null, $response);
        }

        // validate attributes json
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
