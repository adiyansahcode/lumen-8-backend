<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

trait ValidationTrait
{
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
                $sortColumn = Schema::getColumnListing($tableName);
            }

            $column = [];
            foreach ($sortColumn as $columnData) {
                $column[] = Str::camel($columnData);
                $column[] = '-' . Str::camel($columnData);
            }
            $data = request()->query('sort');
            if (strpos($data, ',') !== false) {
                foreach ($column as $columnValue) {
                    $dataArray = explode(',', $data);
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
            } else {
                if (!in_array($data, $column)) {
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
            if (strpos($data, ',') !== false) {
                foreach ($column as $columnValue) {
                    $dataArray = explode(',', $data);
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
            } else {
                if (!in_array($data, $column)) {
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
