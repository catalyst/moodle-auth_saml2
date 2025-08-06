<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Key;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Exception\IOException;

use function error_clear_last;
use function file_get_contents;

/**
 * A class representing an asymmetric key.
 *
 * This class can be extended to implement public or private keys.
 *
 * @package simplesamlphp/xml-security
 */
abstract class AsymmetricKey extends AbstractKey
{
    /**
     * Read a key from a given file.
     *
     * @param string $file The path to a file where the key is stored.
     *
     * @return string The key material.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If the given file cannot be read.
     */
    protected static function readFile(string $file): string
    {
        error_clear_last();
        $key = @file_get_contents($file);

        if ($key === false) {
            $e = error_get_last();
            $error = $e['message'] ?: "Check that the file exists and can be read.";
            throw new IOException("File '$file' was not loaded;  $error");
        }

        return $key;
    }
}
