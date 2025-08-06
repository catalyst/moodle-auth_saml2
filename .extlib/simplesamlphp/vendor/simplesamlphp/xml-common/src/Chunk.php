<?php

declare(strict_types=1);

namespace SimpleSAML\XML;

use DOMElement;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Utils;
use SimpleSAML\Assert\Assert;

use function in_array;
use function intval;

/**
 * Serializable class used to hold an XML element.
 *
 * @package simplesamlphp/xml-common
 */
final class Chunk extends AbstractSerializableXML
{
    /**
     * The localName of the element.
     *
     * @var string
     */
    protected string $localName;

    /**
     * The namespaceURI of this element.
     *
     * @var string|null
     */
    protected ?string $namespaceURI;

    /**
     * The prefix of this element.
     *
     * @var string|null
     */
    protected ?string $prefix;

    /**
     * The \DOMElement we contain.
     *
     * @var \DOMElement
     */
    protected DOMElement $xml;


    /**
     * Create an XML Chunk from a copy of the given \DOMElement.
     *
     * @param \DOMElement $xml The element we should copy.
     */
    public function __construct(DOMElement $xml)
    {
        $this->setLocalName($xml->localName);
        $this->setNamespaceURI($xml->namespaceURI);
        $this->setPrefix($xml->prefix);

        $this->xml = Utils::copyElement($xml);
    }


    /**
     * Get this \DOMElement.
     *
     * @return \DOMElement This element.
     */
    public function getXML(): DOMElement
    {
        return $this->xml;
    }


    /**
     * Collect the value of the localName-property
     *
     * @return string
     */
    public function getLocalName(): string
    {
        return $this->localName;
    }


    /**
     * Set the value of the localName-property
     *
     * @param string $localName
     * @throws \SimpleSAML\Assert\AssertionFailedException if $localName is an empty string
     */
    public function setLocalName(string $localName): void
    {
        Assert::validNCName($localName, SchemaViolationException::class); // Covers the empty string
        $this->localName = $localName;
    }


    /**
     * Collect the value of the namespaceURI-property
     *
     * @return string|null
     */
    public function getNamespaceURI(): ?string
    {
        return $this->namespaceURI;
    }


    /**
     * Set the value of the namespaceURI-property
     *
     * @param string|null $namespaceURI
     */
    protected function setNamespaceURI(string $namespaceURI = null): void
    {
        Assert::nullOrValidURI($namespaceURI, SchemaViolationException::class);
        $this->namespaceURI = $namespaceURI;
    }


    /**
     * Collect the value of the prefix-property
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }


    /**
     * Set the value of the prefix-property
     *
     * @param string|null $prefix
     */
    protected function setPrefix(string $prefix = null): void
    {
        $this->prefix = $prefix;
    }


    /**
     * Get the XML qualified name (prefix:name, or just name when not prefixed)
     *  of the element represented by this class.
     *
     * @return string
     */
    public function getQualifiedName(): string
    {
        $prefix = $this->getPrefix();

        if (is_null($prefix)) {
            return $this->getLocalName();
        } else {
            return $prefix . ':' . $this->getLocalName();
        }
    }


    /*
     * @param \DOMElement $xml The element where we should search for the attribute.
     * @param string      $name The name of the attribute.
     * @param string|null $default The default to return in case the attribute does not exist and it is optional.
     * @return string|null
     * @throws \SimpleSAML\Assert\AssertionFailedException if the attribute is missing from the element
     */
    public static function getAttribute(DOMElement $xml, string $name, ?string $default = ''): ?string
    {
        if (!$xml->hasAttribute($name)) {
            Assert::nullOrStringNotEmpty(
                $default,
                'Missing \'' . $name . '\' attribute on ' . $xml->prefix . ':' . $xml->localName . '.',
                MissingAttributeException::class,
            );

            return $default;
        }

        return $xml->getAttribute($name);
    }


    /**
     * @param \DOMElement $xml The element where we should search for the attribute.
     * @param string      $name The name of the attribute.
     * @param string|null $default The default to return in case the attribute does not exist and it is optional.
     * @return bool|null
     * @throws \SimpleSAML\Assert\AssertionFailedException if the attribute is not a boolean
     */
    public static function getBooleanAttribute(DOMElement $xml, string $name, ?string $default = ''): ?bool
    {
        $value = self::getAttribute($xml, $name, $default);
        if ($value === null) {
            return null;
        }

        Assert::oneOf(
            $value,
            ['0', '1', 'false', 'true'],
            'The \'' . $name . '\' attribute of ' . $xml->prefix . ':' . $xml->localName . ' must be boolean.',
        );

        return in_array($value, ['1', 'true'], true);
    }


    /**
     * Get the integer value of an attribute from a given element.
     *
     * @param \DOMElement $xml The element where we should search for the attribute.
     * @param string      $name The name of the attribute.
     * @param string|null $default The default to return in case the attribute does not exist and it is optional.
     *
     * @return int|null
     * @throws \SimpleSAML\Assert\AssertionFailedException if the attribute is not an integer
     */
    public static function getIntegerAttribute(DOMElement $xml, string $name, ?string $default = ''): ?int
    {
        $value = self::getAttribute($xml, $name, $default);
        if ($value === null) {
            return null;
        }

        Assert::numeric(
            $value,
            'The \'' . $name . '\' attribute of ' . $xml->prefix . ':' . $xml->localName . ' must be numerical.',
        );

        return intval($value);
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        $xml = $this->getXML();
        return (
            ($xml->childNodes->length === 0)
            && ($xml->attributes->length === 0)
        );
    }


    /**
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        return new self($xml);
    }


    /**
     * Append this XML element to a different XML element.
     *
     * @param  \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement The new element.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        return Utils::copyElement($this->xml, $parent);
    }
}
