<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\Test\XML\XMLElement;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XML\AbstractXMLElementTest
 *
 * @covers \SimpleSAML\XML\AbstractXMLElement
 * @covers \SimpleSAML\XML\AbstractSerializableXML
 *
 * @package simplesamlphp\xml-common
 */
final class AbstractXMLElementTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = XMLElement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/bar_XMLElement.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $element = new XMLElement(2, false, 'text');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($element),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $element = XMLElement::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(2, $element->getInteger());
        $this->assertEquals(false, $element->getBoolean());
        $this->assertEquals('text', $element->getString());
    }


    /**
     */
    public function testGetAttributeThrowsExceptionOnMissingAttribute(): void
    {
        $doc = $this->xmlRepresentation->documentElement;
        $doc->removeAttribute('text');

        $this->expectException(MissingAttributeException::class);
        XMLElement::fromXML($doc);
    }


    /**
     */
    public function testGetBooleanAttributeThrowsExceptionOnMissingAttribute(): void
    {
        $doc = $this->xmlRepresentation->documentElement;
        $doc->removeAttribute('boolean');

        $this->expectException(MissingAttributeException::class);
        XMLElement::fromXML($doc);
    }


    /**
     */
    public function testGetIntegerAttributeThrowsExceptionOnMissingAttribute(): void
    {
        $doc = $this->xmlRepresentation->documentElement;
        $doc->removeAttribute('integer');

        $this->expectException(MissingAttributeException::class);
        XMLElement::fromXML($doc);
    }
}
