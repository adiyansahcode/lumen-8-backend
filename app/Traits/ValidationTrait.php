<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

trait ValidationTrait
{
    public function validateInclude(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'include' => [
                'string',
                'filled',
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

        if (!empty($request->query('include'))) {
            $transformerData = new $this->transformer();
            $column = $transformerData->getAvailableIncludes();

            $data = request()->query('include');
            $dataArray = Str::of($data)->explode(',');
            foreach ($dataArray as $dataValue) {
                if (!in_array($dataValue, $column)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '422',
                        'title' => 'invalid relationships',
                        'detail' => 'relationships not exist',
                        'source' => ['parameter' => 'include'],
                    ];
                    $response = response()
                        ->json($errorMsg, 400)
                        ->header('Content-Type', 'application/vnd.api+json')
                        ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                    throw new ValidationException(null, $response);
                }
            }
        }
    }

    public function validateSort(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'sort' => [
                'string',
                'filled',
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

        if (!empty($request->query('sort'))) {
            $model = new $this->model();
            $tableName = $model->getTable();

            $sortColumn = $model->sortable;
            if (empty($sortColumn)) {
                $errorMsg['errors'][] = [
                    'id' => (int) mt_rand(1000, 9999),
                    'status' => '400',
                    'code' => '422',
                    'title' => 'invalid sorting',
                    'detail' => 'API does not support sorting parameter',
                    'source' => ['parameter' => 'sort'],
                ];
                $response = response()
                    ->json($errorMsg, 400)
                    ->header('Content-Type', 'application/vnd.api+json')
                    ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                throw new ValidationException(null, $response);
            }

            $column = [];
            foreach ($sortColumn as $columnData) {
                $column[] = Str::camel($columnData);
                $column[] = '-' . Str::camel($columnData);
                $column[] = $tableName . '.' . Str::camel($columnData);
                $column[] = $tableName . '.' . '-' . Str::camel($columnData);
            }

            $data = request()->query('sort');
            $dataArray = Str::of($data)->explode(',');
            foreach ($dataArray as $dataValue) {
                if (!in_array($dataValue, $column)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '422',
                        'title' => 'invalid sorting',
                        'detail' => 'sorting column is invalid',
                        'source' => ['parameter' => 'sort'],
                    ];
                    $response = response()
                        ->json($errorMsg, 400)
                        ->header('Content-Type', 'application/vnd.api+json')
                        ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                    throw new ValidationException(null, $response);
                }
            }
        }
    }

    public function validateFilter(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'filter' => [
                'array',
                'filled',
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

        if (!empty($request->query('filter'))) {
            // $error = false;
            $model = new $this->model();
            $tableName = $model->getTable();

            $filterColumn = $model->filterable;
            if (empty($filterColumn)) {
                $errorMsg['errors'][] = [
                    'id' => (int) mt_rand(1000, 9999),
                    'status' => '400',
                    'code' => '422',
                    'title' => 'invalid filtering',
                    'detail' => 'API does not support filtering parameter',
                    'source' => ['parameter' => 'filter'],
                ];
                $response = response()
                    ->json($errorMsg, 400)
                    ->header('Content-Type', 'application/vnd.api+json')
                    ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                throw new ValidationException(null, $response);
            }

            $column = [];
            foreach ($filterColumn as $columnData) {
                $column[] = Str::camel($columnData);
                $column[] = $tableName . '.' . Str::camel($columnData);
            }

            $data = array_keys(request()->query('filter'));
            foreach ($data as $filterName) {
                if (!in_array($filterName, $column)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '422',
                        'title' => 'invalid filtering',
                        'detail' => 'filter column is invalid',
                        'source' => ['parameter' => 'filter'],
                    ];
                    $response = response()
                        ->json($errorMsg, 400)
                        ->header('Content-Type', 'application/vnd.api+json')
                        ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                    throw new ValidationException(null, $response);
                }
            }

            // Logical Operators
            $filterRule = [
                'eq', // operator (Equals)
                'ne', // operator (Not Equals)
                'not', // operator (Not Equals)
                'gt', // operator (Greater Than)
                'gte', // operator (Greater Than or Equal)
                'lt', // operator (Less Than)
                'lte', // operator (Less Than or Equal)
                'in', // operator (In)
                'nin', // operator (Not In)
                'contains', // operator string (filters if a string contains another substring)
            ];

            $filterOperator = [];
            $data = request()->query('filter');
            foreach ($data as $filterData) {
                $filterOperator = array_merge($filterOperator, $filterData);
            }
            $filterOperatorList = array_keys($filterOperator);
            foreach ($filterOperatorList as $filterOperatorValue) {
                if (!in_array($filterOperatorValue, $filterRule)) {
                    $errorMsg['errors'][] = [
                        'id' => (int) mt_rand(1000, 9999),
                        'status' => '400',
                        'code' => '422',
                        'title' => 'invalid filtering',
                        'detail' => 'filtering operators is invalid',
                        'source' => ['parameter' => 'filter'],
                    ];
                    $response = response()
                        ->json($errorMsg, 400)
                        ->header('Content-Type', 'application/vnd.api+json')
                        ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                    throw new ValidationException(null, $response);
                }
            }

            $filter = request()->query('filter');
            foreach ($filter as $filterKey => $filterValue) {
                $column = Str::snake($filterKey);
                foreach ($filterValue as $filterKey2 => $filterValue2) {
                    $operator = $filterKey2;
                    if (empty($filterValue2)) {
                        $errorMsg['errors'][] = [
                            'id' => (int) mt_rand(1000, 9999),
                            'status' => '400',
                            'code' => '422',
                            'title' => 'invalid filtering',
                            'detail' => 'the filter field must have a value.',
                            'source' => [
                                'parameter' => "filter/$filterKey/$operator"
                            ],
                        ];
                        $response = response()
                            ->json($errorMsg, 400)
                            ->header('Content-Type', 'application/vnd.api+json')
                            ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                        throw new ValidationException(null, $response);
                    }
                }
            }
        }
    }

    public function validatePaginate(): void
    {
        $request = request();

        $validator = Validator::make($request->all(), [
            'page' => [
                'array',
            ],
            'page.limit' => [
                'integer',
                'filled',
                'gt:0',
                'required_with:page.offset',
            ],
            'page.offset' => [
                'integer',
                'filled',
                'gt:0',
            ],
            'page.size' => [
                'integer',
                'filled',
                'gt:0',
                'required_with:page.number',
                'required_with:page.after',
                'required_with:page.before',
            ],
            'page.number' => [
                'integer',
                'filled',
                'gt:0',
            ],
            'page.after' => [
                'string',
                'filled',
                'uuid',
                'exists:book,uuid',
            ],
            'page.before' => [
                'string',
                'filled',
                'uuid',
                'exists:book,uuid',
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

    public function validateStore(): void
    {
        $request = request();

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
    }
}
