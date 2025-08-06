<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg\Encryption;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\AbstractKey;

/**
 * An abstract class that implements a generic encryption algorithm
 *
 * @package simplesamlphp/xml-security
 */
abstract class AbstractEncryptor implements EncryptionAlgorithmInterface
{
    /** @var \SimpleSAML\XMLSecurity\Key\AbstractKey */
    private AbstractKey $key;

    /** @var \SimpleSAML\XMLSecurity\Backend\EncryptionBackend */
    protected EncryptionBackend $backend;

    /** @var string */
    protected string $default_backend;

    /** @var string */
    protected string $algId;


    /**
     * Build an encryption algorithm.
     *
     * Extend this class to implement your own encryptors.
     *
     * WARNING: remember to adjust the type of the key to the one that works with your algorithm!
     *
     * @param \SimpleSAML\XMLSecurity\Key\AbstractKey $key The signing key.
     * @param string $algId The identifier of this algorithm.
     */
    public function __construct(AbstractKey $key, string $algId)
    {
        Assert::oneOf(
            $algId,
            static::getSupportedAlgorithms(),
            'Unsupported algorithm for ' . static::class,
            UnsupportedAlgorithmException::class,
        );
        $this->key = $key;
        $this->algId = $algId;
        $this->setBackend(new $this->default_backend());
    }


    /**
     * @return string
     */
    public function getAlgorithmId(): string
    {
        return $this->algId;
    }


    /**
     * @return \SimpleSAML\XMLSecurity\Key\AbstractKey
     */
    public function getKey(): AbstractKey
    {
        return $this->key;
    }


    /**
     * @inheritDoc
     */
    public function setBackend(?EncryptionBackend $backend): void
    {
        if ($backend === null) {
            return;
        }

        $this->backend = $backend;
        $this->backend->setCipher($this->algId);
    }


    /**
     * Encrypt a given plaintext with the current algorithm and key.
     *
     * @param string $plaintext The plaintext to encrypt.
     *
     * @return string The (binary) ciphertext.
     */
    public function encrypt(string $plaintext): string
    {
        return $this->backend->encrypt($this->key, $plaintext);
    }


    /**
     * Decrypt a given ciphertext with the current algorithm and key.
     *
     * @param string The (binary) ciphertext to decrypt.
     *
     * @return string The decrypted plaintext.
     */
    public function decrypt(string $ciphertext): string
    {
        return $this->backend->decrypt($this->key, $ciphertext);
    }
}
