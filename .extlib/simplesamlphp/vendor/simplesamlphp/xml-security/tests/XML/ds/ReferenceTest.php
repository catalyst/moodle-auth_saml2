<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ds;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\XML\ds\DigestMethod;
use SimpleSAML\XMLSecurity\XML\ds\DigestValue;
use SimpleSAML\XMLSecurity\XML\ds\Reference;
use SimpleSAML\XMLSecurity\XML\ds\Transform;
use SimpleSAML\XMLSecurity\XML\ds\Transforms;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ds\ReferenceTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ds\Reference
 * @covers \SimpleSAML\XMLSecurity\XML\ds\AbstractDsElement
 *
 * @package simplesamlphp/saml2
 */
final class ReferenceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /**
     */
    public function setUp(): void
    {
        $this->testedClass = Reference::class;

        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/xmldsig1-schema.xsd';

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_Reference.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $reference = new Reference(
            new DigestMethod(C::DIGEST_SHA256),
            new DigestValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI='),
            new Transforms(
                [
                    new Transform(C::XMLDSIG_ENVELOPED),
                    new Transform(C::C14N_EXCLUSIVE_WITHOUT_COMMENTS),
                ],
            ),
            'ghi789',
            'urn:some:type',
            '#_1e280ee704fb1d8d9dec4bd6c1889ec96942921153',
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($reference),
        );
    }


    /**
     */
    public function testMarshallingReferenceElementOrdering(): void
    {
        $reference = new Reference(
            new DigestMethod(C::DIGEST_SHA256),
            new DigestValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI='),
            new Transforms(
                [
                    new Transform(C::XMLDSIG_ENVELOPED),
                    new Transform(C::C14N_EXCLUSIVE_WITHOUT_COMMENTS),
                ]
            ),
            'ghi789',
            'urn:some:type',
            '#_1e280ee704fb1d8d9dec4bd6c1889ec96942921153',
        );

        $referenceElement = $reference->toXML();
        $children = $referenceElement->childNodes;

        $this->assertEquals('ds:Transforms', $children[0]->tagName);
        $this->assertEquals('ds:DigestMethod', $children[1]->tagName);
        $this->assertEquals('ds:DigestValue', $children[2]->tagName);
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $reference = Reference::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('ghi789', $reference->getId());
        $this->assertEquals('urn:some:type', $reference->getType());
        $this->assertEquals('#_1e280ee704fb1d8d9dec4bd6c1889ec96942921153', $reference->getURI());

        $digestMethod = $reference->getDigestMethod();
        $this->assertEquals(C::DIGEST_SHA256, $digestMethod->getAlgorithm());

        $digestValue = $reference->getDigestValue();
        $this->assertEquals('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=', $digestValue->getContent());


        $transforms = $reference->getTransforms();
        $transform = $transforms->getTransform();
        $this->assertCount(2, $transform);

        $this->assertEquals(C::XMLDSIG_ENVELOPED, $transform[0]->getAlgorithm());
        $this->assertEquals(C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, $transform[1]->getAlgorithm());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($reference),
        );
    }
}
