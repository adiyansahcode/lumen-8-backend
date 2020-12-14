<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait ReadDataTrait
{
    use ValidationTrait;

    public function hasInclude(object $data): object
    {
        $this->validateInclude();

        $request = request();
        // * check include param with eager-loading
        if (!empty($request->query('include'))) {
            $includeData = request()->query('include');
            $includeArray = explode(',', $includeData);
            $data = $this->model::with($includeArray);
        }

        return $data;
    }

    public function hasSort(object $data, bool $reverse = false): object
    {
        $this->validateSort();
        $model = new $this->model();

        $request = request();
        if (!empty($request->query('sort'))) {
            $sortData = request()->query('sort');
            $sortArray = Str::of($sortData)->explode(',');
            foreach ($sortArray as $sortValue) {
                if (strpos($sortValue, '.') !== false) {
                    $sortArray2 = Str::of($sortValue)->explode('.');
                    $sortTable = $sortArray2[0];
                    $sortColumn = $sortArray2[1];
                    if (strpos($sortColumn, '-') !== false) {
                        $sort = Str::snake(str_replace('-', ' ', $sortColumn));
                        if ($reverse === false) {
                            $data = $data->orderBy($sortTable . '.' . $sort, 'desc');
                        } else {
                            $data = $data->orderBy($sortTable . '.' . $sort, 'asc');
                        }
                    } else {
                        $sort = Str::snake($sortColumn);
                        if ($reverse === false) {
                            $data = $data->orderBy($sortTable . '.' . $sort, 'asc');
                        } else {
                            $data = $data->orderBy($sortTable . '.' . $sort, 'desc');
                        }
                    }
                } else {
                    if (strpos($sortValue, '-') !== false) {
                        $sort = Str::snake(str_replace('-', ' ', $sortValue));
                        if ($reverse === false) {
                            $data = $data->orderBy($sort, 'desc');
                        } else {
                            $data = $data->orderBy($sort, 'asc');
                        }
                    } else {
                        $sort = Str::snake($sortValue);
                        if ($reverse === false) {
                            $data = $data->orderBy($sort, 'asc');
                        } else {
                            $data = $data->orderBy($sort, 'desc');
                        }
                    }
                }
            }
        } else {
            if ($reverse === false) {
                if ($model->defaultSortOperator === 'asc') {
                    $operator = 'asc';
                } else {
                    $operator = 'desc';
                }
                $data = $data->orderBy($model->defaultSortColumn, $operator);
            } else {
                if ($model->defaultSortOperator === 'asc') {
                    $operator = 'desc';
                } else {
                    $operator = 'asc';
                }
                $data = $data->orderBy($model->defaultSortColumn, $operator);
            }
        }

        return $data;
    }

    public function hasFilter(object $data): object
    {
        $this->validateFilter();

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

        $request = request();
        // * check include param with eager-loading
        if (!empty($request->query('filter'))) {
            $filter = request()->query('filter');
            foreach ($filter as $filterKey => $filterValue) {
                $column = Str::snake($filterKey);
                foreach ($filterValue as $filterKey2 => $filterValue2) {
                    $operator = $filterKey2;
                    $value = $filterValue2;
                    if ($operator === 'eq') {
                        $operatorSymbol = '=';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'ne') {
                        $operatorSymbol = '!=';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'not') {
                        $operatorSymbol = '!=';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'gt') {
                        $operatorSymbol = '>';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'gte') {
                        $operatorSymbol = '>=';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'lt') {
                        $operatorSymbol = '<';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'lte') {
                        $operatorSymbol = '<=';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'contains') {
                        $operatorSymbol = 'like';
                        $value = '%' . $value . '%';
                        $data = $data->where($column, $operatorSymbol, $value);
                    } elseif ($operator === 'in') {
                        $value = Str::of($value)->explode(',');
                        $data = $data->whereIn($column, $value);
                    } elseif ($operator === 'nin') {
                        $value = Str::of($value)->explode(',');
                        $data = $data->whereNotIn($column, $value);
                    }
                }
            }
        }

        return $data;
    }

    public function getCursor(?string $currentCursor, bool $reverse = false): ?string
    {
        // get data first
        $cursor = null;
        $dataCursor = new $this->model();
        $dataCursor = $this->hasSort($dataCursor, $reverse);
        if ($reverse === false) {
            $dataCursor = $dataCursor->limit(1)->get();
            if ($dataCursor->count() > 0) {
                $cursor = $dataCursor->first()->uuid;
                if ($currentCursor) {
                    if ($cursor === $currentCursor) {
                        $cursor = null;
                    }
                }
            }
        } else {
            $dataCursor = $dataCursor->limit(5)->get();
            if ($dataCursor->count() > 0) {
                $cursor = $dataCursor->last()->uuid;
                if ($currentCursor) {
                    if ($cursor === $currentCursor) {
                        $cursor = null;
                    }
                }
            }
        }

        return $cursor;
    }

    public function getCursorSetting(): array
    {
        $cursorParam = [];
        $cursorOperator = null;
        $cursorOperatorPrev = null;

        $request = request();
        if (!empty($request->query('sort'))) {
            $sortData = request()->query('sort');
            $sortArray = explode(',', $sortData);
            foreach ($sortArray as $sortValue) {
                if (strpos($sortValue, '-') !== false) {
                    $sort = Str::snake(str_replace('-', ' ', $sortValue));
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
            array_push($cursorParam, 'created_at');
            $cursorOperator = '<=';
            $cursorOperatorPrev = '>=';
        }

        $data = [
            'param' => $cursorParam,
            'operator' => $cursorOperator,
            'operatorPrev' => $cursorOperatorPrev
        ];
        return $data;
    }

    public function hasResponse(object $data): array
    {
        $this->validatePaginate();

        $request = request();
        if (empty($request->query('page'))) {
            return $this->collection($data->get(), new $this->transformer());
        }

        $pageName = null;
        $pageQuery = null;
        $pageOffset = 0;
        $pageLimit = 0;
        $pageData = $request->query('page');

        // offset-based
        if (
            (isset($pageData['offset']) && $pageData['offset'] > 0) &&
            (isset($pageData['limit']) && $pageData['limit'] > 0)
        ) {
            $pageOffset = (int) $pageData['offset'];
            $pageLimit = (int) $pageData['limit'];
            $pageName = 'page[offset]';
            $pageQuery = 'page.offset';

            $data = $data->paginate($pageLimit, ['*'], $pageName, $pageOffset);
            $data->appends(request()->except($pageQuery));

            return $this->paginate($data, new $this->transformer());
        }

        // page-based
        if (
            (isset($pageData['size']) && $pageData['size'] > 0) &&
            (isset($pageData['number']) && $pageData['number'] > 0)
        ) {
            $pageOffset = (int) $pageData['number'];
            $pageLimit = (int) $pageData['size'];
            $pageName = 'page[number]';
            $pageQuery = 'page.number';

            $data = $data->paginate($pageLimit, ['*'], $pageName, $pageOffset);
            $data->appends(request()->except($pageQuery));

            return $this->paginate($data, new $this->transformer());
        }

        // just limit
        if (isset($pageData['limit']) && $pageData['limit'] > 0) {
            $pageLimit = (int) $pageData['limit'];

            $data = $data->limit($pageLimit)->get();

            return $this->collection($data, new $this->transformer());
        }

        if (
            (isset($pageData['size']) && $pageData['size'] > 0) &&
            (isset($pageData['after']) && !empty($pageData['after']))
        ) {
            // cursor-based
            $pageLimit = (int) $pageData['size'];
            $currentCursor = (string) $pageData['after'];

            // get total data
            $totalCursor = $data->get()->count();

            // get data first
            $firstCursor = $this->getCursor($currentCursor);

            // get data last
            $lastCursor = $this->getCursor($currentCursor, true);

            // get data Current
            $dataDbCurrentCursor = $this->model::where('uuid', $currentCursor)->first();

            // pagination setting
            $cursorSetting = $this->getCursorSetting();
            $cursorParam = $cursorSetting['param'];
            $cursorOperator = $cursorSetting['operator'];
            $cursorOperatorPrev = $cursorSetting['operatorPrev'];

            $cursorCurrentArray = [];
            foreach ($cursorParam as $cursorParamValue) {
                $cursorCurrentArray[] = $dataDbCurrentCursor->{$cursorParamValue};
            }
            $cursorColumn = implode(', ', $cursorParam);
            $cursorCurrent = "'" . implode("', '", $cursorCurrentArray) . "'";

            // set db pagination prev
            $previousCursor = null;
            $dataPrev = new $this->model();
            $dataPrev = $this->hasSort($dataPrev, true);
            $dataPrev = $dataPrev
                ->whereRaw("($cursorColumn) $cursorOperatorPrev ($cursorCurrent)")
                ->limit($pageLimit)
                ->get();
            if ($dataPrev->count() > 0) {
                $previousCursor = $dataPrev->last()->uuid;
                if ($previousCursor === $currentCursor) {
                    $previousCursor = null;
                }
            }

            // set db pagination
            $data = $data
                ->whereRaw("($cursorColumn) $cursorOperator ($cursorCurrent)")
                ->limit($pageLimit)
                ->get();

            $nextCursor = null;
            if ($data->count() > 0) {
                $nextCursor = $data->last()->uuid;
                if ($nextCursor === $currentCursor) {
                    $nextCursor = null;
                } elseif ($lastCursor === null) {
                    $nextCursor = null;
                }
            }

            $dataCursors = [
                'current' => $currentCursor,
                'first' => $firstCursor,
                'previous' => $previousCursor,
                'next' => $nextCursor,
                'last' => $lastCursor,
                'count' => $pageLimit,
                'total' => $totalCursor,
            ];

            return $this->cursorPaginate($data, new $this->transformer(), (object) $dataCursors);
        }

        // cursor-based
        if (isset($pageData['size']) && $pageData['size'] > 0) {
            $pageLimit = (int) $pageData['size'];
            $currentCursor = null;

            // get total data
            $totalCursor = $data->get()->count();

            // get data first
            $firstCursor = $this->getCursor($currentCursor);

            // get data last
            $lastCursor = $this->getCursor($currentCursor, true);

            // setting pagination with sorting
            $cursorParam = [];
            $cursorOperator = null;
            if (!empty($request->query('sort'))) {
                $sort = $request->query('sort');
                $sortArray = explode(',', $sort);
                foreach ($sortArray as $sortValue) {
                    if (strpos($sortValue, '-') !== false) {
                        $sort = Str::snake(str_replace("-", " ", $sortValue));
                        array_push($cursorParam, $sort);
                        if (empty($cursorOperator)) {
                            $cursorOperator = '<=';
                        }
                    } else {
                        $sort = Str::snake($sortValue);
                        array_push($cursorParam, $sort);
                        if (empty($cursorOperator)) {
                            $cursorOperator = '>=';
                        }
                    }
                }
            } else {
                $sort = 'created_at';
                array_push($cursorParam, $sort);
                $cursorOperator = '<=';
            }

            // get data first
            $dataDbCurrentCursor = new $this->model();
            $dataDbCurrentCursor = $this->hasSort($dataDbCurrentCursor);
            $dataDbCurrentCursor = $dataDbCurrentCursor->limit(1)->get()->first();

            // pagination setting
            $cursorSetting = $this->getCursorSetting();
            $cursorParam = $cursorSetting['param'];
            $cursorOperator = $cursorSetting['operator'];
            $cursorOperatorPrev = $cursorSetting['operatorPrev'];

            $cursorCurrentArray = [];
            foreach ($cursorParam as $cursorParamValue) {
                $cursorCurrentArray[] = $dataDbCurrentCursor->{$cursorParamValue};
            }
            $cursorColumn = implode(", ", $cursorParam);
            $cursorCurrent = "'" . implode("', '", $cursorCurrentArray) . "'";

            // set db pagination
            $data = $data
                ->whereRaw("($cursorColumn) $cursorOperator ($cursorCurrent)")
                ->limit($pageLimit)
                ->get();

            $dataCursors = [
                'current' => $data->first()->uuid,
                'first' => null,
                'previous' => null,
                'next' => $data->last()->uuid,
                'last' => $lastCursor,
                'count' => $pageLimit,
                'total' => $totalCursor,
            ];
            return $this->cursorPaginate($data, new $this->transformer(), (object) $dataCursors);
        }
    }
}
