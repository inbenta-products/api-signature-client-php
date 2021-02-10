<?php

namespace Inbenta\ApiSignature;

use Inbenta\ApiSignature\Exceptions\SignatureClientException;
use Inbenta\ApiSignature\Signers\SignerFactory;
use Inbenta\ApiSignature\Signers\Signer;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SignatureClient
{
    /**
     * Request signer
     *
     * @var Signer
     */
    protected $requestSigner;

    /**
     * Response signer
     *
     * @var Signer
     */
    protected $responseSigner;

    /**
     * Base url of the API
     *
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * Timestamp that will be used to generate dynamic signatures.
     * It can be set in constructor to get constant signatures (for testing for instance)
     *
     * @var int
     */
    protected $timestamp;

    /* Standard Signature Headers */
    const SIGNATURE_HEADER = 'x-inbenta-signature';
    const SIGNATURE_VERSION_HEADER = 'x-inbenta-signature-version';
    const TIMESTAMP_HEADER = 'x-inbenta-timestamp';

    /**
     * Constructor. Gets Instance credentials.
     *
     * @param string $apiBaseUrl
     * @param string $signatureKey
     * @param string $signatureVersion [v1]
     * @param int $timestamp [time()]
     */
    public function __construct($apiBaseUrl, $signatureKey, $signatureVersion = '', $timestamp = 0)
    {
        $this->apiBaseUrl = parse_url($apiBaseUrl);
        if (empty($this->apiBaseUrl) || empty($this->apiBaseUrl['host'])) {
            throw new SignatureClientException("Invalid URL");
        }
        if (empty($signatureKey)) {
            throw new SignatureClientException("Signature Key required");
        }
        $signatureVersion = !empty($signatureVersion) ? $signatureVersion : 'v1';
        $this->setTimestamp($timestamp);

        $this->requestSigner = SignerFactory::build('RequestSigner', $signatureKey, $signatureVersion);
        $this->responseSigner = SignerFactory::build('ResponseSigner', $signatureKey, $signatureVersion);
    }

    /**
     * Returns current timestamp used to generate the signature
     *
     * @return timestamp
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets timestamp as current time or a pre-fixed value
     *
     * @param int $timestamp
     * @return void
     */
    protected function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp === 0 ? time() : $timestamp;
    }

    /**
     * Returns current signature version (v1 by default)
     *
     * @return string
     */
    public function getSignatureVersion()
    {
        return $this->requestSigner->getSignatureVersion();
    }

    /**
     * Generates a request signature from the url and body (optional) using the provided credentials.
     *
     * @param string $url
     * @param string $body
     * @param string $method
     * @param int $timestamp [time()]
     * @return string
     */
    public function generateRequestSignature($url, $body = null, $method = 'GET', $timestamp = 0)
    {
        $url = parse_url($url);
        if (!empty($this->apiBaseUrl['path']) && $this->apiBaseUrl['path'] !== '/') {
            $url['path'] = preg_replace("!{$this->apiBaseUrl['path']}\/?!", '', $url['path']);
        }
        $query = [];
        if (!empty($url['query'])) {
            parse_str($url['query'], $query);
        }
        $this->setTimestamp($timestamp);

        return $this->requestSigner->sign([
            'method' => $method,
            'urlPath' => $url['path'],
            'query' => $query,
            'body' => $body,
            'timestamp' => $this->timestamp
        ]);
    }

    /**
     * Generates a request signature from the url and body (optional) using the provided credentials.
     *
     * @param string $url
     * @param string $body
     * @param string $method
     * @param int $timestamp [time()]
     * @return array  With all the necessary headers to make a valid request
     */
    public function getHeadersForSignature($url, $body = null, $method = 'GET', $timestamp = 0)
    {
        return [
            self::SIGNATURE_HEADER => $this->generateRequestSignature($url, $body, $method, $timestamp),
            self::SIGNATURE_VERSION_HEADER => $this->getSignatureVersion(),
            self::TIMESTAMP_HEADER => $this->timestamp,
        ];
    }

    /**
     * Validates the signature included in an API response using the provided signature key.
     *
     * @param string $signature
     * @param string $body
     * @return bool
     */
    public function validateResponseSignature($signature, $body)
    {
        $responseSignature = $this->responseSigner->sign([
            'body' => $body,
            'timestamp' => $this->timestamp
        ]);
        return ($signature === $responseSignature);
    }

    /**
     * Signs a PSR-7 Request with the required signature headers
     *
     * @param Request $request
     * @param int $timestamp [time()]
     * @return Request
     */
    public function signRequest(Request $request, $timestamp = 0)
    {
        $requestUrlPath = $request->getUri()->getPath();
        $requestQuery = $request->getUri()->getQuery();
        $requestBody = $request->getBody()->__toString();
        $requestSignature = $this->generateRequestSignature(
            $requestUrlPath.'?'.$requestQuery,
            $requestBody,
            $request->getMethod(),
            $timestamp
        );

        $request = $request->withHeader(self::SIGNATURE_VERSION_HEADER, $this->getSignatureVersion());
        $request = $request->withHeader(self::TIMESTAMP_HEADER, $this->getTimestamp());
        $request = $request->withHeader(self::SIGNATURE_HEADER, $requestSignature);
        return $request;
    }

    /**
     * Validates a PSR-7 Response has a valid signature header
     *
     * @param Response $response
     * @return bool
     */
    public function validateResponse(Response $response)
    {
        $responseSignature = $response->getHeaderLine(self::SIGNATURE_HEADER);
        $responseBody = $response->getBody()->getContents();
        return $this->validateResponseSignature($responseSignature, $responseBody);
    }
}
