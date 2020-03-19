<?php

namespace App\_lib\Helper;
use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'helper',
            'App\_lib\Helper\HelperProvider'
        );
    }
}