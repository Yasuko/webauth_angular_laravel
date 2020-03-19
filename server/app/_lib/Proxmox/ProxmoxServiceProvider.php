<?php

namespace App\_lib\Proxmox;
use Illuminate\Support\ServiceProvider;

class ProxmoxServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(
            'Proxmox',
            'App\_lib\Proxmox\ProxmoxProvider'
        );
    }
}