<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ec;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ec\InclusiveNamespaces;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ec\InclusiveNamespacesTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ec\InclusiveNamespaces
 * @covers \SimpleSAML\XMLSecurity\XML\ec\AbstractEcElement
 *
 * @package simplesamlphp/xml-security
 */
class InclusiveNamespacesTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/exc-c14n.xsd';

        $this->testedClass = InclusiveNamespaces::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(__FILE__))) . '/resources/xml/ec_InclusiveNamespaces.xml',
        );
    }


    public function testMarshalling(): void
    {
        $inclusiveNamespaces = new InclusiveNamespaces(["dsig", "soap"]);

        $this->assertCount(2, $inclusiveNamespaces->getPrefixes());
        $this->assertEquals("dsig", $inclusiveNamespaces->getPrefixes()[0]);
        $this->assertEquals("soap", $inclusiveNamespaces->getPrefixes()[1]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($inclusiveNamespaces),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $inclusiveNamespaces = InclusiveNamespaces::fromXML(
            $this->xmlRepresentation->documentElement,
        );
        $prefixes = $inclusiveNamespaces->getPrefixes();
        $this->assertCount(2, $prefixes);

        $this->assertEquals('dsig', $prefixes[0]);
        $this->assertEquals('soap', $prefixes[1]);


        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($inclusiveNamespaces),
        );
    }
}
