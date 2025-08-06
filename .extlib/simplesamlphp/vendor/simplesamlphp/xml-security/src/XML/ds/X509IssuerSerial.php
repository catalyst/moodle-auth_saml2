<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;

/**
 * Class representing a ds:X509IssuerSerial element.
 *
 * @package simplesaml/xml-security
 */
final class X509IssuerSerial extends AbstractDsElement
{
    /**
     * The Issuer's name.
     *
     * @var \SimpleSAML\XMLSecurity\XML\ds\X509IssuerName
     */
    protected X509IssuerName $X509IssuerName;

    /**
     * The serial number.
     *
     * @var \SimpleSAML\XMLSecurity\XML\ds\X509SerialNumber
     */
    protected X509SerialNumber $X509SerialNumber;


    /**
     * Initialize a X509SubjectName element.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\X509IssuerName $name
     * @param \SimpleSAML\XMLSecurity\XML\ds\X509SerialNumber $serial
     */
    public function __construct(X509IssuerName $name, X509SerialNumber $serial)
    {
        $this->setIssuerName($name);
        $this->setSerialNumber($serial);
    }


    /**
     * Collect the value of the X509IssuerName-property
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\X509IssuerName
     */
    public function getIssuerName(): X509IssuerName
    {
        return $this->X509IssuerName;
    }


    /**
     * Set the value of the X509IssuerName-property
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\X509IssuerName $name
     */
    private function setIssuerName(X509IssuerName $name): void
    {
        $this->X509IssuerName = $name;
    }


    /**
     * Collect the value of the X509SerialNumber-property
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\X509SerialNumber
     */
    public function getSerialNumber(): X509SerialNumber
    {
        return $this->X509SerialNumber;
    }


    /**
     * Set the value of the X509SerialNumber-property
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\X509SerialNumber $serial
     */
    private function setSerialNumber(X509SerialNumber $serial): void
    {
        $this->X509SerialNumber = $serial;
    }


    /**
     * Convert XML into a X509IssuerSerial
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): self
    {
        Assert::same($xml->localName, 'X509IssuerSerial', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, X509IssuerSerial::NS, InvalidDOMElementException::class);

        $issuer = X509IssuerName::getChildrenOfClass($xml);
        $serial = X509SerialNumber::getChildrenOfClass($xml);

        Assert::minCount($issuer, 1, MissingElementException::class);
        Assert::maxCount($issuer, 1, TooManyElementsException::class);

        Assert::minCount($serial, 1, MissingElementException::class);
        Assert::maxCount($serial, 1, TooManyElementsException::class);

        return new self(
            array_pop($issuer),
            array_pop($serial),
        );
    }


    /**
     * Convert this X509IssuerSerial element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this X509IssuerSerial element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->X509IssuerName->toXML($e);
        $this->X509SerialNumber->toXML($e);

        return $e;
    }
}
