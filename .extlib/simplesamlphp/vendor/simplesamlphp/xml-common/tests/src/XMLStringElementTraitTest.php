<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\Test\XML\XMLStringElement;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\XMLStringElementTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XML\XMLStringElementTraitTest
 *
 * @covers \SimpleSAML\XML\XMLStringElementTrait
 * @covers \SimpleSAML\XML\AbstractXMLElement
 * @covers \SimpleSAML\XML\AbstractSerializableXML
 *
 * @package simplesamlphp\xml-common
 */
final class XMLStringElementTraitTest extends TestCase
{
    use SerializableXMLTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->testedClass = XMLStringElement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/bar_XMLStringElement.xml',
        );
    }

    /**
     */
    public function testMarshalling(): void
    {
        $stringElement = new XMLStringElement('test');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($stringElement),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $stringElement = XMLStringElement::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('test', $stringElement->getContent());
    }
}
