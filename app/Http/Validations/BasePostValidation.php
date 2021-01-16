<?php

namespace App\Http\Validations;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BasePostValidation extends BaseController
{
    public function __construct(object $request, string $type)
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
    }
}
