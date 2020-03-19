<?php
namespace App\_lib;
use Illuminate\Support\Facades\Facade;

class Master extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'Master';
    }
}
