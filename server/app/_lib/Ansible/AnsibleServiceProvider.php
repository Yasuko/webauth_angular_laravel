<?php

namespace App\_lib\Ansible;
use Illuminate\Support\ServiceProvider;

class AnsibleServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'ansible',
            'App\_lib\Ansible\AnsibleProvider'
        );
    }
}