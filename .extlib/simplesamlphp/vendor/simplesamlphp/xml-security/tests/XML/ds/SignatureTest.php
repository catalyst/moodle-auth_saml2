<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ds;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Utils\XPath;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XML\ds\SignatureValue;
use SimpleSAML\XMLSecurity\XML\ds\SignedInfo;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ds\SignatureTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ds\AbstractDsElement
 * @covers \SimpleSAML\XMLSecurity\XML\ds\Signature
 *
 * @package simplesamlphp/xml-security
 */
final class SignatureTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->testedClass = Signature::class;

        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/xmldsig1-schema.xsd';

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/tests/resources/xml/ds_Signature.xml',
        );
    }


    /**
     * Test creating a SignatureValue from scratch.
     */
    public function testMarshalling(): void
    {
        $signature = new Signature(
            SignedInfo::fromXML(
                DOMDocumentFactory::fromFile(
                    dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignedInfo.xml',
                )->documentElement,
            ),
            SignatureValue::fromXML(
                DOMDocumentFactory::fromFile(
                    dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignatureValue.xml',
                )->documentElement,
            ),
            KeyInfo::fromXML(
                DOMDocumentFactory::fromFile(
                    dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_KeyInfo.xml',
                )->documentElement,
            ),
            [
                new Chunk(
                    DOMDocumentFactory::fromString(
                        '<ds:Object xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><some>Chunk</some></ds:Object>',
                    )->documentElement,
                ),
            ],
            'def456',
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($signature),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $signature = new Signature(
            SignedInfo::fromXML(
                DOMDocumentFactory::fromFile(
                    dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignedInfo.xml',
                )->documentElement,
            ),
            SignatureValue::fromXML(
                DOMDocumentFactory::fromFile(
                    dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignatureValue.xml',
                )->documentElement,
            ),
            KeyInfo::fromXML(
                DOMDocumentFactory::fromFile(
                    dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_KeyInfo.xml',
                )->documentElement,
            ),
            [
                new Chunk(
                    DOMDocumentFactory::fromString(
                        '<ds:Object xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><some>Chunk</some></ds:Object>',
                    )->documentElement,
                ),
            ],
            'def456',
        );

        $signatureElement = $signature->toXML();
        $xpCache = XPath::getXPath($signatureElement);

        $signedInfo = XPath::xpQuery($signatureElement, './ds:SignedInfo', $xpCache);
        $this->assertCount(1, $signedInfo);

        /** @psalm-var \DOMElement[] $signatureElements */
        $signatureElements = XPath::xpQuery($signatureElement, './ds:SignedInfo/following-sibling::*', $xpCache);

        // Test ordering of Signature contents
        $this->assertCount(3, $signatureElements);
        $this->assertEquals('ds:SignatureValue', $signatureElements[0]->tagName);
        $this->assertEquals('ds:KeyInfo', $signatureElements[1]->tagName);
        $this->assertEquals('ds:Object', $signatureElements[2]->tagName);
    }


    /**
     * Test creating a SignatureValue object from XML.
     */
    public function testUnmarshalling(): void
    {
        $signature = Signature::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('def456', $signature->getId());

        $signedInfo = $signature->getSignedInfo();
        $this->assertInstanceOf(SignedInfo::class, $signedInfo);

        $signatureValue = $signature->getSignatureValue();
        $this->assertInstanceOf(SignatureValue::class, $signatureValue);

        $keyInfo = $signature->getKeyInfo();
        $this->assertInstanceOf(KeyInfo::class, $keyInfo);

        $objects = $signature->getObjects();
        $this->assertCount(1, $objects);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($signature),
        );
    }
}
