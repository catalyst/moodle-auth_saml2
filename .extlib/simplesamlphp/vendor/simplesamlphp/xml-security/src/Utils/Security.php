<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Utils;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;

use function hash_equals;

/**
 * A collection of security-related functions.
 *
 * @package simplesamlphp/xml-security
 */
class Security
{
    /**
     * Compare two strings in constant time.
     *
     * This function allows us to compare two given strings without any timing side channels
     * leaking information about them.
     *
     * @param string $known The reference string.
     * @param string $user The user-provided string to test.
     *
     * @return bool True if both strings are equal, false otherwise.
     */
    public static function compareStrings(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }


    /**
     * Compute the hash for some data with a given algorithm.
     *
     * @param string $alg The identifier of the algorithm to use.
     * @param string $data The data to digest.
     * @param bool $encode Whether to bas64-encode the result or not. Defaults to true.
     *
     * @return string The (binary or base64-encoded) digest corresponding to the given data.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If $alg is not a valid
     *   identifier of a supported digest algorithm.
     */
    public static function hash(string $alg, string $data, bool $encode = true): string
    {
        Assert::keyExists(
            C::$DIGEST_ALGORITHMS,
            $alg,
            'Unsupported digest method "' . $alg . '"',
            InvalidArgumentException::class,
        );

        $digest = hash(C::$DIGEST_ALGORITHMS[$alg], $data, true);
        if ($encode) {
            $digest = base64_encode($digest);
        }
        return $digest;
    }
}
