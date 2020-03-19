<?php

namespace App\_lib\Fido\Format;

use \App\_lib\Fido\Helper\BinaryHelper;
use \App\_lib\Fido\Helper\ErrorHelper;

class TPMHelper
{
    use BinaryHelper;
    use ErrorHelper;

    private static $SHA256_cose_identifier  = -7;

    private $FMTFormat;

    private $signature  = '';
    private $x5c        = '';
    private $pubArea    = '';
    private $certInfo   = '';
    private $pem        = '';

    public function __construct(
        \App\_lib\Fido\Attestation\FMTFormat $fmtformat)
    {
        // クラスオブジェクトの保存
        $this->FMTFormat = $fmtformat;

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

        $pubArea = $attStmt['pubArea']->get_byte_string();

        $type = unpack('n', $pubArea, 0)[1];

        $length = 8;
        $a = unpack('n', $pubArea, $length)[1];
        substr($pubArea, $length + 2, $a);
        $length += (2 + $a);

        $hoge = array();
        $hoge['symmetric'] = unpack('n', $pubArea, $length)[1];
        $hoge['scheme'] = unpack('n', $pubArea, $length + 2)[1];
        $hoge['keyBits'] = unpack('n', $pubArea, $length + 4)[1];
        $hoge['exponent'] = unpack('n', $pubArea, $length + 6)[1];
        $hoge['endOffset'] = $length + 10;

        // $test2 = hex2bin($value);
        // $test2  = \CBOR\CBOREncoder::decode($_test2);

        // var_dump($hoge);

        $certInfo           = $attStmt['certInfo']->get_byte_string();
        $this->certInfo     = $this->authenticatorData->byteArrayToHex(
                                array_values(unpack('C*', $certInfo))
                            );
        $this->signature    = $attStmt['sig']->get_byte_string();
        $this->x5c          = $attStmt['x5c'][0]->get_byte_string();

    }

    public function buildCertificatePem(): self
    {
        $this->pem = '-----BEGIN CERTIFICATE-----' . "\n";
        $this->pem .= chunk_split(base64_encode($this->x5c), 64, "\n");
        $this->pem .= '-----END CERTIFICATE-----' . "\n";
        return $this;
    }

    public function validateAttestation($clientDataHash)
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
                $dataToVerify,
                $this->signature,
                $pubKey,
                OPENSSL_ALGO_SHA256
            ) === 1;
    }

}