<?php
namespace App\_lib\Fido;
use Illuminate\Support\Facades\Facade;

class Fido extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'Fido';
    }
}
