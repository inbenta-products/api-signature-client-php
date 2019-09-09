<?php

namespace Inbenta\ApiSignature\Signers\v1;

use Inbenta\ApiSignature\Signers\Signer;

class ResponseSigner extends Signer
{
    protected $responseBaseString = '';

    /**
     * Builds the base string from response elements that will be hashed to match response signature
     *
     * @param array $signatureElements
     * @return string
     */
    protected function buildBaseString(array $signatureElements)
    {
        if (empty($this->responseBaseString)) {
            $this->responseBaseString = '';

            $body = !empty($signatureElements['body']) ? $signatureElements['body'] : '';
            $timestamp = !empty($signatureElements['timestamp']) ? $signatureElements['timestamp'] : 0;

            $responseElements = [
                $this->signatureVersion,
                $timestamp,
                urlencode(json_encode($body)),
            ];

            $this->responseBaseString = implode('&', $responseElements);
        }
        return $this->responseBaseString;
    }
}
