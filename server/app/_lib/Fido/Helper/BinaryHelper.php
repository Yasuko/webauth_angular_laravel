<?php

namespace App\_lib\Fido\Helper;


trait BinaryHelper
{

    /**
     * バイト配列を文字列に変換
     * 
     * byte_array内の全てのバイト文字列から
     * １バイトの文字列を生成し結合したものを返す
     * 
     * @param array $byte_array
     */    
    private function byteArrayToString(array $byte_array)
    {
        return implode(array_map("chr", $byte_array));
    }

    /**
     * バイト配列をCBORオブジェクトに変換
     *
     * @param array $byte_array
     * @return array
     */
    private function byteArrayToCBORObject(array $byte_array): array
    {
        $CBORstring = $this->byteArrayToString($byte_array);
        $data = \CBOR\CBOREncoder::decode($CBORstring);
        return $data;
    }

    /**
     * バイト配列の中身をbig endianに変換し数値化
     *
     * @param array $byte_array
     */
    private function byteArrayToEndian(array $byte_array)
    {
        $value = '';
        foreach($byte_array as $num) {
            // $numを10進数から16進数に変換し
            // str_padで文字列の左側を0で埋める
            $value = $value . str_pad(decbin($num), 8, 0, STR_PAD_LEFT);
        }
        $value = bindec($value);
        return $value;
    }

    /**
     * バイト配列（10進数）を16進数の文字列に変換
     *
     * @param array $byte_array
     */
    private function byteArrayToHex(array $byte_array)
    {
        $value = '';
        foreach ($byte_array as $num) {
            $value = $value . str_pad(dechex($num), 2, 0, STR_PAD_LEFT);
        }
        return $value;
    }

    /**
     * 16進数の文字列をbyte array(10進数)に変換
     *
     * @param string $hex
     */
    private function hexToByteArray(string $hex)
    {
        $array = str_split($hex, 2);
        $value = [];
        foreach($array as $num) {
            $value []= hexdec($num);
        }
        return $value;
    }

    /**
     * 16進数を10進数に変換
     *
     * @param string $hex
     * @return integer
     */
    private function hexToDec(string $hex): int
    {
        return hexdec($hex);
    }

    /**
     * ランダムなバイト文字列を使って指定長のハッシュを作成
     *
     * 暗号学的に安全なハッシュ値の生成が目的らしいので
     * 改変の可能性が常にある
     * @param integer $length
     */
    private function getRandomByte(int $length = 0)
    {
        return base64_encode(random_bytes($length));
    }
}


