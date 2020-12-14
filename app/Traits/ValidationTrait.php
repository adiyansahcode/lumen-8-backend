<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
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
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                $errorMsg['errors'][] = [
                    'code' => 400,
                    'source' => ['parameter' => 'include'],
                    'title' => 'invalid include relationships',
                    'detail' => $message,
                ];
            }

            $response = response()
                ->json($errorMsg, 400)
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
                        'code' => 400,
                        'source' => ['parameter' => 'include'],
                        'title' => 'invalid include relationships',
                        'detail' => 'relationships not exist',
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
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                $errorMsg['errors'][] = [
                    'code' => 400,
                    'source' => ['parameter' => 'sort'],
                    'title' => 'invalid sorting',
                    'detail' => $message,
                ];
            }

            $response = response()
                ->json($errorMsg, 400)
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
                    'code' => 400,
                    'source' => ['parameter' => 'sort'],
                    'title' => 'invalid sorting',
                    'detail' => 'API does not support sorting parameter',
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
                        'code' => 400,
                        'source' => ['parameter' => 'sort'],
                        'title' => 'invalid sorting',
                        'detail' => 'sorting column not exist',
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
                'filled'
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                $errorMsg['errors'][] = [
                    'code' => 400,
                    'source' => ['parameter' => 'filter'],
                    'title' => 'invalid filtering',
                    'detail' => $message,
                ];
            }

            $response = response()
                ->json($errorMsg, 400)
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
                    'code' => 400,
                    'source' => ['parameter' => 'filter'],
                    'title' => 'invalid sorting',
                    'detail' => 'API does not support filtering parameter',
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
                        'code' => 400,
                        'source' => ['parameter' => 'filter'],
                        'title' => 'invalid filtering',
                        'detail' => 'filter column not exist',
                    ];
                    $response = response()
                        ->json($errorMsg, 400)
                        ->header('Content-Type', 'application/vnd.api+json')
                        ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

                    throw new ValidationException(null, $response);
                }
            }

            // $data = array_keys(request()->query('filter'));
            // foreach ($data as $filterName) {
            //     if (strpos($filterName, '.') !== false) {
            //         $filterArray = Str::of($filterName)->explode('.');
            //         $filterTable = $filterArray[0];
            //         $filterColumn = $filterArray[1];
            //         if (Schema::hasTable($filterTable)) {
            //             if (!Schema::hasColumn($filterTable, Str::snake($filterColumn))) {
            //                 $errorMsg['errors'][] = [
            //                     'code' => 400,
            //                     'source' => ['parameter' => 'filter'],
            //                     'title' => 'invalid filtering',
            //                     'detail' => 'filter column not exist',
            //                 ];
            //                 $response = response()
            //                     ->json($errorMsg, 400)
            //                     ->header('Content-Type', 'application/vnd.api+json')
            //                     ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

            //                 throw new ValidationException(null, $response);
            //             }
            //         }
            //     } else {
            //         if (!Schema::hasColumn($tableName, Str::snake($filterName))) {
            //             $errorMsg['errors'][] = [
            //                 'code' => 400,
            //                 'source' => ['parameter' => 'filter'],
            //                 'title' => 'invalid filtering',
            //                 'detail' => 'filter column not exist',
            //             ];
            //             $response = response()
            //                 ->json($errorMsg, 400)
            //                 ->header('Content-Type', 'application/vnd.api+json')
            //                 ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');

            //             throw new ValidationException(null, $response);
            //         }
            //     }
            // }

            /*
            Logical Operators
            eq operator (Equals)
            ne operator (Not Equals)
            not operator (Not Equals)
            gt operator (Greater Than)
            gte operator (Greater Than or Equal)
            lt operator (Less Than)
            lte operator (Less Than or Equal)
            in operator (In)
            nin operator (Not In)
            contains function (filters if a string contains another substring)
            */

            $filterRule = [
                'eq',
                'ne',
                'not',
                'gt',
                'gte',
                'lt',
                'lte',
                'in',
                'nin',
                'contains',
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
                        'code' => 400,
                        'source' => ['parameter' => 'filter'],
                        'title' => 'invalid filtering',
                        'detail' => 'filtering operators is invalid',
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
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                $errorMsg['errors'][] = [
                    'code' => 400,
                    'source' => ['parameter' => 'page'],
                    'title' => 'invalid pagination',
                    'detail' => $message,
                ];
            }

            $response = response()
                ->json($errorMsg, 400)
                ->header('Content-Type', 'application/vnd.api+json')
                ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');
            throw new ValidationException(null, $response);
        }
    }
}
