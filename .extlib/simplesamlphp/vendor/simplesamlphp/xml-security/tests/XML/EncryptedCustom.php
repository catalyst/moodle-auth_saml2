<?php

namespace SimpleSAML\XMLSecurity\Test\XML;

use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Backend\OpenSSL;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\RuntimeException;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptedElementTrait;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;

/**
 * This is an example class demonstrating how an encrypted object can be implemented with this library.
 *
 * Please have a look at \SimpleSAML\XMLSecurity\XML\EncryptedElementInterface, and read carefully the comments in this
 * file. The implementation in \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait may also serve as a reference.
 *
 * @package simplesamlphp/xml-security
 */
final class EncryptedCustom extends AbstractXMLElement implements EncryptedElementInterface
{
    /*
     * By using this trait, we get a constructor out of the box that processes the EncryptedData and any possible
     * EncryptedKey elements inside. If you need your own constructor, make sure to rename the one from the trait
     * here so that you can call it later.
     */
    use EncryptedElementTrait {
        __construct as constructor;
    }

    /** @var string */
    public const NS = 'urn:ssp:custom';

    /** @var string */
    public const NS_PREFIX = 'ssp';

    /** @var EncryptionBackend */
    private EncryptionBackend $backend;

    /** @var string[] */
    private array $blacklistedAlgs = [];


    /**
     * Construct an encrypted object.
     *
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptedData $encryptedData
     */
    public function __construct(EncryptedData $encryptedData)
    {
        $this->constructor($encryptedData);
        $this->backend = new OpenSSL();
    }


    /**
     * Implement a method like this if your encrypted object needs to instantiate a new decryptor, for example, to
     * decrypt a session key. This method is required by \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait.
     *
     * @return \SimpleSAML\XMLSecurity\Backend\EncryptionBackend|null The encryption backend to use, or null if we want
     * to use the default.
     */
    public function getEncryptionBackend(): ?EncryptionBackend
    {
        return $this->backend;
    }


    /**
     * Implement a method like this if your encrypted object needs to instantiate a new decryptor, for example, to
     * decrypt a session key. This method is required by \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait.
     *
     * @param \SimpleSAML\XMLSecurity\Backend\EncryptionBackend|null $backend The encryption backend we want to use, or
     * null if we want to use the defaults.
     */
    public function setEncryptionBackend(?EncryptionBackend $backend): void
    {
        $this->backend = $backend;
    }


    /**
     * Implement a method like this if your encrypted object needs to instantiate a new decryptor, for example, to
     * decrypt a session key. This method is required by \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait.
     *
     * @return string[]|null An array with all algorithm identifiers that we want to blacklist, or null if we want to
     * use the defaults.
     */
    public function getBlacklistedAlgorithms(): ?array
    {
        return $this->blacklistedAlgs;
    }


    /**
     * Implement a method like this if your encrypted object needs to instantiate a new decryptor, for example, to
     * decrypt a session key. This method is required by \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait.
     *
     * @param string[]|null $algIds An array with the identifiers of the algorithms we want to blacklist, or null if we
     * want to use the defaults.
     */
    public function setBlacklistedAlgorithms(?array $algIds): void
    {
        $this->blacklistedAlgs = $algIds;
    }


    /**
     * Decrypt this encrypted element.
     *
     * This method needs to be implemented by any object implementing EncryptedElementInterface. Depending on the
     * encryption mechanism used by your XML elements, this might be as simple as instantiating a decryptor with
     * the algorithm specified in the EncryptionMethod and giving it the right key, or you might need to first obtain
     * a decryption key by decrypting it with a KeyTransport algorithm or by resolving a reference.
     *
     * The \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait trait implements this method, supporting objects encrypted
     * with and without a session key. Depending on the decryptor passed as an argument, if it implements a key
     * transport algorithm and the EncryptedData has a KeyInfo object with an EncryptedKey inside, then that key will
     * be decrypted with the given decryptor, and later used to build a decryptor that can decrypt the object itself.
     * If, on the contrary, the decryptor implements a block cipher encryption algorithm, the method in the trait will
     * attempt to decrypt the object directly.
     *
     * @param \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface $decryptor The decryptor able to
     * decrypt this object.
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): CustomSignable
    {
        return CustomSignable::fromXML(
            DOMDocumentFactory::fromString(
                $this->decryptData($decryptor),
            )->documentElement,
        );
    }


    /**
     * Custom implementation of the decrypt() method.
     *
     * Here we implement this method manually to serve as a guide for those needing to implement it on their own. This
     * method implements an example where the EncryptedData includes a KeyInfo with an EncryptedKey. We then use the
     * given decryptor to decrypt that key, which will in turn be used to decrypt the element itself. Note that if you
     * plan to support encrypted objects that include their own EncryptedKey, your object will have to build a
     * decryptor on its own. This means the end user will have no way to specify the backend to use or what algorithms
     * should be blacklisted, so your encrypted object implementation should cater for this.
     *
     * @param \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface $decryptor The decryptor able to
     * decrypt this object. In this particular example, this decryptor will be used to decrypt the session key inside
     * the encrypted object, and therefore must implement a key transport algorithm.
     * @return CustomSignable A CustomSignable object created from the decrypted element.
     */
    public function decryptWithSessionKey(EncryptionAlgorithmInterface $decryptor): CustomSignable
    {
        if (!$this->hasDecryptionKey()) {
            throw new RuntimeException('EncryptedCustom without encryption key.');
        }

        if ($this->getEncryptedData() === null) {
            throw new RuntimeException('No encrypted data.');
        }

        /*
         * Get the encryption algorithm, and check if we know it. In this case, we assume it must be a block cipher,
         * since this object can only be encrypted with them (which is the common scenario). Always remember to check
         * the supported algorithms.
         */
        $algId = $this->getEncryptedData()->getEncryptionMethod()->getAlgorithm();
        if (!isset(C::$BLOCK_CIPHER_ALGORITHMS[$algId])) {
            throw new RuntimeException('Unknown or unsupported encryption algorithm.');
        }

        // decrypt the encryption key with the decryptor we were provided
        $encryptedKey = $this->getEncryptedKey();
        $decryptionKey = $encryptedKey->decrypt($decryptor);

        /*
         * Instantiate a new decryptor with the blacklisted algorithms and encryption backend given. This decryptor
         * will be the one implementing the block cipher used to encrypt the object itself.
         */
        $factory = new EncryptionAlgorithmFactory($this->getBlacklistedAlgorithms());
        $alg = $factory->getAlgorithm($algId, new SymmetricKey($decryptionKey));
        $alg->setBackend($this->getEncryptionBackend());

        // finally, decrypt the element, create an XML document from it and then use that to create an object
        $xml = DOMDocumentFactory::fromString(
            $alg->decrypt($this->getEncryptedData()->getCipherData()->getCipherValue()->getContent()),
        );
        return CustomSignable::fromXML($xml->documentElement);
    }
}
