<?php

declare(strict_types=1);

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return Response()
            ->json('OK', 200)
            ->header('Content-Type', 'application/vnd.api+json');
});

// book group
$router->group(
    [
        'prefix' => '/book',
    ],
    function () use ($router) {
        $router->get('/', [
            'as' => 'book.index',
            'uses' => 'BookController@index',
        ]);
    }
);
