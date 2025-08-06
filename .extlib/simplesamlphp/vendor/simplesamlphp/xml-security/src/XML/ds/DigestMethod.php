<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;

/**
 * Class representing a ds:DigestMethod element.
 *
 * @package simplesamlphp/xml-security
 */
final class DigestMethod extends AbstractDsElement
{
    /**
     * The algorithm.
     *
     * @var string
     */
    protected string $Algorithm;

    /**
     * @var \SimpleSAML\XML\Chunk[]
     */
    protected array $elements;


    /**
     * Initialize a DigestMethod element.
     *
     * @param string $algorithm
     * @param \SimpleSAML\XML\Chunk[] $elements
     */
    public function __construct(string $algorithm, array $elements = [])
    {
        $this->setAlgorithm($algorithm);
        $this->setElements($elements);
    }


    /**
     * Collect the value of the Algorithm-property
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->Algorithm;
    }


    /**
     * Set the value of the Algorithm-property
     *
     * @param string $algorithm
     */
    private function setAlgorithm(string $algorithm): void
    {
        Assert::validURI($algorithm, SchemaViolationException::class);
        Assert::oneOf(
            $algorithm,
            array_keys(C::$DIGEST_ALGORITHMS),
            'Invalid digest method',
            InvalidArgumentException::class,
        );

        $this->Algorithm = $algorithm;
    }


    /**
     * Collect the embedded elements
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }


    /**
     * Set the value of the elements-property
     *
     * @param \SimpleSAML\XML\Chunk[] $elements
     * @throws \SimpleSAML\Assert\AssertionFailedException
     *   if the supplied array contains anything other than Chunk objects
     */
    private function setElements(array $elements): void
    {
        Assert::allIsInstanceOf($elements, Chunk::class, InvalidArgumentException::class);

        $this->elements = $elements;
    }


    /**
     * Convert XML into a DigestMethod
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'DigestMethod', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, DigestMethod::NS, InvalidDOMElementException::class);

        /** @psalm-var string $Algorithm */
        $Algorithm = DigestMethod::getAttribute($xml, 'Algorithm');

        $elements = [];
        foreach ($xml->childNodes as $elt) {
            if (!($elt instanceof DOMElement)) {
                continue;
            }

            $elements[] = new Chunk($elt);
        }

        return new self($Algorithm, $elements);
    }


    /**
     * Convert this DigestMethod element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this DigestMethod element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Algorithm', $this->Algorithm);

        foreach ($this->elements as $elt) {
            $e->appendChild($e->ownerDocument->importNode($elt->getXML(), true));
        }

        return $e;
    }
}
