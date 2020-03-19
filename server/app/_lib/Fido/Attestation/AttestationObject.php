<?php

namespace App\_lib\Fido\Attestation;
use App\_lib\Fido\Helper\ConvertHelper;
use App\_lib\Fido\Helper\ErrorHelper;

class AttestationObject {
    use ConvertHelper;
    use ErrorHelper;

    private $attestationObject = array(
        'fmt'          => '',       // attestationの証明方法
        'authData'     => '',       // 認証器からの情報
        'attStmt'      => array(),  // attestationの検証データ
    );

    /**
     * Binaryデータを受け取りattestationObjectにパースする
     *
     * @param array $authData
     */
    public function __construct(array $authData) {

        $this->attestationObject = array(
            'fmt'       => $authData['fmt'],
            'authData'  => $authData['authData'],
            'attStmt'   => $authData['attStmt'],
        );
    }

    /**
     * attestationObejctを取得
     *
     * @return array
     */
    public function getAttestationObject(): array
    {
        return $this->attestationObject;
    }

    /**
     * fmtデータを返す
     *
     * @return string
     */
    public function getFmt(): string
    {
        return $this->attestationObject['fmt'];
    }

    /**
     * authDataを返す
     * 戻り文字列はバイナリデータ
     *
     * @return string
     */
    public function getAuthData(): string
    {
        return $this->attestationObject['authData']->get_byte_string();
    }

    /**
     * attStmtを返す
     *
     * @return array
     */
    public function getAttStmt(): array
    {
        return $this->attestationObject['attStmt'];
    }

    /**
     * FMTの一致確認
     *
     * @return boolean
     */
    public function checkFmt($chk): bool
    {
        return ($this->attestationObject['fmt'] === $chk) ? true : false;
    }

}
