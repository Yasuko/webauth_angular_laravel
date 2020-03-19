<?php

namespace App\_lib\Fido;

use App\_lib\Fido\Helper\BinaryHelper;
use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

/**
 * clientCredentialOptionsを作成するヘルパークラス
 * 
 * クライアントの認証器に鍵を作成させるためのOptionを作成し返す
 * 登録時と認証時でOptionの内容が異なるので注意
 * 
 * 
 * challengeキー等ハッシュの作成部分が
 * 仕様に則っていないので「暗号学的に安全なハッシュキー」
 * という仕様に合わせて拡張が恐らく必要になる
 * 「pubKeyCredParams」「authenticatorSelection」は
 * 今後のWebAuthの仕様変更、クライアント側の認証器の変更追加
 * などの理由により変更の可能性が高いので注意
 */

class CredentialRepository
{
    use BinaryHelper;
    use ConvertHelper;
    use ErrorHelper;

    // RP情報
    private $relyingParty = array(
        // RPの識別子（ドメインなど）
        'id'    => '',
        // RPの名前（サービス事業者など）
        'name'  => '',
    );

    /**
     * ClientCredentailOptionのデフォルト値
     *
     * @var array
     */
    private $clientCredentialDefault = array(
        'username'  => 'guest',   // 現在登録処理を行っているユーザー名
        'displayName' => 'guest', // ユーザーが画面表示用に使っている値
        'authenticatorSelection'  => array( // RPから認証器へ要求事項を示すオブジェクト
            // 認証機にユーザー情報を保存するかを指定
            'requireResidentKey'        => false,
            // ビルトイン認証器「platform」外部接続認証器「cross-platform」
            'authenticatorAttachment'   => 'cross-platform',
            // 必須「required」、優先「preferred」、検出されない「discouraged」
            'userVerification'          => 'required',
        ),
        // 認証器からの公開鍵作成時の情報がRPとしてどのくらい必要か
        'attestation'   => 'direct',
        // 認証のタイムアウト、整数値（ms）
        'timeout'       => 200000
    );

    private $clientCredentialOption = array(
        'rp'    => array(   // RPの情報を含むオブジェクト
            'id'    => '',  // RPの識別子（ドメインなど）
            'name'  => '',  // RPの名前（サービス事業者名など）
        ),
        'user'  => array(   // 公開鍵を作るユーザー名を含むオブジェクト
            'id'            => '',  // ユーザー識別子を示す値
            'name'          => '',  // ユーザー名（メールアドレスなど）
            'displayName'   => '',  // ユーザーに表示するための名前
        ),

        'challenge' => '',  // FIDO2サーバーから渡ってくるランダム値

        'pubKeyCredParams'  => array(), // 公開鍵作成時の情報を含むオブジェクト

        'authenticatorSelection'    => array(   // RPから認証機へ要求事項を示すオブジェクト
            // 認証機にユーザー情報を保存するかを指定
            'requireResidentKey'        => false,
            // ビルトイン認証器「platform」
            // 外部接続認証器  「cross-platform」
            'authenticatorAttachment'   => '',
            // 必須「required」
            // 優先「preferred」
            // 検出されない「discouraged」
            'userVerification'          => '',
        ),
        // 認証器からの公開鍵作成時の情報がRPとしてどのくらい必要か
        'attestation'   => 'direct',
        // 認証のタイムアウト、整数値（ms）
        'timeout'       => 0,
    );

    // 公開鍵の生成に使用する各種ハッシュキーを作成
    private $hashKeys       = array(
        'registrationId'    => '',
        'userId'            => '',
        'challenge'         => '',
    );

    /**
     * ClientCredentialOptionを返す
     *
     * clientCredentialOption作成処理をしていない場合は
     * crete向けの雛形配列が返る
     * @return array
     */
    public function getClientCredentialOption(): array
    {
        return $this->clientCredentialOption;
    }

    /**
     * clientCredentialOption用の各種ハッシュキーを返す
     *
     * registrationId,userId,challengeの
     * 各ハッシュをまとめた配列を返す
     * @return array
     */
    public function getHashKeys(): array
    {
        return $this->hashKeys;
    }

    /**
     * RP(Relying Party)を保持する
     *
     * @param array $rp
     * @return self
     */
    public function setRP(array $rp): self
    {
        $this->relyingParty['id']   = $rp['id'];
        $this->relyingParty['name']   = $rp['name'];

        return $this;
    }


