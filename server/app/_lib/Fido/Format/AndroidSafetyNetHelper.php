<?php

namespace App\_lib\Fido\Format;

use \App\_lib\Fido\Helper\ConvertHelper;
use \App\_lib\Fido\Helper\BinaryHelper;
use \App\_lib\Fido\Helper\ErrorHelper;


class AndroidKeyHelper
{
    use ConvertHelper;
    use BinaryHelper;
    use ErrorHelper;

    private static $SHA256_cose_identifier  = -7;

    private $FMTFormat;

    private $signature  = '';
    private $signedValue= '';
    private $x5c        = '';
    private $payload    = '';
    private $pem        = '';

    public function __construct(
        \App\_lib\Fido\Attestation\FMTFormat $fmtFormat
    ){
        // オブジェクトの登録
        $this->FMTFormat = $fmtFormat;

        $attStmt = $this->FMTFormat->callAttestationObject()->getAttStmt();
        // 
        if (!array_key_exists('ver', $attStmt)
            || !$attStmt['ver']) {
                $this->setError('Format ERROR : ', 'Invalid Android Safety Net Format');
        }
        if (!array_key_exists('response', $attStmt)) {
                $this->setError('Format ERROR : ', 'Invalid Android Safety Net Format');
        }

        // 応答は、コンパクトシリアル化のJWS [RFC7515]オブジェクトです。
        // JWSには、2つのピリオド（'.'）文字で区切られた3つのセグメントがあります
        $parst = explode('.', $response);
        unset($response);

        if (count($parst) !== 3) {
            $this->setError('JWS ERROR : ', 'Invalid JWS DATA');
        }

        $header = $this->base64urlDecode($parts[0]);
        $payload = $this->base64urlDecode($parts[1]);
        $this->signature = $this->base64urlDecode($parts[2]);
        $this->signedValue = $parts[0] . '.' . $parts[1];
        unset ($parts);

        $header = json_decode($header);
        $payload = json_decode($payload);

        if (!($header instanceof stdClass)) {
            $this->setError('JWS ERROR', 'Inavalid JWS Header');
        }

        if (!($payload instanceof stdClass)) {
            $this->setError('JWS ERROR', 'Invalid JWS Payload');
        }

        if (!($header->x5c || is_array($header->x5c) || count($header->x5c) === 0)) {
            $this->setError('JWS ERROR', 'No X.509 signature in JWS Header');
        }


        $this->x5c          = base64_decode($header->x5c[0]);
        $this->payload      = $payload;

        if (count($attStmt['x5c']) > 1) {
            for ($i = 1; $i < count($header->x5c); $i++) {
                $this->x5c_chain[] = base64_decode($header->x5c[$i]);
            }
            unset($i);
        }
    }

    /**
     * PEM形式の公開鍵を作成
     *
     * @return self
     */
    public function buildCertificatePem(): self
    {
        $this->pem = '-----BEGIN CERTIFICATE-----' . "\n";
        $this->pem .= chunk_split(base64_encode($this->x5c), 64, "\n");
        $this->pem .= '-----END CERTIFICATE-----' . "\n";
        return $this;
    }


    /**
     * Attestationの検証を行う
     *
     * @return bool 
     */
    public function validateAttestation($clientDataHash): bool
    {
        $this->buildCertificatePem();
        $pubKey = openssl_pkey_get_public($this->pem);

        // authenticatorDataとclientDataHashを連結したSHA-256ハッシュと
        // payloadのnonceをBase64エンコードした値と同一であることを確認
        if (!$this->payload->nonce
            || $this->payload->nonce !== base64_encode(
                                hash('SHA256', $this->FMTFormat->callAuthenticatorData()->getAuthenticatorDataCBOR()))
        ) {
            $this->setError('JWS ERROR', 'Invalid nonce in JWS payload');    
        }

        $certinfo = openssl_x509_parse($this->pem);

        // 「attest.android.com」に対してattestationCertが発行されていることを確認
        if (!is_array($certinfo)
            || !$certinfo['subject']
            || $certinfo['subject']['CN'] !== 'attest.android.com'
        ) {
            $this->setError('JWS ERROR', 'Invalid certificate Cn in JWS (' . $certinfo['subject']['CN'] . ')');
        }

        // payloadのctsProfileMatch属性がtrueであることを確認します。
        if (!$this->payload->ctsProfileMatch) {
            $this->setError('payload ERROR', 'Invalid ctsProfileMatch in payload');
        }
        
        return openssl_verify(
                $this->signedValue,
                $this->signature,
                $pubKey,
                OPENSSL_ALGO_SHA256
            );
    }

}