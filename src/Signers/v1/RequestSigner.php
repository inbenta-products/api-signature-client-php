<?php

namespace Inbenta\ApiSignature\Signers\v1;

use Inbenta\ApiSignature\Signers\Signer;

class RequestSigner extends Signer
{
    protected $requestBaseString = '';

    /**
     * Builds the base string of the signature hash
     *
     * @param array $signatureElements
     * @return string
     */
    protected function buildBaseString(array $signatureElements)
    {
        if (empty($this->requestBaseString)) {
            $this->requestBaseString = '';
            $method = !empty($signatureElements['method']) ? $signatureElements['method'] : 'GET';
            // Remove any spaces or slashes (either at the start or the end) to prevent false negatives
            $urlPath = !empty($signatureElements['urlPath']) ? trim($signatureElements['urlPath'], ' /') : '';
            $query = !empty($signatureElements['query']) ? $signatureElements['query'] : [];
            $body = !empty($signatureElements['body']) ? $signatureElements['body'] : '';
            $timestamp = !empty($signatureElements['timestamp']) ? $signatureElements['timestamp'] : 0;

            // build base string elements
            $requestElements = [
                $method,
                urlencode($urlPath),
                $this->buildQueryString($query),
                urlencode($body),
                $timestamp,
                $this->signatureVersion
            ];
            // Remove empty elements from the final signature
            $requestElements = array_filter($requestElements, function ($element) {
                return !empty($element);
            });
            // join base string elements
            $this->requestBaseString = implode('&', $requestElements);
        }
        return $this->requestBaseString;
    }

    /**
     * Returns the encoded query string
     *
     * @param array $queryParams
     * @return string
     */
    protected function buildQueryString(array $queryParams)
    {
        $encodedQueryString = '';

        if (!empty($queryParams)) {
            // sort to grant query params order
            ksort($queryParams);
            $queryStringEncodedElements = [];
            foreach ($queryParams as $key => $value) {
                $value = urldecode(json_encode($value, JSON_UNESCAPED_SLASHES));
                $queryStringEncodedElements[$key] = "{$key}={$value}";
            }
            $encodedQueryString = rawurlencode(implode('&', $queryStringEncodedElements));
        }

        return $encodedQueryString;
    }
}
