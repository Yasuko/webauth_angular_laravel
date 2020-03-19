<?php

namespace App\_lib\Fido\Attestation;

use App\_lib\Fido\Helper\BinaryHelper;
use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

class AttestationFlag {
    
    use BinaryHelper;
    use ConvertHelper;
    use ErrorHelper;

    private static $_ED_FLAG_ADDRESS = 8;
    private static $_UP_FLAG_ADDRESS = 7;
    private static $_UV_FLAG_ADDRESS = 5;
    private static $_AT_FLAG_ADDRESS = 1;

    private $FLAGS = '';

    private $authDataFlags = array(
        // UP ユーザーの存在証明をするもの、最も重要でない、らしい
        'user_present'  => 0,
        // UV　ユーザーの認証を通ったかどうかを表す
        'user_verified' => 0,
        // AT「Attested credential data」認証器の正当性をチェックするデータを含んでいるか
        'attested_credential_data'  => 0,
        // ED　Extensionを含んでいるか
        'extension'     => 0,
    );

    /**
     * authenticatorDataをバイナリからパースする
     * @param int $binary
     * @throws WebAuthnException
     */
    public function __construct(int $binary) {
        $_flags = decbin($binary);
        $this->FLAGS = str_pad($_flags, 8, 0, STR_PAD_LEFT);
        
        // Flagデータのパース
        // authDataFlagsの切り出しと配列に保存
        $this->parseAttestationDataFlags();
    }

    /**
     * authDataFlagsの中身をフラグごとに切り出し
     *
     * @return self
     */
    public function parseAttestationDataFlags(): self
    {
        // ED　Extensionフラグデータ
        $this->authDataFlags['extension'] = $this->parseBynary(self::$_ED_FLAG_ADDRESS);
        // UP　UserPresenceフラグデータ
        $this->authDataFlags['user_present']  = $this->parseBynary(self::$_UP_FLAG_ADDRESS);
        // UV　userVertificationフラグデータ
        $this->authDataFlags['user_verified'] = $this->parseBynary(self::$_UV_FLAG_ADDRESS);
        // AT　Attested credential dataフラグデータ
        $this->authDataFlags['attested_credential_data']  = $this->parseBynary(self::$_AT_FLAG_ADDRESS);

        $this->checkUPUVFlag();
        return $this;
    }

    /**
     * FLAG文字列を指定位置から1字切り出して返す
     *
     * @param integer $address
     * @return integer
     */
    private function parseBynary(int $address): int
    {
        return (int)substr($this->FLAGS, $address, 1);
    }

    /**
     * authDataFlagsを返す
     *
     * @return array
     */
    public function getAuthDataFlags(): array
    {
        return $this->authDataFlags;
    }

    /**
     * user_presentフラグを返す（UPフラグ）
     *
     * @return integer
     */
    public function getFlagUserPresent(): int
    {
        return $this->authDataFlags['user_present'];
    }

    /**
     * user_verifiedフラグを返す（UPフラグ）
     *
     * @return integer
     */
    public function getFlagUserVerified(): int
    {
        return $this->authDataFlags['user_verified'];
    }

    /**
     * attested_credential_dataフラグを返す（UPフラグ）
     *
     * @return integer
     */
    public function getFlagAttestedCredentialData(): int
    {
        return $this->authDataFlags['attested_credential_data'];
    }

    /**
     * extensionフラグを返す（UPフラグ）
     *
     * @return integer
     */
    public function getFlagExtension(): int
    {
        return $this->authDataFlags['extension'];
    }

    /**
     * UPUVフラグが有効か判定
     *
     * @return boolean
     */
    public function checkUPUVFlag(): bool
    {
        // UP UVのフラグが有効で無い場合、エラーを記録
        if ($this->authDataFlags['user_present'] !== 1
            || $this->authDataFlags['user_verified'] !== 1) {
            $this->setError('Flag State Error : ', 'Flag State is Invalid!!');
            return false;
        }
        return true;
    }

    /**
     * AttestedCredentialData Flagが有効か判定
     *
     * @return boolean
     */
    public function checkAttestedCredentialFlag(): bool
    {
        // AttestedCredentialData Flagが有効か判定
        if ($this->authDataFlags['attested_credential_data']) {
            return true;
        }
        return false;
    }

}
