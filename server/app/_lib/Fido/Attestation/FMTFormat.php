<?php

namespace App\_lib\Fido\Attestation;

use \App\_lib\Fido\Helper\BinaryHelper;
use \App\_lib\Fido\Helper\ErrorHelper;

class FMTFormat
{
    use BinaryHelper;
    use ErrorHelper;

    private $attestationObject;     // attestationObjectクラス
    private $authenticatorData;     // authenticatorDataクラス
    private $attestedCredentialData;// attestedCredentailDataクラス
    private $clientDataJson;        // clientDataJsonクラス
    private $attestationFormat;     // FMTフォーマットに対応した各クラス

    private $attStmt    = array();
    private $fmt        = 'fido-u2f';
    private $credentailPublicKey = array();      // 公開鍵データ

    private $PublicKey      = '';
    private $PEMKey         = '';

    private static $_COSE_KTY = 1;  // KTYフラグ
    private static $_COSE_ALG = 3;  // ALGフラグ
    private static $_COSE_CRV = -1; // CRVフラグ
    private static $_COSE_X = -2;   // Xフラグ
    private static $_COSE_Y = -3;   // Yフラグ

    private static $_COSE_N = -1;   // Nフラグ
    private static $_COSE_E = -2;   // Eフラグ

    private static $_EC2_TYPE = 2;  // EC2暗号化フォーマット
    private static $_EC2_ES256 = -7;// ES256暗号化フォーマット
    private static $_EC2_P256 = 1;  // P256暗号化フォーマット
    private static $_EC2_RS256 = -257;// RS256暗号化フォーマット

    /**
     * FMTオブジェクトの初期化
     *
     * @param \App\_lib\Fido\Attestation\AttestationObject $attestationObject
     * @param \App\_lib\Fido\Attestation\AuthenticatorData $authenticatorData
     * @param \App\_lib\Fido\Attestation\AttestedCredentialData $attestedCredentialData
     * @param \App\_lib\Fido\Attestation\ClientDataJson $clientDataJson
     */
    public function __construct(
        \App\_lib\Fido\Attestation\AttestationObject $attestationObject = null,
        \App\_lib\Fido\Attestation\AuthenticatorData $authenticatorData,
        \App\_lib\Fido\Attestation\AttestedCredentialData $attestedCredentialData = null,
        \App\_lib\Fido\Attestation\ClientDataJson $clientDataJson
    ) {
        // オブジェクトを代入
        $this->attestationObject = $attestationObject;
        $this->authenticatorData = $authenticatorData;
        $this->attestedCredentialData = $attestedCredentialData;
        $this->clientDataJson = $clientDataJson;

        // attestationObjectが存在する場合fmtとattStmt情報を取得
        if ($attestationObject !== null) {
            // fmt取得
            $this->setFmt($this->attestationObject->getFmt());
            // attStmt取得
            $this->setAttStmt($this->attestationObject->getAttStmt());
        }

    }

    /**
     * fmtを登録
     *
     * @param string $fmt
     * @return self
     */
    public function setFmt(string $fmt): self
    {
        $this->fmt = $fmt;
        return $this;
    }

    /**
     * 
     * attStmtオブジェクトを登録（取得は連想配列）
     *
     * @param array $attStmt
     * @return self
     */
    public function setAttStmt(array $attStmt): self
    {
        $this->attStmt = $attStmt;
        return $this;
    }

    /**
     * AttestationObjectクラスを呼び出す
     *
     * @return \App_liv\Fido\Attestation\AttestationObject
     */
    public function callAttestationObject(): \App\_lib\Fido\Attestation\AttestationObject
    {
        return $this->attestationObject;
    }

    /**
     * AuthenticatorDataクラスを呼び出す
     *
     * @return \App_liv\Fido\Attestation\AuthenticatorData
     */
    public function callAuthenticatorData(): \App_liv\Fido\Attestation\AuthenticatorData
    {
        return $this->authenticatorData;
    }

    /**
     * AttestedCredentialDataクラスを呼び出す
     *
     * @return \App_liv\Fido\Attestation\AttestedcredentialData
     */
    public function callAttestedCredentialData(): \App_liv\Fido\Attestation\AttestedcredentialData
    {
        return $this->attestedCredentialData;
    }

    /**
     * 復元した公開鍵のBinaryArrayを返す
     *
     * @return array
     */
    public function getPublicKey(): array
    {
        return $this->PublicKey;
    }

    /**
     * returns the public key in PEM format
     * @return string
     */
    public function getPublicKeyPem(): string
    {

        // メタデータの付与
        $pubkey_hex = 
            "3059301306072a8648ce3d020106082a8648ce3d030107034200"
            . $this->byteArrayToHex($this->PublicKey);
        // 10進数のbyte arrayへ変換
        $pubkey = $this->hexToByteArray($pubkey_hex);
        // byte arrayからbase64へ
        $pubkey = $this->byteArrayToString($pubkey);
        $pubkey = base64_encode($pubkey);
        
        // PEMに整形
        $pubkey = chunk_split($pubkey,64, "\n");
        $this->PemKey = "-----BEGIN PUBLIC KEY-----\n$pubkey-----END PUBLIC KEY-----\n";
        return $this->PemKey;

    }

