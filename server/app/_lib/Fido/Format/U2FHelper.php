<?php

namespace App\_lib\Fido\Format;

use \App\_lib\Fido\Helper\BinaryHelper;
use \App\_lib\Fido\Helper\ErrorHelper;


class U2FHelper
{
    use BinaryHelper;
    use ErrorHelper;

    private static $SHA256_cose_identifier  = -7;

    private $FMTFormat;

    private $signature  = '';
    private $x5c        = '';
    private $pem        = '';

    public function __construct(
        \App\_lib\Fido\Attestation\FMTFormat $fmtFormat
    ){
        // オブジェクトの登録
        $this->FMTFormat = $fmtFormat;

        $attStmt = $this->FMTFormat->callAttestationObject()->getAttStmt();
        // 
        if (array_key_exists('alg', $attStmt)
             && $attStmt['alg'] !== self::$SHA256_cose_identifier) {
                $this->setError('SHA256 ERROR : ', 'Only SHA256 acceptable');
        }
        if (array_key_exists('sig', $attStmt)
            || is_obbject($attStmt['sig'])) {
                $this->setError('Signature ERROR : ', 'Signature Not Found');
        }
        if (array_key_exists('x5c', $attStmt)
            || is_array($attStmt['x5c'])
            || count($attStmt['x5c']) !== 1) {
                $this->setError('x5c ERROR : ', 'Invalid x5c certificate');
        }

        $this->signature    = $attStmt['sig']->get_byte_string();
        $this->x5c          = $attStmt['x5c'][0]->get_byte_string();

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
     * 検証結果が返る、はず？
     * @return bool 
     */
    public function validateAttestation($clientDataHash): bool
    {
        $this->buildCertificatePem();
        $pubKey = openssl_pkey_get_public($this->pem);

        if ($pubKey === false) {
            $this->setError(
                'PublicKey ERROR : ',
                'Invalid Public Key' . openssl_error_string()
            );
        }

        $rpid = $this->FMTFormat->callAuthenticatorData()->getRPId();
        $credentialId = $this->FMTFormat->callAttedtedCredentialData()->getCredentialId();
        $publicKey = $this->FMTFormat->getPublicKey();
        
        $verificationData = '\x00';
        $verificationData .= $this->byteArrayToString($rpid);
        $verificationData .= $clientDataHash;
        $verificationData .= $this->byteArrayToString($credentialId);
        $verificationData .= $this->byteArrayToString($publicKey);

        return openssl_verify(
                $verificationData,
                $this->signature,
                $pubKey,
                OPENSSL_ALGO_SHA256
            );
    }

}