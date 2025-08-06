<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Alg\KeyTransport;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\AbstractKey;

/**
 * An abstract class that implements a generic key transport algorithm.
 *
 * @package simplesamlphp/xml-security
 */
abstract class AbstractKeyTransporter implements EncryptionAlgorithmInterface
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
     * Build a key transport algorithm.
     *
     * Extend this class to implement your own key transporters.
     *
     * WARNING: remember to adjust the type of the key to the one that works with your algorithm!
     *
     * @param \SimpleSAML\XMLSecurity\Key\AbstractKey $key The encryption key.
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
     * @return AbstractKey
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
     * Encrypt a given key with this cipher and the loaded key.
     *
     * @param string $plaintext The original key to encrypt.
     *
     * @return string The encrypted key (ciphertext).
     */
    public function encrypt(string $plaintext): string
    {
        return $this->backend->encrypt($this->key, $plaintext);
    }


    /**
     * Decrypt a given key with this cipher and the loaded key.
     *
     * @note The class of the returned key will depend on the algorithm it is going to be used for.
     *
     * @param string $ciphertext The encrypted key.
     *
     * @return string The decrypted key.
     */
    public function decrypt(string $ciphertext): string
    {
        return $this->backend->decrypt($this->key, $ciphertext);
    }
}
