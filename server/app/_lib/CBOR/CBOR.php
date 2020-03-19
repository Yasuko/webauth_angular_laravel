<?php
namespace App\_lib\CBOR;
use Illuminate\Support\Facades\Facade;

class CBOR extends Facade
{
    /**
     * ファサード呼び出し時にどのサービスプロバイダーを呼び出すか
     */
    protected static function getFacadeAccessor()
    {
        return 'Cbor';
    }
}
