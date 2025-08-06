<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg\Encryption;

use SimpleSAML\XMLSecurity\Alg\AbstractAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\AbstractKey;

/**
 * Factory class to create and configure encryption algorithms.
 *
 * @package simplesamlphp/xml-security
 */
final class EncryptionAlgorithmFactory extends AbstractAlgorithmFactory
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
     * Defaults to 3DES.
     *
     * @var string[]
     */
    protected array $blacklist = [
        C::BLOCK_ENC_3DES,
    ];


    /**
     * Build a factory that creates encryption algorithms.
     *
     * @param array|null $blacklist A list of algorithms forbidden for their use.
     */
    public function __construct(array $blacklist = null)
    {
        parent::__construct(
            $blacklist,
            [
                TripleDES::class,
                AES::class,
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
        return EncryptionAlgorithmInterface::class;
    }


    /**
     * Get a new object implementing the given encryption algorithm.
     *
     * @param string $algId The identifier of the algorithm desired.
     * @param \SimpleSAML\XMLSecurity\Key\AbstractKey $key The key to use with the given algorithm.
     *
     * @return \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface An object implementing the given
     * algorithm.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If an error occurs, e.g. the given algorithm
     * is blacklisted, unknown or the given key is not suitable for it.
     */
    public function getAlgorithm(string $algId, AbstractKey $key): EncryptionAlgorithmInterface
    {
        return parent::getAlgorithm($algId, $key);
    }
}
