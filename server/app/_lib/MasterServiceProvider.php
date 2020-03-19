<?php

namespace App\_lib;
use Illuminate\Support\ServiceProvider;

class MasterServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'Master',
            'App\_lib\MasterProvider'
        );
    }
}