<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg\Encryption;

use SimpleSAML\XMLSecurity\Backend\OpenSSL;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

/**
 * Class implementing the AES family of encryption algorithms (both AES-CBC and AES-GCM).
 *
 * @package simplesamlphp/xml-security
 */
class AES extends AbstractEncryptor
{
    /** @var string */
    protected string $default_backend = OpenSSL::class;


    /**
     * AES constructor.
     *
     * @param \SimpleSAML\XMLSecurity\Key\SymmetricKey $key The symmetric key to use.
     * @param string $algId The identifier of this algorithm.
     */
    public function __construct(SymmetricKey $key, string $algId = C::BLOCK_ENC_AES256_GCM)
    {
        parent::__construct($key, $algId);
    }

    /**
     * @inheritDoc
     */
    public static function getSupportedAlgorithms(): array
    {
        return [
            C::BLOCK_ENC_AES128,
            C::BLOCK_ENC_AES192,
            C::BLOCK_ENC_AES256,
            C::BLOCK_ENC_AES128_GCM,
            C::BLOCK_ENC_AES192_GCM,
            C::BLOCK_ENC_AES256_GCM,
        ];
    }
}
