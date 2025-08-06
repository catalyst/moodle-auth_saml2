<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Key;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;
use SimpleSAML\XMLSecurity\Exception\RuntimeException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;

use function array_map;
use function array_pop;
use function array_shift;
use function base64_encode;
use function explode;
use function function_exists;
use function hash;
use function implode;
use function openssl_error_string;
use function openssl_pkey_get_public;
use function openssl_x509_export;
use function openssl_x509_fingerprint;
use function openssl_x509_parse;
use function openssl_x509_read;
use function trim;

/**
 * A class modeling X509 certificates.
 *
 * @package simplesamlphp/xml-security
 */
class X509Certificate extends PublicKey
{
    public const PEM_HEADER = '-----BEGIN CERTIFICATE-----';
    public const PEM_FOOTER = '-----END CERTIFICATE-----';

    /** @var string */
    protected string $certificate;

    /** @var array */
    protected array $thumbprint = [];

    /** @var array */
    protected array $parsed = [];


    /**
     * Create a new X509 certificate from its PEM-encoded representation.
     *
     * @param string|resource $cert The PEM-encoded certificate or the path to a file containing it.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If the certificate cannot be read from $cert.
     * @throws \SimpleSAML\XMLSecurity\Exception\RuntimeException If the certificate cannot be exported to PEM format.
     */
    final public function __construct($cert)
    {
        $resource = openssl_x509_read($cert);
        if ($resource === false) {
            throw new InvalidArgumentException('Cannot read certificate: ' . openssl_error_string());
        }

        $certificate = null;
        if (!openssl_x509_export($resource, $certificate)) {
            throw new RuntimeException('Cannot export certificate to PEM: ' . openssl_error_string());
        }
        $this->certificate = $certificate;

        parent::__construct(openssl_pkey_get_public($this->certificate));
        $this->thumbprint[C::DIGEST_SHA1] = $this->getRawThumbprint();

        $this->parsed = openssl_x509_parse($this->certificate);
    }


    /**
     * Compute a certificate digest manually.
     *
     * @param string $alg The digest algorithm to use.
     *
     * @return string The thumbprint associated with the given certificate.
     */
    protected function manuallyComputeThumbprint(string $alg): string
    {
        // remove beginning and end delimiters
        $lines = explode("\n", trim($this->certificate));
        array_shift($lines);
        array_pop($lines);

        return $this->thumbprint[$alg] = strtolower(
            hash(
                C::$DIGEST_ALGORITHMS[$alg],
                base64_decode(
                    implode(
                        array_map("trim", $lines),
                    ),
                ),
            ),
        );
    }


    /**
     * Get the raw thumbprint of a certificate
     *
     * @param string $alg The digest algorithm to use. Defaults to SHA1.
     *
     * @return string The thumbprint associated with the given certificate.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If $alg is not a valid digest identifier.
     */
    public function getRawThumbprint(string $alg = C::DIGEST_SHA1): string
    {
        if (isset($this->thumbprint[$alg])) {
            return $this->thumbprint[$alg];
        }

        Assert::keyExists(
            C::$DIGEST_ALGORITHMS,
            $alg,
            'Invalid digest algorithm identifier',
            UnsupportedAlgorithmException::class,
        );

        if (function_exists('openssl_x509_fingerprint')) {
            // if available, use the openssl function
            return $this->thumbprint[$alg] = openssl_x509_fingerprint(
                $this->certificate,
                C::$DIGEST_ALGORITHMS[$alg],
            );
        }

        return $this->manuallyComputeThumbprint($alg);
    }


    /**
     * Get the certificate this key originated from.
     *
     * @return string The certificate.
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }


    /**
     * Get the details of this certificate.
     *
     * @return array An array with all the details of the certificate.
     *
     * @see openssl_x509_parse()
     */
    public function getCertificateDetails(): array
    {
        return $this->parsed;
    }


    /**
     * Get a new X509 certificate from a file.
     *
     * @param string $file The file where the PEM-encoded certificate is stored.
     *
     * @return \SimpleSAML\XMLSecurity\Key\X509Certificate A new X509Certificate key.
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException If the file cannot be read.
     */
    public static function fromFile(string $file): X509Certificate
    {
        return new static(static::readFile($file));
    }
}
