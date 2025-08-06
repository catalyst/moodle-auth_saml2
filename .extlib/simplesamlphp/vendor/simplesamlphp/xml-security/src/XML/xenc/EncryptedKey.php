<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\xenc;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;
use SimpleSAML\XMLSecurity\Key\AbstractKey;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

/**
 * Class representing an encrypted key.
 *
 * @package simplesamlphp/xml-security
 */
class EncryptedKey extends AbstractEncryptedType
{
    /** @var \SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName|null */
    protected ?CarriedKeyName $carriedKeyName;

    /** @var string|null */
    protected ?string $recipient;

    /** @var \SimpleSAML\XMLSecurity\XML\xenc\ReferenceList|null */
    protected ?ReferenceList $referenceList;


    /**
     * EncryptedKey constructor.
     *
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CipherData $cipherData The CipherData object of this EncryptedData.
     * @param string|null $id The Id attribute of this object. Optional.
     * @param string|null $type The Type attribute of this object. Optional.
     * @param string|null $mimeType The MimeType attribute of this object. Optional.
     * @param string|null $encoding The Encoding attribute of this object. Optional.
     * @param string|null $recipient The Recipient attribute of this object. Optional.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName|null $carriedKeyName
     *   The value of the CarriedKeyName element of this EncryptedData.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod|null $encryptionMethod
     *   The EncryptionMethod object of this EncryptedData. Optional.
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo|null $keyInfo The KeyInfo object of this EncryptedData. Optional.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\ReferenceList|null $referenceList
     *   The ReferenceList object of this EncryptedData. Optional.
     */
    public function __construct(
        CipherData $cipherData,
        ?string $id = null,
        ?string $type = null,
        ?string $mimeType = null,
        ?string $encoding = null,
        ?string $recipient = null,
        ?CarriedKeyName $carriedKeyName = null,
        ?EncryptionMethod $encryptionMethod = null,
        ?KeyInfo $keyInfo = null,
        ?ReferenceList $referenceList = null
    ) {
        parent::__construct($cipherData, $id, $type, $mimeType, $encoding, $encryptionMethod, $keyInfo);
        $this->setRecipient($recipient);
        $this->setReferenceList($referenceList);
        $this->setCarriedKeyName($carriedKeyName);
    }


