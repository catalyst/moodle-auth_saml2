<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;

/**
 * Abstract class to be implemented by all signed classes
 *
 * @psalm-consistent-constructor
 * @package simplesamlphp/xml-security
 */
abstract class AbstractSignedXMLElement extends AbstractXMLElement implements SignedElementInterface
{
    use SignedElementTrait;

    /**
     * The signed DOM structure.
     *
     * @var \DOMElement
     */
    protected DOMElement $structure;

    /**
     * The unsigned elelement.
     *
     * @var \SimpleSAML\XMLSecurity\XML\SignableElementInterface
     */
    protected SignableElementInterface $element;


    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param \DOMElement $xml
     * @param \SimpleSAML\XMLSecurity\XML\ds\Signature $signature
     */
    public function __construct(DOMElement $xml, Signature $signature)
    {
        $this->setStructure($xml);
        $this->setSignature($signature);
    }


    /**
     * Output the class as an XML-formatted string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->structure->ownerDocument->saveXML();
    }


    /**
     * Set the value of the structure-property
     *
     * @param \DOMElement $structure
     */
    private function setStructure(DOMElement $structure): void
    {
        $this->structure = $structure;
    }


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        return $this->structure;
    }


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): self
    {
        /** @var \DOMDocument $original */
        $original = $xml->ownerDocument->cloneNode(true);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::minCount($signature, 1, MissingElementException::class);
        Assert::maxCount($signature, 1, TooManyElementsException::class);

        return new static($original->documentElement, array_pop($signature));
    }
}
