<?php

namespace App\_lib\CBOR;
use Illuminate\Support\ServiceProvider;

class CBORServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'Cbor',
            'App\_lib\CBOR\CBORProvider'
        );
    }
}