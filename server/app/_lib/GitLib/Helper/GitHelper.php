<?php

namespace App\_lib\GitLib\Helper;

use GuzzleHttp\Client;

trait GitHelper
{
    private $logfile    = 'c:\xampp\htdocs\resthub\log.txt';

    // 取得情報の保存先
    private $RepositoryStack    = array();
    private $BranchStack        = array();
    private $CommitStack        = array();

    // Guzzleクライントインスタンス
    private $CL = null;

    // サーバーレスポンス一時保存
    private $ResponseBody   = array();
    private $ResponseStatus = array();


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
            $this->CL = new Client(['base_uri' => $this->baseURL]);
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
            $this->ResponseBody = json_decode($response->getBody()->getContents(), true);
            // dd($this->ResponseBody);
        } catch (Error $e) {
            throw new Exception("ERRO Server Request Faild", $e);
        }
        return $this;
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



    /**
     * 次のページがある場合更に読み込む
     *
     * @param string $next ページ取得後に呼び出すメソッド名
     * @return Bool
     */
    private function pageNation(string $next = '') : Bool
    {
        $build  = 'buildRequest' . $this->Builder;

        if (array_key_exists('next', $this->ResponseBody)) {
            $this->ini()
                ->request($this->$build($this->ResponseBody['next']))
                ->$next()
                ->pageNation($next);
        }
        return false;
    }
}