    /**
     * クラアントからのcredential request情報を保持する
     *
     * 設定されていない（null）情報は全てデフォルトに置き換わる
     * @param object $request
     * @return self
     */
    public function setClientRequest(object $request): self
    {
        $rB = function($r, $key, $auth = false){
            if ($auth) {
                return $r[$key] ?? $this->clientCredentialDefault['authenticatorSelection'][$key];
            } else {
                return $r[$key] ?? $this->clientCredentialDefault[$key];
            }
        };

        $this->clientCredentialDefault = array(
            'username'      => $rB($request, 'username'),
            'displayName'   => $rB($request, 'displayName'),
            'authenticatorSelection'  => array(
                'requireResidentKey'        => $rB($request, 'requireResidentKey', true),
                'authenticatorAttachment'   => $rB($request, 'authenticatorAttachment', true),
                'userVerification'          => $rB($request, 'userVerification', true),
            ),
            'attestation'   => $rB($request, 'attestation'),
            'timeout'       => $rB($request, 'timeout'),
        );
        
        return $this;
    }

    /**
     * 登録用、ClientCredentialOptionを作成する
     *
     * @return self
     */
    public function buildClientCredentialOptionToCreate(): self
    {
        $this->buildHashKeys();
        $clientCredentialOption = array(
            'rp'    => array(
                'id'    => $this->relyingParty['id'],
                'name'  => $this->relyingParty['name'],

            ),
            'user'  => array(
                'id'            => $this->hashKeys['userId'],
                'name'          => $this->clientCredentialDefault['username'],
                'displayName'   => $this->clientCredentialDefault['displayName'],
            ),
            'challenge' => $this->hashKeys['challenge'],
            'pubKeyCredParams'  => array(),
            'authenticatorSelection'    => array(
                'requireResidentKey'        => 
                    $this->clientCredentialDefault['authenticatorSelection']['requireResidentKey'],
                'authenticatorAttachment'   => 
                    $this->clientCredentialDefault['authenticatorSelection']['authenticatorAttachment'],
                'userVerification'          => 
                    $this->clientCredentialDefault['authenticatorSelection']['userVerification'],
            ),
            'attestation'   => $this->clientCredentialDefault['attestation'],
            'timeout'       => $this->clientCredentialDefault['timeout'],
        );
        $this->clientCredentialOption = array(
            'status'            => 'OK',
            'registrationId'    => $this->hashKeys['registrationId'],
            'publicKeyCredentialCreationOptions'    => $clientCredentialOption,
        );
        return $this;
    }

    /**
     * 認証用、ClientCredentialOptionを作成する
     *
     * @param object user
     * @return self
     */
    public function buildClientCredentialOptionToGet(object $user): self
    {
        $this->buildHashKeys($user);
        $clientCredentialOption = array(
            'status'        => 'ok',
            'errorMessage'  => '',
            'challenge'     => $this->hashKeys['challenge'],
            'timeout'       => $this->clientCredentialDefault['timeout'],
            'rpId'          => $this->relyingParty['id'],
            'allowCredentials'    => array(
                array(
                'id'            => $user->credential_id,
                'type'          => 'public-key',
                'transports'    => array(
                    'usb',
                    'nfc',
                    'blu',
                    //'internal'
                )),
            ),
            'userVerification'  => 'required',
        );

        $this->clientCredentialOption = array(
            'status'            => 'OK',
            'assertionId'    => $this->hashKeys['registrationId'],
            'publicKeyCredentialRequestOptions'    => $clientCredentialOption,
        );
        return $this;
    }

    /**
     * EC2KeyTypeの暗号をサポートに追加する
     *
     * 主にFIDO-U2Fで使われているっぽい
     * @return self
     */
    public function addEC2KeyType(): self
    {
        $this->clientCredentialOption['publicKeyCredentialCreationOptions']['pubKeyCredParams'][] = array(
            'type'  => 'public-key',
            'alg'   => -7
        );
        return $this;
    }

    /**
     * RSAKeyTypeの暗号をサポートに追加する
     *
     * 主にWindowsで使われている、指紋認証、WindowsHellowなど
     * @return self
     */
    public function addRSAKeyType(): self
    {
        $this->clientCredentialOption['publicKeyCredentialCreationOptions']['pubKeyCredParams'][] = array(
            'type'  => 'public-key',
            'alg'   => -257
        );
        return $this;
    }


    /**
     * RP情報が一致しているか検証
     *
     * @return boolean
     */
    public function checkRP(array $clientJson, string $type = 'webauthn.create'): bool
    {
        if ($clientJson['type'] === $type) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * registrationId userId challengeにハッシュキーを生成する
     * 作成済みのユーザー情報を渡すと、userIdを再利用する
     *
     * @param object $user
     * @return self
     */
    private function buildHashKeys(object $user=null): self
    {

        // 16byteのRegistrationIdを作成
        $this->hashKeys['registrationId']   = $this->getRandomByte(16);
        // 32byteのチャレンジバッファ作成
        $this->hashKeys['challenge']        = $this->getRandomByte(32);
        // 16byteのUserIDを作成
        $this->hashKeys['userId']           = ($user===null) ? $this->getRandomByte(16) : $user->userid;

        return $this;
    }

    /**
     * ランダムバイト文字列を生成し返す
     *
     * @param int $length
     * @return string
     */
    private function getRandomByte(int $length): string
    {
        return self::base64urlEncode(random_bytes($length));
    }

}

