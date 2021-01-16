<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Validators\JsonApiValidator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new JsonApiValidator($translator, $data, $rules, $messages);
        });
    }
}
