<?php

namespace Inbenta\ApiSignature\Signers;

use Inbenta\ApiSignature\Exceptions\SignatureClientException;

abstract class Signer
{
    /**
     * The algorithm that will be used to generate the hash
     */
    const HASH_ALGORITHM = 'sha256';

    /**
     * Stores credentials required for the Inbenta Signature Protocol
     *
     * @var string
     */
    protected $signatureKey;

    /**
     * Signature version (v1 for now)
     *
     * @var string
     */
    protected $signatureVersion;

    /**
     * Signature versions in the form of <versionId> => <is Valid>
     *
     * @var array
     */
    public static $ALLOWED_VERSIONS = [
        'v1' => true,
    ];

    /**
     * Constructor. Gets Instance credentials.
     *
     * @param string $signatureKey
     * @param string $signatureVersion [v1]
     */
    public function __construct($signatureKey = null, $signatureVersion = 'v1')
    {
        if (!is_null($signatureKey)) {
            $this->signatureKey = $signatureKey;
        }
        if (!is_null($signatureVersion)) {
            $this->signatureVersion = $signatureVersion;
        }
    }

    /**
     * Returns current signature version (v1 by default)
     *
     * @return string
     */
    public function getSignatureVersion()
    {
        return $this->signatureVersion;
    }

    /**
     * Signs the base string applying the hash algorithm
     *
     * @param array $signatureElements
     * @param string $signatureKey
     * @param string $signatureVersion
     * @return string
     */
    public function sign(array $signatureElements, $signatureKey = null, $signatureVersion = null)
    {
        $signatureKey = (is_null($signatureKey)) ? $this->signatureKey : $signatureKey;
        $signatureVersion = (is_null($signatureVersion)) ? $this->signatureVersion : $signatureVersion;
        $this->validateVersion($signatureVersion);
        $baseString = $this->buildBaseString($signatureElements);
        return hash_hmac(static::HASH_ALGORITHM, $baseString, $signatureKey);
    }

    /**
     * Ensure that the given protocol version is known and that still applies
     *
     * @param string $signatureVersion
     * @return string
     */
    public function validateVersion($signatureVersion)
    {
        if (!isset(self::$ALLOWED_VERSIONS[$signatureVersion])) {
            throw new SignatureClientException('Signature version not recognized');
        }
        if (self::$ALLOWED_VERSIONS[$signatureVersion] === false) {
            throw new SignatureClientException("Signature version {$signatureVersion} has been deprecated");
        }
        return $signatureVersion;
    }

    /**
     * Builds the base string of the signature hash
     *
     * @param array $signatureElements
     * @return string
     */
    abstract protected function buildBaseString(array $signatureElements);
}
