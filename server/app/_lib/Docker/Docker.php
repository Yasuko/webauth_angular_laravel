<?php
namespace App\_lib\Docker;
use Illuminate\Support\Facades\Facade;

class Docker extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'docker';
    }
}
