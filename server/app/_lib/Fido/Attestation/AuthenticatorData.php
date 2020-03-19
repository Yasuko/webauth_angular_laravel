<?php

namespace App\_lib\Fido\Attestation;

use App\_lib\Fido\Helper\BinaryHelper;
use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

class AuthenticatorData {
    
    use BinaryHelper;
    use ConvertHelper;
    use ErrorHelper;

    private $attestationFlags;

    private $authDataByteArray;
    private $authenticatorDataCBOR;


    private $credential     = array(
        'id'            => '',
        'rawId'         => '',
        'signature'    => '',
    );

    private $authenticatorData = array(
        'rpId'                      => '',
        'flags'                     => '',
        'signCount'                 => 0,
        'attestedCredentialData'    => '',
    );

    /**
     * authenticatorDataをバイナリからパースする
     * @param string $binary
     * @throws WebAuthnException
     */
    public function __construct(string $binary, array $credential) {
        
        $this->parseCredential($credential);
        
        // Binary文字列を保持
        $this->authenticatorDataCBOR = $binary;

        // authenticatorDataの切り出しと配列に保存
        $this->parseAuthenticatorData();

        // Flags管理オブジェクト作成
        $this->attestationFlags =
            new \App\_lib\Fido\Attestation\AttestationFlag($this->authDataByteArray[32]);
     
        // AttestedCredentialData Flagが有効な場合
        // attestedCredentialDataを切り出す
        if ($this->attestationFlags->checkAttestedCredentialFlag()) {
            // attestedCredentialDataの切り出し
            // 公開鍵の抽出にすぐ回るので個別に保存しなく良いかも？
            $this->authenticatorData['attestedCredentialData'] = array_slice($this->authDataByteArray, 37);
        }
    }

    public function parseCredential(array $credential): self
    {
        // credentailのIDとrawIDを保持
        // 文字列がbase64URLEncodeされているためデコードしておく
        $this->credential['id'] = $this->base64urlDecode($credential['id']);
        $this->credential['rawId'] = $this->base64urlDecode($credential['rawId']);

        // signatureが有効な場合、signatureも切り出す
        if (array_key_exists('signature', $credential['response'])) {
            $this->credential['signature'] = $this->base64urlDecode($credential['response']['signature']);
        }
        return $this;
    }

    /**
     * authenticatorDataをバイナリから復元
     *
     * [rpid, flags, signCount, attestedCredentialData]を取得し
     * authenticatorDataに格納する
     * 
     * @return self
     */
    public function parseAuthenticatorData(): self
    {
        // バイナリデータ文字列をunsigned charフォーマットで抽出
        $this->authDataByteArray = array_values(unpack('C*', $this->authenticatorDataCBOR));

        // rpIdの切り出し
        $this->authenticatorData['rpId'] = array_slice($this->authDataByteArray, 0, 32);
        
        // signCountの切り出し
        $_count = array_slice($this->authDataByteArray, 33, 4);
        $this->authenticatorData['signCount'] = $this->hexToDec($this->byteArrayToHex($_count));
        // $this->authenticatorData['signCount'] = $this->byteArrayToString($_count);

        return $this;
    }

    /**
     * rawIdを返す
     *
     * @return string
     */
    public function getRawId(): string
    {
        return $this->credential['rawId'];
    }

    /**
     * sigunatureを返す
     * 認証時のみデータが存在する
     * 
     * @return string
     */
    public function getSignature(): string
    {
        return $this->credential['signature'];
    }

    /**
     * authenticatorDataを返す
     *
     * @return array
     */
    public function callAuthenticatorData(): array
    {
        return $this->authenticatorData;
    }

    /**
     * AuthenticatorDataのCBOR文字列を返す
     *
     * @return string
     */
    public function getAuthenticatorDataCBOR(): string
    {
        return $this->authenticatorDataCBOR;
    }

    /**
     * rpidを返す
     *
     * @return array
     */
    public function getRPId(): array
    {
        return $this->authenticatorData['rpId'];
    }

    /**
     * signCountを返す(認証回数)
     *
     * @return integer
     */
    public function getSignCount(): int
    {
        return $this->authenticatorData['signCount'];
    }

    /**
     * attestationFlagsを返す
     *
     * @return object
     */
    public function getAttestationFlags(): object
    {
        return $this->attestationFlags;
    }

    /**
     * attestedCredentialDataを返す
     *
     * @return array
     */
    public function getAttestedCredentialData(): array
    {
        return $this->authenticatorData['attestedCredentialData'];
    }

    /**
     * 認証回数を確認
     *
     * 前回認証時より回数が増えており
     * 尚且０ではない
     * @param int $count
     * @return bool | int
     */
    public function checkSignCount(int $count)
    {
        if ($this->authenticatorData['signCount'] === 0) {
            return $this->authenticatorData['signCount'];
        } else if ($count > 0 && $this->authenticatorData['signCount'] > $count) {
            return $this->authenticatorData['signCount'];
        } else {
            return false;
        }
    }

}
