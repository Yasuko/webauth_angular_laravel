<?php

namespace App\_lib\Fido\Helper;


trait ConvertHelper
{

    /**
     * AttestationObject、AuthenticatorDataをCBOR形式からデコードし返す
     *
     * @param string $binary
     * @return array
     */
    private function decodeAttestationData(string $binary): array
    {
        $plainTxt = $this->base64urlDecode($binary);
        return \CBOR\CBOREncoder::decode($plainTxt);
    }

    /**
     * URLsafeなbase64文字列に変換する
     *
     * @param string $txt
     */
    private function base64urlEncode(string $txt)
    {
        $base64 = strtr(base64_encode($txt), '+/', '-_');
        return rtrim($base64, '=');
    }

    /**
     * URLsafeなbase64文字列を元の文字列に戻す
     *
     * @param string $txt
     * @return string
     */
    private function base64urlDecode(string $txt): string
    {
        $base64 = strtr($txt, '-_', '+/');
        return base64_decode($base64);
    }
}


