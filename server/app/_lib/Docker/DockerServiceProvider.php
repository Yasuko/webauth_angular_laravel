<?php

namespace App\_lib\Docker;
use Illuminate\Support\ServiceProvider;

class DockerServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'docker',
            'App\_lib\Docker\DockerProvider'
        );
    }
}