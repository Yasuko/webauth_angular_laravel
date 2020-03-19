<?php

namespace App\_lib\GitLib;
use Illuminate\Support\ServiceProvider;

class GitServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'git',
            'App\_lib\GitLib\GitProvider'
        );
    }
}