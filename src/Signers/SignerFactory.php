<?php

namespace Inbenta\ApiSignature\Signers;

use Inbenta\ApiSignature\Exceptions\SignatureClientException;

class SignerFactory
{
    public static function build($signer, $signatureKey, $signatureVersion = 'v1')
    {
        $signerClass = "Inbenta\\ApiSignature\\Signers\\{$signatureVersion}\\{$signer}";
        if (!class_exists($signerClass)) {
            throw new SignatureClientException("{$signer} version {$signatureVersion} not implemented");
        }
        return new $signerClass($signatureKey, $signatureVersion);
    }
}
