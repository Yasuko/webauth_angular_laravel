<?php

namespace App\_lib\Fido\Attestation;

use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

class ClientDataJson {
    
    use ConvertHelper;
    use ErrorHelper;

    private $authDataByteArray;

    private $clientDataJson     = array(
        // サーバーで発行したランダムな文字列
        'challenge'     => '',
        // 公開鍵作成時「webauthn.create」認証時「webauthn.get」が設定される
        'type'          => '',
        // WebAuthAPIの呼び出し元
        'origin'        => '',
    );

    private $clientDataORG = '';



    /**
     * clientDataJsonをデコードし保持する
     * 
     * @param string $clientDataJson
     */
    public function __construct(string $clientDataJson) {
        $this->decodeClientDataJson($clientDataJson);
    }

    /**
     * デコード後のclientDataJson配列を返す
     *
     * @return array
     */
    public function getClientData(): array
    {
        return $this->clientDataJson;
    }

    /**
     * challengeを返す
     *
     * @return string
     */
    public function getChallenge(): string
    {
        return $this->clientDataJson['challenge'];
    }

    /**
     * typeを返す
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->clientDataJson['type'];
    }

    /**
     * originを返す
     *
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->clientDataJson['origin'];
    }

    /**
     * clientDataJSONのハッシュ文字列を返す
     *
     * @return string
     */
    public function getClientDataHash(): string
    {
        return hash('sha256', $this->clientDataORG, true);
    }

    /**
     * Challengeが一致しているか検証
     *
     * @param array $keys
     * @return boolean
     */
    public function checkChallenge(array $keys): bool
    {
        if ($keys[1] === $this->getChallenge()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * clientDataJsonをデコードする
     *
     * @param string $clientDataJson
     * @return self
     */
    private function decodeClientDataJson(string $clientDataJson): self
    {
         $this->clientDataORG = $this->base64urlDecode($clientDataJson);
        $_client = json_decode($this->clientDataORG);
        $this->clientDataJson['challenge']  = $_client->challenge;
        $this->clientDataJson['type']       = $_client->type;
        $this->clientDataJson['origin']     = $_client->origin;

        return $this;
    }

}
