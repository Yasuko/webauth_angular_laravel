<?php

namespace App\_lib\Fido\Format;

class NoneHelper
{

    private $FMTFormat;


    public function __construct(
        \App\_lib\Fido\Attestation\FMTFormat $fmtformat)
    {
        // クラスオブジェクトの保存
        $this->FMTFormat = $fmtformat;

    }

    public function buildCertificatePem(): self
    {
        return $this;
    }

    public function validateAttestation($clientDataHash)
    {
        return true;
    }

}