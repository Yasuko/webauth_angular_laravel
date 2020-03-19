<?php

namespace App\_lib\Helper;

class CodeCheck
{

    /**
     * 文字列がURLか判定
     *
     * @param string $url
     * @return boolean
     */
    public static function checkURL(string $url): bool
    {
        return (filter_var($url, FILTER_VALIDATE_URL))? true : false;
    }

}
