<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Exception\BlacklistedAlgorithmException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\AbstractKey;

/**
 * An abstract class implementing an algorithm factory.
 *
 * Extending classes MUST declare two static properties:
 *
 * - $cache: an associative array holding algorithm IDs as the keys, and class names implementing those algorithms as
 *   the value.
 * - $initialized: a boolean telling whether this factory has been initialized (a constructor has been called) or not.
 *
 * @package simplesamlphp/xml-security
 */
abstract class AbstractAlgorithmFactory
{
    protected static array $cache = [];
    protected static bool $initialized = false;


    /**
     * An array of blacklisted algorithms.
     *
     * @var string[]
     */
    protected array $blacklist = [];


    /**
     * Build a factory that creates algorithms.
     *
     * @param string[]|null $blacklist A list of algorithms forbidden for their use.
     * @param string[]|null $defaults A list of known implementations.
     */
    public function __construct(array $blacklist = null, array $defaults = null)
    {
        if ($blacklist !== null) {
            $this->blacklist = $blacklist;
        }

        // initialize the cache for supported algorithms per known implementation
        if (!static::$initialized && $defaults !== null) {
            foreach ($defaults as $algorithm) {
                /** @var \SimpleSAML\XMLSecurity\Alg\AlgorithmInterface $algorithm */
                foreach ($algorithm::getSupportedAlgorithms() as $algId) {
                    if (array_key_exists($algId, static::$cache)) {
                        /*
                         * If the key existed before initialization, that means someone registered a handler for this
                         * algorithm, so we should respect that and skip registering the default here.
                         */
                        continue;
                    }
                    static::$cache[$algId] = $algorithm;
                }
            }
            static::$initialized = true;
        }
    }


    /**
     * Get a new object implementing the given algorithm.
     *
     * @param string $algId The identifier of the algorithm desired.
     * @param \SimpleSAML\XMLSecurity\Key\AbstractKey $key The key to use with the given algorithm.
     *
     * @return \SimpleSAML\XMLSecurity\Alg\AlgorithmInterface An object implementing the given algorithm.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If an error occurs, e.g. the given algorithm
     * is blacklisted, unknown or the given key is not suitable for it.
     */
    public function getAlgorithm(string $algId, AbstractKey $key): AlgorithmInterface
    {
        Assert::true(
            !in_array($algId, $this->blacklist, true),
            sprintf('Blacklisted algorithm: \'%s\'.', $algId),
            BlacklistedAlgorithmException::class,
        );
        Assert::keyExists(
            static::$cache,
            $algId,
            sprintf('Unknown or unsupported algorithm: \'%s\'.', $algId),
            UnsupportedAlgorithmException::class,
        );

        return new static::$cache[$algId]($key, $algId);
    }


    /**
     * Get the name of the abstract class our algorithm implementations must extend.
     *
     * @return string
     */
    abstract protected static function getExpectedParent(): string;


    /**
     * Register an implementation of some algorithm(s) for its use.
     *
     * @param string $className
     */
    public static function registerAlgorithm(string $className): void
    {
        $parent = static::getExpectedParent();
        Assert::subclassOf(
            $className,
            $parent,
            'Cannot register algorithm "' . $className . '", must implement ' . $parent . '.',
        );

        /** @var \SimpleSAML\XMLSecurity\Alg\AlgorithmInterface $className */
        foreach ($className::getSupportedAlgorithms() as $algId) {
            static::$cache[$algId] = $className;
        }
    }
}
