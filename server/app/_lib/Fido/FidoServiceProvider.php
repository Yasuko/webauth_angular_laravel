<?php

namespace App\_lib\Fido;
use Illuminate\Support\ServiceProvider;

class FidoServiceProvider extends ServiceProvider
{
    private $schema = 'Fido';

    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            $this->schema,
            'App\_lib\\'. $this->schema .'\\'. $this->schema .'Provider'
        );
    }
}