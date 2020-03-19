<?php
namespace App\_lib\Proxmox;
use Illuminate\Support\Facades\Facade;

class Proxmox extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'Proxmox';
    }
}
