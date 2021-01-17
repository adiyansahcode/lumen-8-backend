<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Validations\ValidationInterface;
use App\Validations\BookStoreValidation;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // register the interface class for dependency injection
        $this->app->bind(ValidationInterface::class, BookStoreValidation::class);
    }
}
