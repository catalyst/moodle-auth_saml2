<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\xenc;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\Utils\XPath;

use function array_pop;

/**
 * Class representing <xenc:CipherData>.
 *
 * @package simplesamlphp/xml-security
 */
class CipherData extends AbstractXencElement
{
    /** @var \SimpleSAML\XMLSecurity\XML\xenc\CipherValue|null */
    protected ?CipherValue $cipherValue = null;

    /** @var \SimpleSAML\XMLSecurity\XML\xenc\CipherReference|null */
    protected ?CipherReference $cipherReference = null;


    /**
     * CipherData constructor.
     *
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CipherValue|null $cipherValue
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CipherReference|null $cipherReference
     */
    public function __construct(?CipherValue $cipherValue, ?CipherReference $cipherReference = null)
    {
        Assert::oneOf(
            null,
            [$cipherValue, $cipherReference],
            'Can only have one of CipherValue/CipherReference',
        );

        Assert::false(
            is_null($cipherValue) && is_null($cipherReference),
            'You need either a CipherValue or a CipherReference',
        );

        $this->setCipherValue($cipherValue);
        $this->setCipherReference($cipherReference);
    }


    /**
     * Get the value of the $cipherValue property.
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\CipherValue|null
     */
    public function getCipherValue(): ?CipherValue
    {
        return $this->cipherValue;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CipherValue|null $cipherValue
     */
    protected function setCipherValue(?CipherValue $cipherValue): void
    {
        $this->cipherValue = $cipherValue;
    }


    /**
     * Get the CipherReference element inside this CipherData object.
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\CipherReference|null
     */
    public function getCipherReference(): ?CipherReference
    {
        return $this->cipherReference;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\XML\xenc\CipherReference|null $cipherReference
     */
    protected function setCipherReference(?CipherReference $cipherReference): void
    {
        $this->cipherReference = $cipherReference;
    }


    /**
     * @inheritDoc
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'CipherData', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, CipherData::NS, InvalidDOMElementException::class);

        $cv = CipherValue::getChildrenOfClass($xml);
        Assert::maxCount(
            $cv,
            1,
            'More than one CipherValue element in <xenc:CipherData',
            TooManyElementsException::class
        );

        $cr = CipherReference::getChildrenOfClass($xml);
        Assert::maxCount(
            $cr,
            1,
            'More than one CipherReference element in <xenc:CipherData',
            TooManyElementsException::class
        );

        return new self(
            empty($cv) ? null : array_pop($cv),
            empty($cr) ? null : array_pop($cr),
        );
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);

        if ($this->cipherValue !== null) {
            $this->cipherValue->toXML($e);
        }

        if ($this->cipherReference !== null) {
            $this->cipherReference->toXML($e);
        }

        return $e;
    }
}
