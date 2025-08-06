<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;

/**
 * Class representing a ds:X509Data element.
 *
 * @package simplesamlphp/xml-security
 */
final class X509Data extends AbstractDsElement
{
    /**
     * The various X509 data elements.
     *
     * Array with various elements describing this certificate.
     * Unknown elements will be represented by \SimpleSAML\XML\Chunk.
     *
     * @var (\SimpleSAML\XML\Chunk|
     *       \SimpleSAML\XMLSecurity\XML\ds\X509Certificate|
     *       \SimpleSAML\XMLSecurity\XML\ds\X509IssuerSerial|
     *       \SimpleSAML\XMLSecurity\XML\ds\X509SubjectName)[]
     */
    protected array $data = [];


    /**
     * Initialize a X509Data.
     *
     * @param (\SimpleSAML\XML\Chunk|
     *         \SimpleSAML\XMLSecurity\XML\ds\X509Certificate|
     *         \SimpleSAML\XMLSecurity\XML\ds\X509IssuerSerial|
     *         \SimpleSAML\XMLSecurity\XML\ds\X509SubjectName)[] $data
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }


    /**
     * Collect the value of the data-property
     *
     * @return (\SimpleSAML\XML\Chunk|
     *          \SimpleSAML\XMLSecurity\XML\ds\X509Certificate|
     *          \SimpleSAML\XMLSecurity\XML\ds\X509Digest|
     *          \SimpleSAML\XMLSecurity\XML\ds\X509SubjectName)[]
     */
    public function getData(): array
    {
        return $this->data;
    }


    /**
     * Set the value of the data-property
     *
     * @param (\SimpleSAML\XML\Chunk|
     *         \SimpleSAML\XMLSecurity\XML\ds\X509Certificate|
     *         \SimpleSAML\XMLSecurity\XML\ds\X509IssuerSerial|
     *         \SimpleSAML\XMLSecurity\XML\ds\X509SubjectName)[] $data
     * @throws \SimpleSAML\Assert\AssertionFailedException
     *     if $data contains anything other than X509Certificate or Chunk
     */
    private function setData(array $data): void
    {
        Assert::allIsInstanceOfAny(
            $data,
            [Chunk::class, X509Certificate::class, X509IssuerSerial::class, X509SubjectName::class],
            InvalidArgumentException::class,
        );

        $this->data = $data;
    }


    /**
     * Convert XML into a X509Data
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'X509Data', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, X509Data::NS, InvalidDOMElementException::class);

        $data = [];

        for ($n = $xml->firstChild; $n !== null; $n = $n->nextSibling) {
            if (!($n instanceof DOMElement)) {
                continue;
            } elseif ($n->namespaceURI !== self::NS) {
                $data[] = new Chunk($n);
                continue;
            }

            switch ($n->localName) {
                case 'X509Certificate':
                    $data[] = X509Certificate::fromXML($n);
                    break;
                case 'X509IssuerSerial':
                    $data[] = X509IssuerSerial::fromXML($n);
                    break;
                case 'X509SubjectName':
                    $data[] = X509SubjectName::fromXML($n);
                    break;
                default:
                    $data[] = new Chunk($n);
                    break;
            }
        }

        return new self($data);
    }


    /**
     * Convert this X509Data element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this X509Data element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getData() as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
