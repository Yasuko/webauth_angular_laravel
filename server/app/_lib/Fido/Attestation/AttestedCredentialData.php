<?php

namespace App\_lib\Fido\Attestation;

use App\_lib\Fido\Helper\BinaryHelper;
use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

class AttestedCredentialData {
    
    use BinaryHelper;
    use ConvertHelper;
    use ErrorHelper;

    // AAGUIDのbynaryアドレス
    private static $_AAGUID_ADDRESS = array(0, 16);
    // CredentialIDLengthのbinaryアドレス
    private static $_CREDENTIAL_ID_LENGTH_ADDRESS = array(16, 2);
    // CredentialIDのbinaryアドレス、終端はCredentailIdLengthに従う
    private static $_CREDENTIAL_ID_ADDRESS = 18;
    // CredentialPublicKeyのbinaryアドレスは
    // CredentailIdの終端からbinaryデータの最後までになる

    private $attestedCredentialData = array(
        // 認証器ごとの識別子
        'aaguid'                => '',
        // credentialのサイズを示した値
        'credentialIdLength'    => '',
        // 公開鍵ごと割り振られているユニークなID
        'credentialId'          => '',
        // RP側で指定した鍵のアルゴリズムに従って生成された公開鍵の要素
        'credentialPublicKey'   => '',
    );

    /**
     * attestedCredentialDataをバイナリからパースする
     * @param array $binary
     * @throws WebAuthnException
     */
    public function __construct(array $binary) {
        // 認証器毎に識別子取得
        $this->attestedCredentialData['aaguid'] = $this->parseAAGUID($binary);
        // 認証情報のサイズ
        $this->attestedCredentialData['credentialIdLength'] = $this->parseCredentialIdLength($binary);
        // 公開鍵毎に割り振られるユニークID
        $this->attestedCredentialData['credentialId'] = $this->parseCredentialIdLength($binary);
        // 公開鍵情報
        $this->attestedCredentialData['credentialPublicKey'] = $this->parseCredentialPublicKey($binary);
    }

    /**
     * AAGUIDをByteArrayから切り出して返す
     *
     * @param array $binary
     * @return string
     */
    public function parseAAGUID(array $binary): string
    {
        return $this->byteArrayToHex(
            array_slice(
                $binary,
                self::$_AAGUID_ADDRESS[0],
                self::$_AAGUID_ADDRESS[1]
            )
        );
    }

    /**
     * CredentialIdLenghtをByteArrayから切り出して返す
     *
     * @param array $binary
     * @return string
     */
    public function parseCredentialIdLength(array $binary): string
    {
        return $this->byteArrayToEndian(
            array_slice(
                $binary,
                self::$_CREDENTIAL_ID_LENGTH_ADDRESS[0],
                self::$_CREDENTIAL_ID_LENGTH_ADDRESS[1]
            )
        );
    }

    /**
     * CredentialIdをByteArrayから切り出して返す
     *
     * @param array $binary
     * @return string
     */
    public function parseCredentialId(array $binary): string
    {
        return array_slice(
            $binary,
            self::$_CREDENTIAL_ID_ADDRESS,
            $this->attestedCredentialData['credentialIdLength']
        );
    }

    /**
     * CredentialPublicKeyをByteArrayから切り出して返す
     *
     * @param array $binary
     * @return array
     */
    public function parseCredentialPublicKey(array $binary): array
    {
        return array_slice(
            $binary,
            self::$_CREDENTIAL_ID_ADDRESS
             + $this->attestedCredentialData['credentialIdLength']
        );
    }

    /**
     * attestedCredentialDataを返す
     *
     * @return array
     */
    public function getAttestedCredentialData(): array
    {
        return $this->attestedCredentialData;
    }

    /**
     * aaguidを返す
     *
     * @return string
     */
    public function getAaguid(): string
    {
        return $this->attestedCredentialData['aaguid'];
    }

    /**
     * credentialIdLengthを返す
     *
     * @return string
     */
    public function getCredentialIdLength(): string
    {
        return $this->attestedCredentialData['credentialIdLength'];
    }

    /**
     * credentialIdを返す
     *
     * @return string
     */
    public function getCredentialId(): string
    {
        return $this->byteArrayToHex(
        // return $this->byteArrayToString(
            $this->attestedCredentialData['credentialId']
        );
    }

    /**
     * credentialPublicKeyを返す
     *
     * @return array
     */
    public function getCredentialPublicKey(): array
    {
        return $this->byteArrayToCBORObject(
            $this->attestedCredentialData['credentialPublicKey']
        );
    }

    /**
     * authDataのバイナリ値から公開鍵情報を復元する
     *
     * @param string $rawid
     * @return bool
     */
    public function checkCredentialId(string $rawId): bool
    {
        // credentialIdとrawIdが一致しているか確認
        if ($rawId !== $this->byteArrayToString($this->attestedCredentialData['credentialId'])) {
            $this->setError('Credentail ID ERROR : ', 'invalid!!! not match credential id');
            return false;
        }

        return true;
    }
}
