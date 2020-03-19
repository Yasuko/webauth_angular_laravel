<?php
namespace App\_lib\GitLib;
use Illuminate\Support\Facades\Facade;

class Git extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'git';
    }
}