    /**
     * Attestation Formatを判別し
     * フォーマットごとのヘルパー関数を呼び出す
     *
     * @return self
     */
    public function createPublicKeyToFormat(): self
    {
        if ($this->fmt === 'fido-u2f'
        || $this->fmt === 'packed') {
            // 公開鍵情報のパースとU2Fタイプの公開鍵の作成
            $this->readCredentialPublicKeyToU2F()
                ->buildPublicKeyU2F();
            
            //
            $this->attestationFormat = 
                new \App\_lib\Fido\Format\U2FHelper($this);

        } else if ($this->fmt === 'none') {

        } else if ($this->fmt === 'android-key') {

        } else if ($this->fmt === 'android-safetynet') {

        } else if ($this->fmt === 'tpm') {
            $this->attestationFormat = 
                new \App\_lib\Fido\Format\TPMHelper($this);
            $this->readCredentialPublicKeyToTPM()
                    ->buildPublicKeyTPM();
        }

        return $this;
    }

    /**
     * signatureの検証
     *
     * @param string $key
     * @return boolean
     */
    public function verifiOpenSSLKey(string $key): bool
    {
        $verifi = ''
                . $this->authenticatorData->getAuthenticatorDataCBOR()
                . $this->clientDataJson->getClientDataHash();
        
        // public key の形式検証
        $pubKey = openssl_pkey_get_public($key);
        if (!$pubKey) {
            return false;
        }

        // 保存済みの公開鍵を使用しsignatureの正当性を確認
        $ssl_verifi = openssl_verify(
                    $verifi,
                    $this->authenticatorData->getSignature(),
                    $pubKey,
                    OPENSSL_ALGO_SHA256
                );
        if ($ssl_verifi !== 1) {
            return false;
        }
        return true;
    }

    /**
     * U2Fフォーマットの公開鍵を返す
     * @return array
     */
    private function buildPublicKeyU2F(): array
    {
        // key is [1,3,-1,-2,-3]
        $x = unpack('C*',$this->credentailPublicKey->x);
        $y = unpack('C*',$this->credentailPublicKey->y);
        $this->PublicKey = array_merge([4],$x,$y);

        return $this->PublicKey;
    }

    /**
     * TPMフォーマットの公開鍵を返したい
     * @return string
     */
    private function buildPublicKeyTPM(): string
    {
        return $this->PublicKey;
    }


    /**
     * COSEのEC2フォーマット公開鍵の情報を読み込む
     *
     * @return self
     */
    private function readCredentialPublicKeyToU2F(): self
    {
        $enc = $this->attestedCredentialData->getCredentialPublicKey();

        // COSE key-encoded elliptic curve public key in EC2 format
        $credPKey = new \stdClass();
        $credPKey->kty = $enc[self::$_COSE_KTY];
        $credPKey->alg = $enc[self::$_COSE_ALG];
        $credPKey->crv = $enc[self::$_COSE_CRV];
        $credPKey->x   = $enc[self::$_COSE_X] = (isset($enc[self::$_COSE_X])) ? $enc[self::$_COSE_X]->get_byte_string() : null;
        $credPKey->y   = $enc[self::$_COSE_Y] = (isset($enc[self::$_COSE_Y])) ? $enc[self::$_COSE_Y]->get_byte_string() : null;

        unset ($enc);

        // Validation
        if ($credPKey->kty !== self::$_EC2_TYPE) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Public Key Not In EX2 Format');
        }

        if ($credPKey->alg !== self::$_EC2_ES256) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Signature Algorithm Not ES256');
        }

        if ($credPKey->crv !== self::$_EC2_P256) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Curve Not P-256');
        }

        if (\strlen($credPKey->x) !== 32) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Invalid X-coordinate');
        }

        if (\strlen($credPKey->y) !== 32) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Invalid Y-coordinate');
        }

        $this->credentailPublicKey = $credPKey;
        return $this;
    }


    /**
     * reads COSE key-encoded elliptic curve public key in EC2 format
     *
     * @return self
     */
    private function readCredentialPublicKeyToTPM(): self
    {
        $enc = $this->attestedCredentialData->getCredentialPublicKey();
        // COSE key-encoded elliptic curve public key in EC2 format
        $credPKey = new \stdClass();
        $credPKey->kty  = $enc[self::$_COSE_KTY];
        $credPKey->alg  = $enc[self::$_COSE_ALG];
        $credPKey->n    = $enc[self::$_COSE_N] = (isset($enc[self::$_COSE_N])) ? $enc[self::$_COSE_N]->get_byte_string() : null;
        $credPKey->e    = $enc[self::$_COSE_E] = (isset($enc[self::$_COSE_E])) ? $enc[self::$_COSE_E]->get_byte_string() : null;

        unset ($enc);

        // Validation
        if ($credPKey->kty !== self::$_EC2_TYPE) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Public Key Not In EX2 Format');
        }

        if ($credPKey->alg !== self::$_EC2_RS256) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Signature Algorithm Not ES256');
        }

        if (strlen($credPKey->n) !== 32) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Invalid N-coordinate');
        }

        if (strlen($credPKey->e) !== 32) {
            $this->setError('INVALID PUBLIC KEY ERROR : ', 'Invalid E-coordinate');
        }

        $this->credentailPublicKey = $credPKey;
        return $this;
    }

}



