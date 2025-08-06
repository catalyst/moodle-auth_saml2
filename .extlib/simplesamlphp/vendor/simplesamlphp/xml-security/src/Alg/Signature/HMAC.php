<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg\Signature;

use SimpleSAML\XMLSecurity\Backend\HMAC as HMAC_Backend;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

/**
 * Class implementing the HMAC signature algorithm
 *
 * @package simplesamlphp/xml-security
 */
final class HMAC extends AbstractSigner implements SignatureAlgorithmInterface
{
    /** @var string */
    protected string $default_backend = HMAC_Backend::class;


    /**
     * HMAC constructor.
     *
     * @param \SimpleSAML\XMLSecurity\Key\SymmetricKey $key The symmetric key to use.
     * @param string $algId The identifier of this algorithm.
     */
    public function __construct(SymmetricKey $key, string $algId = C::SIG_HMAC_SHA256)
    {
        parent::__construct($key, $algId, C::$HMAC_DIGESTS[$algId]);
    }


    /**
     * @inheritDoc
     */
    public static function getSupportedAlgorithms(): array
    {
        return array_keys(C::$HMAC_DIGESTS);
    }
}
