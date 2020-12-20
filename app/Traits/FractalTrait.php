<?php

declare(strict_types=1);

namespace App\Traits;

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

trait FractalTrait
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

    public function item(object $data, object $transformer): array
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

    public function paginate(EloquentPaginator $data, object $transformer): array
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer, $transformer->type);
        $resource->setPaginator(new IlluminatePaginatorAdapter($data));
        return $manager->createData($resource)->toArray();
    }

    public function cursorPaginate(EloquentCollection $data, object $transformer, object $dataCursors): array
    {
        $request = request();
        $baseUrl = config('app.url');
        $manager = $this->getFractalManager();
        $dataCursor = new Cursor(
            $dataCursors->current,
            $dataCursors->previous,
            $dataCursors->next,
            $dataCursors->count
        );
        $resource = new Collection($data, $transformer, $transformer->type);
        $resource->setCursor($dataCursor);
        $result = $manager->createData($resource)->toArray();

        $path = $request->path();

        $linksSelf = null;
        if ($dataCursors->current) {
            $link = [ "page" => [ "after" => $dataCursors->current]];
            $linksSelf = $baseUrl . '/' . $path . '?';
            $linksSelf .= http_build_query($request->except(['page.after', 'page.before']));
            $linksSelf .= '&' . http_build_query($link);
        }

        $linksFirst = null;
        if ($dataCursors->first) {
            $link = [ "page" => [ "after" => $dataCursors->first]];
            $linksFirst = $baseUrl . '/' . $path . '?';
            $linksFirst .= http_build_query($request->except(['page.after', 'page.before']));
            $linksFirst .= '&' . http_build_query($link);
        }

        $linksPrev = null;
        if ($dataCursors->previous) {
            $link = [ "page" => [ "after" => $dataCursors->previous]];
            $linksPrev = $baseUrl . '/' . $path . '?';
            $linksPrev .= http_build_query($request->except(['page.after','page.before']));
            $linksPrev .= '&' . http_build_query($link);
        }

        $linksNext = null;
        if ($dataCursors->next) {
            $link = [ "page" => [ "after" => $dataCursors->next]];
            $linksNext = $baseUrl . '/' . $path . '?';
            $linksNext .= http_build_query($request->except(['page.after','page.before']));
            $linksNext .= '&' . http_build_query($link);
        }

        $linksLast = null;
        if ($dataCursors->last) {
            $link = [ "page" => [ "after" => $dataCursors->last]];
            $linksLast = $baseUrl . '/' . $path . '?';
            $linksLast .= http_build_query($request->except(['page.after', 'page.before']));
            $linksLast .= '&' . http_build_query($link);
        }

        $result["links"] = [];
        $result['links']['self'] = $linksSelf;
        $result['links']['first'] = $linksFirst;
        $result['links']['prev'] = $linksPrev;
        $result['links']['next'] = $linksNext;
        $result['links']['last'] = $linksLast;

        return $result;
    }
}
