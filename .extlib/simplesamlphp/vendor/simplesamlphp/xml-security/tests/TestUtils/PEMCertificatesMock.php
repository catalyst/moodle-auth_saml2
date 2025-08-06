<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\TestUtils;

use Exception;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\Utils\Certificate as CertificateUtils;

use function dirname;
use function file_get_contents;
use function preg_match;
use function preg_replace;

/**
 * Class \SimpleSAML\TestUtils\PEMCertificatesMock
 */
class PEMCertificatesMock
{
    public const CERTIFICATE_DIR = 'resources/certificates';
    public const PASSPHRASE = '1234';

    public const PUBLIC_KEY = 'signed.simplesamlphp.org.crt';
    public const PRIVATE_KEY = 'signed.simplesamlphp.org.key';
    public const OTHER_PUBLIC_KEY = 'other.simplesamlphp.org.crt';
    public const OTHER_PRIVATE_KEY = 'other.simplesamlphp.org.key';
    public const SELFSIGNED_PUBLIC_KEY = 'selfsigned.simplesamlphp.org.crt';
    public const SELFSIGNED_PRIVATE_KEY = 'selfsigned.simplesamlphp.org.key';
    public const BROKEN_PUBLIC_KEY = 'broken.simplesamlphp.org.crt';
    public const BROKEN_PRIVATE_KEY = 'broken.simplesamlphp.org.key';
    public const CORRUPTED_PUBLIC_KEY = 'corrupted.simplesamlphp.org.crt';
    public const CORRUPTED_PRIVATE_KEY = 'corrupted.simplesamlphp.org.key';


    /**
     * @param string $file The file to use
     * @return string
     */
    private static function buildPath(string $file): string
    {
        $base = dirname(dirname(__FILE__));
        return $base . DIRECTORY_SEPARATOR . self::CERTIFICATE_DIR . DIRECTORY_SEPARATOR . $file;
    }


    /**
     * @param string $file The file we should load
     * @return string The file contents
     */
    public static function loadPlainCertificateFile(string $file): string
    {
        return file_get_contents(self::buildPath($file));
    }


    /**
     * @param string $file The file to use
     * @param string $passphrase The passphrase to use
     * @return \SimpleSAML\XMLSecurity\Key\PublicKey
     */
    public static function getPublicKey(string $file, string $passphrase = self::PASSPHRASE): PublicKey
    {
        $path = self::buildPath($file);
        return PublicKey::fromFile($path, $passphrase);
    }


    /**
     * @param string $file The file to use
     * @param string $passphrase The passphrase to use
     * @return \SimpleSAML\XMLSecurity\Key\PrivateKey
     */
    public static function getPrivateKey(string $file, string $passphrase = self::PASSPHRASE): PrivateKey
    {
        $path = self::buildPath($file);
        return PrivateKey::fromFile($path, $passphrase);
    }


    /**
     * @param string $file The file to use
     * @return string
     */
    public static function getPlainPublicKey(
        string $file = self::PUBLIC_KEY
    ): string {
        return self::loadPlainCertificateFile($file);
    }


    /**
     * @param string $file The file to use
     * @return string
     */
    public static function getPlainPrivateKey(
        string $file = self::PRIVATE_KEY
    ): string {
        return self::loadPlainCertificateFile($file);
    }


    /**
     * @param string $file The file to use
     * @return string
     */
    public static function getPlainPublicKeyContents(
        string $file = self::PUBLIC_KEY
    ): string {
        return CertificateUtils::stripHeaders(
            self::loadPlainCertificateFile($file),
            CertificateUtils::PUBLIC_KEY_PATTERN
        );
    }


    /**
     * @param string $file The file to use
     * @return string
     */
    public static function getPlainPrivateKeyContents(
        string $file = self::PRIVATE_KEY
    ): string {
        return CertificateUtils::stripHeaders(
            self::loadPlainCertificateFile($file),
            CertificateUtils::PRIVATE_KEY_PATTERN
        );
    }
}
