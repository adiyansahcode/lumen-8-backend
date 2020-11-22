<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as EloquentPaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;
use App\Serializers\MySerializer;

class Controller extends BaseController
{
    private function getFractalManager(): object
    {
        $request = app(Request::class);
        $manager = new Manager();
        $baseUrl = config('app.url');
        $manager->setSerializer(new MySerializer($baseUrl));
        if (!empty($request->query('include'))) {
            $manager->parseIncludes($request->query('include'));
        }
        if (!empty($request->query('exclude'))) {
            $manager->parseExcludes($request->query('exclude'));
        }
        if (!empty($request->query('fields'))) {
            $manager->parseFieldsets($request->query('fields'));
        }
        return $manager;
    }

    public function item(EloquentCollection $data, object $transformer): array
    {
        $manager = $this->getFractalManager();
        $resource = new Item($data, $transformer, $transformer->type);
        return $manager->createData($resource)->toArray();
    }

    public function collection(EloquentCollection $data, object $transformer): array
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer, $transformer->type);
        return $manager->createData($resource)->toArray();
    }

    /**
     * @param LengthAwarePaginator $data
     * @param $transformer
     * @return array
     */
    public function paginate(EloquentPaginator $data, object $transformer): array
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer, $transformer->type);
        $resource->setPaginator(new IlluminatePaginatorAdapter($data));
        return $manager->createData($resource)->toArray();
    }

    public function cursorPaginate(EloquentCollection $data, object $transformer, object $dataCursors): array
    {
        $manager = $this->getFractalManager();
        $dataCursor = new Cursor(
            $dataCursors->current,
            $dataCursors->previous,
            $dataCursors->next,
            $dataCursors->total
        );
        $resource = new Collection($data, $transformer, $transformer->type);
        $resource->setCursor($dataCursor);
        return $manager->createData($resource)->toArray();
    }
}
