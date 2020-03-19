<?php
namespace App\_lib\Ansible;
use Illuminate\Support\Facades\Facade;

class Ansible extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'ansible';
    }
}
