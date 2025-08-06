<?php

declare(strict_types=1);

namespace SimpleSAML\XML;

use DOMElement;
use SimpleSAML\XML\DOMDocumentFactory;

use function get_object_vars;

/**
 * Abstract class for serialization of XML structures
 *
 * @package simplesamlphp/xml-common
 */
abstract class AbstractSerializableXML implements XMLElementInterface
{
    /**
     * Whether to format the string output of this element or not.
     *
     * Defaults to true. Override to disable output formatting.
     *
     * @var bool
     */
    protected bool $formatOutput = true;


    /**
     * Output the class as an XML-formatted string
     *
     * @return string
     */
    public function __toString(): string
    {
        $xml = $this->toXML();
        /** @psalm-var \DOMDocument $xml->ownerDocument */
        $xml->ownerDocument->formatOutput = $this->formatOutput;
        return $xml->ownerDocument->saveXML($xml);
    }


    /**
     * Serialize this XML chunk.
     *
     * This method will be invoked by any calls to serialize().
     *
     * @return array The serialized representation of this XML object.
     */
    public function __serialize(): array
    {
        $xml = $this->toXML();
        /** @psalm-var \DOMDocument $xml->ownerDocument */
        return [$xml->ownerDocument->saveXML($xml)];
    }


    /**
     * Unserialize an XML object and load it..
     *
     * This method will be invoked by any calls to unserialize(), allowing us to restore any data that might not
     * be serializable in its original form (e.g.: DOM objects).
     *
     * @param array $serialized The XML object that we want to restore.
     */
    public function __unserialize(array $serialized): void
    {
        $xml = static::fromXML(
            DOMDocumentFactory::fromString(array_pop($serialized))->documentElement,
        );

        $vars = get_object_vars($xml);
        foreach ($vars as $k => $v) {
            $this->$k = $v;
        }
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    abstract public function isEmptyElement(): bool;


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return self
     */
    abstract public static function fromXML(DOMElement $xml): object;


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    abstract public function toXML(DOMElement $parent = null): DOMElement;
}
