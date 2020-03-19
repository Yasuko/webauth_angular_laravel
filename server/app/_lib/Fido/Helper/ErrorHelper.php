<?php

namespace App\_lib\Fido\Helper;


trait ErrorHelper
{
    /**
     * エラー情報を格納
     *
     * @var array
     */
    private $errors = array();


    /**
     * エラー情報取得
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * エラー情報の登録
     *
     * @param string $title
     * @param string $message
     * @return void
     */
    private function setError(string $title, string $message): void
    {
        $this->error[]  = array(
            'title'     => $title,
            'message'   => $message
        );
    }
}


