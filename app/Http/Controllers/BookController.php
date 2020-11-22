<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use App\Http\Controllers\BaseController;
use App\Models\Book;
use App\Transformers\BookTransformer;

class BookController extends BaseController
{
    public function index(ServerRequestInterface $request): JsonResponse
    {
        // * get a request
        $requestData = $request->getQueryParams();

        // * validate request
        $validator = Validator::make($requestData, [
            'sort' => [
                'string',
            ],
            'page' => [
                'array',
            ],
            'page.limit' => [
                'integer','gt:0', 'required_with:page.offset',
            ],
            'page.offset' => [
                'integer','gt:0',
            ],
            'page.size' => [
                'integer','gt:0', 'required_with:page.number', 'required_with:page.after', 'required_with:page.before',
            ],
            'page.number' => [
                'integer','gt:0',
            ],
            'page.after' => [
                'string', 'uuid', 'exists:book,uuid'
            ],
            'page.before' => [
                'string', 'uuid', 'exists:book,uuid'
            ],
        ]);

        // * if fail sned Response 400
        if ($validator->fails()) {
            $errorMsg = $validator->errors()->messages();

            $responseError["errors"] = [];
            foreach ($errorMsg as $errorKey => $errorValue) {
                foreach ($errorValue as $errorValue2) {
                    $responseError["errors"][] = [
                        "code" => 400,
                        "source" => $errorKey,
                        "title" => "invalid request",
                        "detail" => $errorValue2,
                    ];
                }
            }

            return Response()
                ->json($responseError, 400)
                ->header('Content-Type', 'application/vnd.api+json')
                ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');
        }

        // * Begin query
        $dataDbs = new Book();
        $dataDbsReverse = new Book();

        // * check include param
        if (isset($requestData['include']) && !empty($requestData['include'])) {
            $include = $requestData['include'];
            $includeArray = explode(',', $include);
            foreach ($includeArray as $includeValue) {
                $dataDbs->load($includeValue);
            }
        }

        // * check sorting parameters
        if (isset($requestData['sort']) && !empty($requestData['sort'])) {
            $sort = $requestData['sort'];
            if (strpos($sort, ',') !== false) {
                $sortArray = explode(",", $sort);
                foreach ($sortArray as $sortValue) {
                    if (strpos($sortValue, '-') !== false) {
                        $sort = Str::snake(str_replace("-", " ", $sortValue));
                        $dataDbs = $dataDbs->orderBy($sort, 'desc');
                        $dataDbsReverse = $dataDbsReverse->orderBy($sort, 'asc');
                    } else {
                        $sort = Str::snake($sortValue);
                        $dataDbs = $dataDbs->orderBy($sort, 'asc');
                        $dataDbsReverse = $dataDbsReverse->orderBy($sort, 'desc');
                    }
                }
            } else {
                if (strpos($sort, '-') !== false) {
                    $sort = Str::snake(str_replace("-", " ", $sort));
                    $dataDbs = $dataDbs->orderBy($sort, 'desc');
                    $dataDbsReverse = $dataDbsReverse->orderBy($sort, 'asc');
                } else {
                    $sort = Str::snake($sort);
                    $dataDbs = $dataDbs->orderBy($sort, 'asc');
                    $dataDbsReverse = $dataDbsReverse->orderBy($sort, 'desc');
                }
            }
        } else {
            $dataDbs = $dataDbs->orderBy('created_at', 'desc');
            $dataDbsReverse = $dataDbsReverse->orderBy('created_at', 'asc');
        }

        // pagination
        if (isset($requestData['page'])) {
            $page = $requestData['page'];
            if (
                (isset($page['offset']) && $page['offset'] > 0) &&
                (isset($page['limit']) && $page['limit'] > 0)
            ) {
                // offset-based
                $pageLimit = (int) $page['limit'];
                $pageOffset = (int) $page['offset'];

                $dataDbs = $dataDbs->paginate($pageLimit, ['*'], 'page[offset]', $pageOffset);
                $dataDbs->appends(request()->except('page.offset'));
                $responseData = $this->paginate($dataDbs, new BookTransformer());
            } elseif (
                (isset($page['size']) && $page['size'] > 0) &&
                (isset($page['number']) && $page['number'] > 0)
            ) {
                // page-based
                $pageLimit = (int) $page['size'];
                $pageOffset = (int) $page['number'];

                $dataDbs = $dataDbs->paginate($pageLimit, ['*'], 'page[number]', $pageOffset);
                $dataDbs->appends(request()->except('page.number'));
                $responseData = $this->paginate($dataDbs, new BookTransformer());
            } elseif (isset($page['limit']) && $page['limit'] > 0) {
                $pageLimit = (int) $page['limit'];

                $dataDbs = $dataDbs->limit($pageLimit)->get();
                $responseData = $this->collection($dataDbs, new BookTransformer());
            } elseif (
                (isset($page['size']) && $page['size'] > 0) &&
                (isset($page['after']) && !empty($page['after']))
            ) {
                // cursor-based
                $pageLimit = (int) $page['size'];
                $currentCursor = (string) $page['after'];

                // get total data
                $totalCursor = $dataDbs->get()->count();

                // get data Current
                $dataDbCurrentCursor = Book::where('uuid', $currentCursor)->first();

                // setting pagination with sorting
                $cursorParam = [];
                $cursorOperator = null;
                $cursorOperatorPrev = null;
                if (isset($requestData['sort']) && !empty($requestData['sort'])) {
                    $sort = $requestData['sort'];
                    if (strpos($sort, ',') !== false) {
                        $sortArray = explode(",", $sort);
                        foreach ($sortArray as $sortValue) {
                            if (strpos($sortValue, '-') !== false) {
                                $sort = Str::snake(str_replace("-", " ", $sortValue));
                                array_push($cursorParam, $sort);
                                if (empty($cursorOperator)) {
                                    $cursorOperator = '<=';
                                    $cursorOperatorPrev = '>=';
                                }
                            } else {
                                $sort = Str::snake($sortValue);
                                array_push($cursorParam, $sort);
                                if (empty($cursorOperator)) {
                                    $cursorOperator = '>=';
                                    $cursorOperatorPrev = '<=';
                                }
                            }
                        }
                    } else {
                        if (strpos($sort, '-') !== false) {
                            $sort = Str::snake(str_replace("-", " ", $sort));
                            array_push($cursorParam, $sort);
                            $cursorOperator = '<=';
                            $cursorOperatorPrev = '>=';
                        } else {
                            $sort = Str::snake($sort);
                            array_push($cursorParam, $sort);
                            $cursorOperator = '>=';
                            $cursorOperatorPrev = '<=';
                        }
                    }
                } else {
                    array_push($cursorParam, 'created_at');
                    $cursorOperator = '<=';
                    $cursorOperatorPrev = '>=';
                }

                // pagination setting
                $cursorCurrentArray = [];
                foreach ($cursorParam as $cursorParamValue) {
                    $cursorCurrentArray[] = $dataDbCurrentCursor->{$cursorParamValue};
                }
                $cursorColumn = implode(", ", $cursorParam);
                $cursorCurrent = "'" . implode("', '", $cursorCurrentArray) . "'";

                // set db pagination prev
                $previousCursor = null;
                $dataDbsPrev = $dataDbsReverse
                    ->whereRaw("($cursorColumn) $cursorOperatorPrev ($cursorCurrent)")
                    ->limit($pageLimit)
                    ->get();
                    // var_dump($dataDbsPrev);
                if ($dataDbsPrev->count() > 0) {
                    $previousCursor = $dataDbsPrev->last()->uuid;
                }

                // set db pagination
                $dataDbs = $dataDbs
                    ->whereRaw("($cursorColumn) $cursorOperator ($cursorCurrent)")
                    ->limit($pageLimit)
                    ->get();

                $dataCursors = [
                    'current' => $currentCursor,
                    'previous' => $previousCursor,
                    'next' => $dataDbs->last()->uuid,
                    'count' => $pageLimit,
                    'total' => $totalCursor,
                ];
                $responseData = $this->cursorPaginate($dataDbs, new BookTransformer(), (object) $dataCursors);
            } elseif (isset($page['size']) && $page['size'] > 0) {
                // cursor-based
                $pageLimit = (int) $page['size'];
                $currentCursor = null;

                // get total data
                $totalCursor = $dataDbs->get()->count();

                // get data Current
                $dataDbCurrentCursor = new Book();

                // setting pagination with sorting
                $cursorParam = [];
                $cursorOperator = null;
                if (isset($requestData['sort']) && !empty($requestData['sort'])) {
                    $sort = $requestData['sort'];
                    if (strpos($sort, ',') !== false) {
                        $sortArray = explode(",", $sort);
                        foreach ($sortArray as $sortValue) {
                            if (strpos($sortValue, '-') !== false) {
                                $sort = Str::snake(str_replace("-", " ", $sortValue));
                                $dataDbCurrentCursor = $dataDbCurrentCursor->orderBy($sort, 'desc');
                                array_push($cursorParam, $sort);
                                if (empty($cursorOperator)) {
                                    $cursorOperator = '<=';
                                }
                            } else {
                                $sort = Str::snake($sortValue);
                                $dataDbCurrentCursor = $dataDbCurrentCursor->orderBy($sort, 'asc');
                                array_push($cursorParam, $sort);
                                if (empty($cursorOperator)) {
                                    $cursorOperator = '>=';
                                }
                            }
                        }
                    } else {
                        if (strpos($sort, '-') !== false) {
                            $sort = Str::snake(str_replace("-", " ", $sort));
                            $dataDbCurrentCursor = $dataDbCurrentCursor->orderBy($sort, 'desc');
                            array_push($cursorParam, $sort);
                            $cursorOperator = '<=';
                        } else {
                            $sort = Str::snake($sort);
                            $dataDbCurrentCursor = $dataDbCurrentCursor->orderBy($sort, 'asc');
                            array_push($cursorParam, $sort);
                            $cursorOperator = '>=';
                        }
                    }
                } else {
                    $sort = 'created_at';
                    $dataDbCurrentCursor = $dataDbCurrentCursor->orderBy($sort, 'desc');
                    array_push($cursorParam, $sort);
                    $cursorOperator = '<=';
                }

                $dataDbCurrentCursor = $dataDbCurrentCursor->limit(1)->get()->first();

                $cursorCurrentArray = [];
                foreach ($cursorParam as $cursorParamValue) {
                    $cursorCurrentArray[] = $dataDbCurrentCursor->{$cursorParamValue};
                }
                $cursorColumn = implode(", ", $cursorParam);
                $cursorCurrent = "'" . implode("', '", $cursorCurrentArray) . "'";

                // set db pagination
                $dataDbs = $dataDbs
                    ->whereRaw("($cursorColumn) $cursorOperator ($cursorCurrent)")
                    ->limit($pageLimit)
                    ->get();

                $dataCursors = [
                    'current' => $dataDbs->first()->uuid,
                    'previous' => null,
                    'next' => $dataDbs->last()->uuid,
                    'count' => $pageLimit,
                    'total' => $totalCursor,
                ];
                $responseData = $this->cursorPaginate($dataDbs, new BookTransformer(), (object) $dataCursors);
            } else {
                $responseError["errors"][] = [
                    "code" => 400,
                    "source" => "page",
                    "title" => "invalid request",
                    "detail" => "invalid request pagination",
                ];

                return Response()
                    ->json($responseError, 400)
                    ->header('Content-Type', 'application/vnd.api+json')
                    ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');
            }
        } else {
            $dataDbs = $dataDbs->get();
            $responseData = $this->collection($dataDbs, new BookTransformer());
        }

        // return JSON response
        return response()
            ->json($responseData, 200)
            ->header('Content-Type', 'application/vnd.api+json')
            ->header('Allow', 'GET,POST,DELETE,OPTIONS,HEAD');
    }
}
