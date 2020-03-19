<?php

namespace App\_lib\Proxmox\Helper;

use GuzzleHttp\Cookie\CookieJar;

trait ProxmoxRequestHelper
{

    /**
     * リクエストを生成
     *
     * @param string $path
     * @return array
     */
    private function buildRequestAuth(
        string $path = '', string $username,
        string $password, string $method = 'POST'
    ): array
    {
        return array(
            'path'      => $path,
            'options'   => array(
                            'http_errors' => false,
                            'form_params'   => array(
                                'username'  => $username,
                                'password'  => $password
                            ),
                            'verify'    => false
                        ),
            'method'    => $method
        );
    }


    /**
     * サーバーに投げるリクエストを作成、トークンアクセスベース
     *
     * @param string $path
     * @param string $method
     * @return array
     */
    private function buildRequestToken(
        string $path, array $form, string $method = 'GET'
    ): array
    {
        $jar = CookieJar::fromArray(
            [
                'PVEAuthCookie' => $this->Ticket
            ],
            $this->Server
        );
        return array(
            'path'      => $path,
            'options'   => [
                            'http_errors'   => false,
                            'form_params'   => $form,
                            'cookies'       => $jar,
                            'headers'       => [
                                'CSRFPreventionToken' => $this->CSRFToken
                            ],
                            'verify'        => false
                        ],
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

