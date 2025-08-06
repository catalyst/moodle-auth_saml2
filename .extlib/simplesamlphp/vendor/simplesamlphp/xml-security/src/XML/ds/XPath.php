<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;
use SimpleSAML\XMLSecurity\Utils\XPath as XPathUtils;

use function str_replace;

/**
 * Class implementing the XPath element.
 *
 * @package simplesamlphp/xml-security
 */
class XPath extends AbstractDsElement
{
    /**
     * The XPath expression.
     *
     * @var string
     */
    protected string $expression;

    /**
     * A key-value array with namespaces, indexed by the prefixes used in the XPath expression.
     *
     * @var string[]
     */
    protected array $namespaces = [];


    /**
     * Construct an XPath element.
     *
     * @param string $expression The XPath expression itself.
     * @param string[] $namespaces A key - value array with namespace definitions.
     */
    public function __construct(string $expression, array $namespaces = [])
    {
        $this->setExpression($expression);
        $this->setNamespaces($namespaces);
    }


    /**
     * Get the actual XPath expression.
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }


    /**
     * Set the xpath expression itself.
     *
     * @param string $expression
     */
    private function setExpression(string $expression): void
    {
        $this->expression = $expression;
    }


    /**
     * Get the list of namespaces used in this XPath expression, with their corresponding prefix as
     * the keys of each element in the array.
     *
     * @return string[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }


    /**
     * Set the list of namespaces used in this XPath expression.
     *
     * @param string[] $namespaces
     */
    private function setNamespaces(array $namespaces): void
    {
        Assert::allString($namespaces, InvalidArgumentException::class);
        Assert::allString(array_keys($namespaces, InvalidArgumentException::class));

        $this->namespaces = $namespaces;
    }


    /**
     * Convert XML into a class instance
     *
     * @param DOMElement $xml
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'XPath', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, self::NS, InvalidDOMElementException::class);

        $namespaces = [];
        $xpath = XPathUtils::getXPath($xml->ownerDocument);
        foreach (XPathUtils::xpQuery($xml, './namespace::*', $xpath) as $ns) {
            if ($xml->getAttributeNode($ns->nodeName)) {
                // only add namespaces when they are defined explicitly in an attribute
                $namespaces[$ns->localName] = $xml->getAttribute($ns->nodeName);
            }
        }

        return new self($xml->textContent, $namespaces);
    }


    /**
     * @param DOMElement|null $parent
     * @return DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->expression;

        foreach ($this->namespaces as $prefix => $namespace) {
            $e->setAttribute('xmlns:' . $prefix, $namespace);
        }
        return $e;
    }
}
