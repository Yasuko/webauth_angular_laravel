<?php

namespace App\_lib\GitLib\Helper;

trait GitRequestHelper
{

    /**
     * リクエストを生成
     * 基本的にGETのみを想定
     *
     * @param string $path
     * @return array
     */
    private function buildRequestAuth(
        string $path = '', string $method = 'GET'
    ): array
    {
        return array(
            'path'      => $path,
            'options'    => array(
                            'http_errors' => false,
                            'auth' => [$this->User, $this->Pass]
                        ),
            'method'    => $method
        );
    }


    /**
     * サーバーに投げるリクエストを作成、トークンアクセスベース
     * 基本的にGETのみを想定
     *
     * @param string $path
     * @param string $method
     * @return array
     */
    private function buildRequestToken(
        string $path, string $method = 'GET'
    ): array
    {
        return array(
            'path'      => $path,
            'options'    => array(
                            'http_errors' => false,
                        ),
            'header'    => array(
                            'Authorization: bearer '. $this->Token,
                            // 'Content-type: application/json; charset=UTF-8',
                        ),
            'method'    => $method
        );
    }

    /**
     * サーバーに投げるリクエストを作成GraphQLバージョン
     * 全てPOST要求になるので投げるクエリを必ず渡す事
     *
     * @param string $path
     * @param string $method
     * @return array
     */
    private function buildRequestGraphql(
        string $path, array $query, string $method = 'POST'
    ): array
    {
        return array(
            'path'      => $path,
            'options'    => array(
                            'http_errors'   => false,
                            //'auth'          => [$this->User, $this->Pass],
                            'json'          => $query
                        ),
            'header'    => array(
                            'Authorization: bearer '. $this->Token,
                            'Content-type: application/json; charset=UTF-8',
                        ),
            'method'    => $method
        );
    }

}

