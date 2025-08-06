<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ds;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\XPath;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ds\XPathTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ds\XPath
 *
 * @package simplesamlphp/xml-security
 */
class XPathTest extends TestCase
{
    use SerializableXMLTestTrait;

    /**
     */
    public function setUp(): void
    {
        $this->testedClass = XPath::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_XPath.xml',
        );
    }


    public function testMarshalling(): void
    {
        $xpath = new XPath(
            'self::xenc:CipherValue[@Id="example1"]',
            [
                'xenc' => 'http://www.w3.org/2001/04/xmlenc#',
            ],
        );

        $this->assertEquals('self::xenc:CipherValue[@Id="example1"]', $xpath->getExpression());
        $namespaces = $xpath->getNamespaces();
        $this->assertCount(1, $namespaces);
        $this->assertArrayHasKey('xenc', $namespaces);
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#', $namespaces['xenc']);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($xpath),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $xpath = XPath::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('self::xenc:CipherValue[@Id="example1"]', $xpath->getExpression());
        $namespaces = $xpath->getNamespaces();
        $this->assertCount(2, $namespaces);
        $this->assertEquals('xenc', array_keys($namespaces)[0]);
        $this->assertEquals('ds', array_keys($namespaces)[1]);


        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($xpath),
        );
    }
}
