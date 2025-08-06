<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg\Signature;

use SimpleSAML\XMLSecurity\Alg\AbstractAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\AbstractKey;

/**
 * Factory class to create and configure digital signature algorithms.
 *
 * @package simplesamlphp/xml-security
 */
final class SignatureAlgorithmFactory extends AbstractAlgorithmFactory
{
    /**
     * A cache of algorithm implementations indexed by algorithm ID.
     *
     * @var string[]
     */
    protected static array $cache = [];

    /**
     * Whether the factory has been initialized or not.
     *
     * @var bool
     */
    protected static bool $initialized = false;

    /**
     * An array of blacklisted algorithms.
     *
     * Defaults to RSA-SHA1 & HMAC-SHA1 due to the weakness of SHA1.
     *
     * @var string[]
     */
    protected array $blacklist = [
        C::SIG_RSA_SHA1,
        C::SIG_HMAC_SHA1,
    ];


    /**
     * Build a factory that creates signature algorithms.
     *
     * @param array|null $blacklist A list of algorithms forbidden for their use.
     */
    public function __construct(array $blacklist = null)
    {
        parent::__construct(
            $blacklist,
            [
                RSA::class,
                HMAC::class,
            ],
        );
    }


    /**
     * Get the name of the abstract class our algorithm implementations must extend.
     *
     * @return string
     */
    protected static function getExpectedParent(): string
    {
        return SignatureAlgorithmInterface::class;
    }


    /**
     * Get a new object implementing the given digital signature algorithm.
     *
     * @param string $algId The identifier of the algorithm desired.
     * @param \SimpleSAML\XMLSecurity\Key\AbstractKey $key The key to use with the given algorithm.
     *
     * @return \SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmInterface An object implementing the given
     * algorithm.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException If an error occurs, e.g. the given
     * algorithm is blacklisted, unknown or the given key is not suitable for it.
     */
    public function getAlgorithm(string $algId, AbstractKey $key): SignatureAlgorithmInterface
    {
        return parent::getAlgorithm($algId, $key);
    }
}
