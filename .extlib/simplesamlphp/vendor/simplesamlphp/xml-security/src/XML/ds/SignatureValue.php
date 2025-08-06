<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\XMLBase64ElementTrait;

/**
 * Class representing a ds:SignatureValue element.
 *
 * @package simplesaml/xml-security
 */
final class SignatureValue extends AbstractDsElement
{
    use XMLBase64ElementTrait;

    /** @var string|null */
    protected ?string $Id;


    /**
     * @param string $content
     * @param string|null $id
     */
    public function __construct(string $content, ?string $id = null)
    {
        $this->setContent($content);
        $this->setId($id);
    }


    /**
     * Get the Id used for this signature value.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->Id;
    }


    /**
     * Set the Id used for this signature value.
     *
     * @param string|null $Id
     */
    protected function setId(?string $Id): void
    {
        Assert::nullOrValidNCName($Id);
        $this->Id = $Id;
    }


    /**
     * Convert XML into a SignatureValue element
     *
     * @param \DOMElement $xml
     * @return \SimpleSAML\XML\AbstractXMLElement
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'SignatureValue', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SignatureValue::NS, InvalidDOMElementException::class);

        $Id = self::getAttribute($xml, 'Id', null);

        return new self($xml->textContent, $Id);
    }


    /**
     * Convert this SignatureValue element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this SignatureValue element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->getContent();

        if ($this->Id !== null) {
            $e->setAttribute('Id', $this->Id);
        }

        return $e;
    }
}
