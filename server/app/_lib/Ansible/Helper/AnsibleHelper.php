<?php

namespace App\_lib\Proxmox\Helper;

use GuzzleHttp\Client;


trait ProxmoxHelper
{

    // Guzzleクライントインスタンス
    private $CL = null;


    var $scheem = "";
    var $layout = array();

    /**
     * GuzzleClinet初期化、作成済みの場合は何もしない
     *
     * @return this
     */
    private function ini() : self
    {
        if (!$this->CL) {
            $this->CL = new Client(
                [
                    'base_uri'  => $this->baseURL,
                    'cookies'   => true
                ]);
        }
        return $this;
    }

    /**
     * サーバーにリクエストを投げる
     *
     * @param array $param[path, method, options]
     * @return Object
     */
    private function request(array $param): self
    {
        try {
            $response = $this->CL->request(
                $param['method'],   // 送信メソッド
                $param['path'],     // アクセスパス
                $param['options']   // オプション
            );
            $this->Response = $response;
            $this->ResponseBody = json_decode($response->getBody()->getContents(), true);
        } catch (Error $e) {
            // throw new Exception("ERRO Server Request Faild", $e);
        }
        return $this;
    }

    private function checkResponseCode(): any
    {
        if ($this->Response->getStatusCode() === '200') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * サーバーレスポンスを分解保存
     *
     * @param Guzzle\response $response
     * @return self
     */
    /*
    private function saveResponse(Guzzle\response $response) : self
    {
        $statusCode = $response->getStatusCode()->getContents();
        $responseBody = $response->getBody()->getContents();
        return $this;
    }*/

}

