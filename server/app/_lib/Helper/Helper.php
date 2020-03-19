<?php
namespace App\_lib\Helper;
use Illuminate\Support\Facades\Facade;

class Helper extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'helper';
    }
}
