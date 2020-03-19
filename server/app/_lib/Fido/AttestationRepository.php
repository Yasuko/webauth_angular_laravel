<?php

namespace App\_lib\Fido;

use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

/**
 * クライアントからのauthDataを分解
 * 応答の正当性、認証器の正当性、公開鍵の復元と正当性
 * 以上を行うヘルパー
 * 
 */

class AttestationRepository
{
    use ConvertHelper;
    use ErrorHelper;

    private $AttestationObject = null;
    private $AuthenticatorData = null;
    private $AttestedCredentialData = null;
    private $clientDataJson = null;
    private $FMTFormat = null;

    private $credentialData     = array();

    public function setup(array $credential): self
    {
        if ($credential !== null) {
            $this->credentialData = $credential;
        }
        return $this;
    }

    /**
     * AuthenticatorDataクラスを返す
     *
     * @return object
     */
    public function callAuthenticatorDataRepository(): object
    {
        return $this->AuthenticatorData;
    }

    /**
     * clientDataJSONインスタンスを返す
     * 
     * 先にパースが終わっていないと
     * 空の配列が返る
     * @return object
     */
    public function callClientDataJsonRepository(): object
    {
        return $this->clientDataJson;
    }

    /**
     * FMTFormatインスタンスを返す
     *
     * @return object
     */
    public function callFMTFormat(): object
    {
        return $this->FMTFormat;
    }

    /**
     * 登録処理の簡単セットアップ
     *
     * @param array $credential
     * @return bool | self
     */
    public function easySetupToRegist(array $credential, array $keys)
    {
        $this->setup($credential);

        // clientDataJsonをパース
        $this->parseClientData()
            // clientDataJsonインスタンスを作成
            ->callClientDataJsonRepository();

        // clientDataに格納されているchallengeの検証
        if (!$this->callClientDataJsonRepository()->checkChallenge($keys)) {
            return false;
        }
        // AttestationObjectデータをヘルパーに渡し
        // AttestationObject、AuthenticatorDataをそれぞれパースする
        $this->parseAttestationObject()
            // FMTFormatインスタンスを作成
            ->setupFMTFormat()
            // FMTFormatインスタンスの呼び出し
            ->callFMTFormat()
            // フォーマットを判定し公開鍵を作成
            ->createPublicKeyToFormat();
        
        return $this;
    }

    /**
     * 認証処理の簡単セットアップ
     *
     * @param array $credential
     * @param object $user
     * @param array $keys
     * @return string | int
     */
    public function easySetupToAuth(array $credential, object $user, array $keys)
    {
        // AttestationRepository取得
        $this->setup($credential)
                ->parseClientData();

        // clientDataに格納されているchallengeの検証
        if (!$this->callClientDataJsonRepository()->checkChallenge($keys)) {
            return 'not challenge';
        }
        // AuthenticatorDataパースする
        $fmt = $this->parseAuthenticatorData()
            // FMTFormatインスタンスを作成
            ->setupFMTFormat()
            // FMTFormatクラスの呼び出し
            ->callFMTFormat();

        // SSLKeyの有効性確認
        if (!$fmt->verifiOpenSSLKey($user->public_key)) {
            return 'not sslkey';
        };

        // カウンターチェック
        $count = $this->callAuthenticatorDataRepository()
                    ->checkSignCount($user->count);
        if (!$count) {
            return 'not counter';
        }

        return (int)$count;
    }

    /**
     * データベースに保存するようのcredentialデータを返す
     *
     * @param object $user
     * @return array
     */
    public function getCredentialSaveData(object $user): array
    {
        return array(
            'id'                => $user->userid,
            'app_user_id'       => $user->id,
            'count'             => $this->AuthenticatorData->getSignCount(),
            'credential_id'     => base64_encode($this->AuthenticatorData->getRawId()),
            'public_key_cose'   => $this->FMTFormat->getPublicKeyPem()
        );
    }

    /**
     * 認証器から受け取ったattestationObjectをパースし保持する
     *
     * 受け取ったattestationObjectを保持し
     * attestationObjectからauthenticatorDataを生成し保持する
     * 
     * @return self
     */
    public function parseAttestationObject(): self
    {
        // AttestatinoObjectをヘルパー関数に渡しパース
        $this->AttestationObject = 
            new \App\_lib\Fido\Attestation\AttestationObject(
                $this->decodeAttestationData(
                    $this->credentialData['response']['attestationObject']
                )
            );
        // AuthencatorDataをヘルパー関数に渡しパース
        $this->AuthenticatorData = 
            new \App\_lib\Fido\Attestation\AuthenticatorData(
                $this->AttestationObject->getAuthData(),
                $this->credentialData
            );

        // AttestedCredentialData Flagが有効な場合
        // AttestedCredentailDataオブジェクトを作成
        if ($this->AuthenticatorData
                ->getAttestationFlags()
                ->checkAttestedCredentialFlag()) {
            $this->AttestedCredentialData = 
                new \App\_lib\Fido\Attestation\AttestedCredentialData(
                    $this->AuthenticatorData->getAttestedCredentialData()
                );
        }


        return $this;
    }


    public function parseAuthenticatorData(): self
    {
        // AuthencatorDataをヘルパー関数に渡しパース
        $this->AuthenticatorData = new \App\_lib\Fido\Attestation\AuthenticatorData(
            $this->base64urlDecode(
                $this->credentialData['response']['authenticatorData']
            ),
            $this->credentialData
        );

        return $this;  
    }

    /**
     * clientDataJsonを保持する
     *
     * @return self
     */
    public function parseClientData(): self
    {
        $this->clientDataJson = 
                    new \App\_lib\Fido\Attestation\ClientDataJson(
                        $this->credentialData['response']['clientDataJSON']
                    );

        return $this;
    }

    /**
     * FMTフォーマットを管理するオブジェクトをセットアップ
     *
     * @return self
     */
    public function setupFMTFormat(): self
    {
        $this->FMTFormat = new \App\_lib\Fido\Attestation\FMTFormat(
            $this->AttestationObject,
            $this->AuthenticatorData,
            $this->AttestedCredentialData,
            $this->clientDataJson,
        );
        return $this;
    }

}