    /**
     * Get the value of the CarriedKeyName property.
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName|null
     */
    public function getCarriedKeyName(): ?CarriedKeyName
    {
        return $this->carriedKeyName;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName|null $carriedKeyName
     */
    protected function setCarriedKeyName(?CarriedKeyName $carriedKeyName): void
    {
        $this->carriedKeyName = $carriedKeyName;
    }


    /**
     * Get the value of the Recipient attribute.
     *
     * @return string|null
     */
    public function getRecipient(): ?string
    {
        return $this->recipient;
    }


    /**
     * @param string|null $recipient
     */
    protected function setRecipient(?string $recipient): void
    {
        $this->recipient = $recipient;
    }


    /**
     * Get the ReferenceList object.
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\ReferenceList|null
     */
    public function getReferenceList(): ?ReferenceList
    {
        return $this->referenceList;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\xenc\ReferenceList|null $referenceList
     */
    protected function setReferenceList(?ReferenceList $referenceList): void
    {
        $this->referenceList = $referenceList;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface $decryptor The decryptor to use
     * to decrypt the key.
     *
     * @return string The decrypted key.
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): string
    {
        Assert::notNull(
            $this->getCipherData()->getCipherValue(),
            'Decrypting keys by reference is not supported.',
            InvalidArgumentException::class,
        );

        Assert::eq(
            $decryptor->getAlgorithmId(),
            $this->getEncryptionMethod()->getAlgorithm(),
            'Decryptor algorithm does not match algorithm used.',
            InvalidArgumentException::class,
        );

        return $decryptor->decrypt(base64_decode($this->getCipherData()->getCipherValue()->getContent()));
    }


    /**
     * Create an EncryptedKey by encrypting a given key.
     *
     * @param \SimpleSAML\XMLSecurity\Key\AbstractKey $keyToEncrypt The key to encrypt.
     * @param \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface $encryptor The encryptor to use.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod $encryptionMethod
     *   The EncryptionMethod object of this EncryptedData. Optional.
     * @param string|null $id The Id attribute of this object. Optional.
     * @param string|null $type The Type attribute of this object. Optional.
     * @param string|null $mimeType The MimeType attribute of this object. Optional.
     * @param string|null $encoding The Encoding attribute of this object. Optional.
     * @param string|null $recipient The Recipient attribute of this object. Optional.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName|null $carriedKeyName
     *   The value of the CarriedKeyName element of this EncryptedData.
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo|null $keyInfo The KeyInfo object of this EncryptedData. Optional.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\ReferenceList|null $referenceList
     *   The ReferenceList object of this EncryptedData. Optional.
     *
     * @return EncryptedKey The new EncryptedKey object.
     */
    public static function fromKey(
        AbstractKey $keyToEncrypt,
        EncryptionAlgorithmInterface $encryptor,
        EncryptionMethod $encryptionMethod,
        ?string $id = null,
        ?string $type = null,
        ?string $mimeType = null,
        ?string $encoding = null,
        ?string $recipient = null,
        ?CarriedKeyName $carriedKeyName = null,
        ?KeyInfo $keyInfo = null,
        ?ReferenceList $referenceList = null
    ): EncryptedKey {
        Assert::eq(
            $encryptor->getAlgorithmId(),
            $encryptionMethod->getAlgorithm(),
            'Encryptor algorithm and encryption method do not match.',
            InvalidArgumentException::class,
        );

        return new self(
            new CipherData(
                new CipherValue(
                    base64_encode(
                        $encryptor->encrypt($keyToEncrypt->get()),
                    ),
                ),
            ),
            $id,
            $type,
            $mimeType,
            $encoding,
            $recipient,
            $carriedKeyName,
            $encryptionMethod,
            $keyInfo,
            $referenceList,
        );
    }


    /**
     * @inheritDoc
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'EncryptedKey', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EncryptedKey::NS, InvalidDOMElementException::class);

        $cipherData = CipherData::getChildrenOfClass($xml);
        Assert::count(
            $cipherData,
            1,
            'No or more than one CipherData element found in <xenc:EncryptedKey>.',
            TooManyElementsException::class,
        );

        $encryptionMethod = EncryptionMethod::getChildrenOfClass($xml);
        Assert::maxCount(
            $encryptionMethod,
            1,
            'No more than one EncryptionMethod element allowed in <xenc:EncryptedKey>.',
            TooManyElementsException::class,
        );

        $keyInfo = KeyInfo::getChildrenOfClass($xml);
        Assert::maxCount(
            $keyInfo,
            1,
            'No more than one KeyInfo element allowed in <xenc:EncryptedKey>.',
            TooManyElementsException::class,
        );

        $referenceLists = ReferenceList::getChildrenOfClass($xml);
        Assert::maxCount(
            $keyInfo,
            1,
            'Only one ReferenceList element allowed in <xenc:EncryptedKey>.',
            TooManyElementsException::class,
        );

        $carriedKeyNames = CarriedKeyName::getChildrenOfClass($xml);
        Assert::maxCount(
            $carriedKeyNames,
            1,
            'Only one CarriedKeyName element allowed in <xenc:EncryptedKey>.',
            TooManyElementsException::class,
        );

        return new self(
            $cipherData[0],
            self::getAttribute($xml, 'Id', null),
            self::getAttribute($xml, 'Type', null),
            self::getAttribute($xml, 'MimeType', null),
            self::getAttribute($xml, 'Encoding', null),
            self::getAttribute($xml, 'Recipient', null),
            array_pop($carriedKeyNames),
            array_pop($encryptionMethod),
            array_pop($keyInfo),
            array_pop($referenceLists),
        );
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = parent::toXML($parent);

        if ($this->referenceList !== null) {
            $this->referenceList->toXML($e);
        }

        if ($this->carriedKeyName !== null) {
            $this->carriedKeyName->toXML($e);
        }

        if ($this->recipient !== null) {
            $e->setAttribute('Recipient', $this->recipient);
        }

        return $e;
    }
}
