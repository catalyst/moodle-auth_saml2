<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ds;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\XML\ds\CanonicalizationMethod;
use SimpleSAML\XMLSecurity\XML\ds\Reference;
use SimpleSAML\XMLSecurity\XML\ds\SignatureMethod;
use SimpleSAML\XMLSecurity\XML\ds\SignedInfo;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ds\SignedInfoTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ds\SignedInfo
 * @covers \SimpleSAML\XMLSecurity\XML\ds\AbstractDsElement
 *
 * @package simplesamlphp/xml-security
 */
final class SignedInfoTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /**
     */
    public function setUp(): void
    {
        $this->testedClass = SignedInfo::class;

        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/xmldsig1-schema.xsd';

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignedInfo.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $signedInfo = new SignedInfo(
            new CanonicalizationMethod(C::C14N_EXCLUSIVE_WITHOUT_COMMENTS),
            new SignatureMethod(C::SIG_RSA_SHA256),
            [
                Reference::fromXML(
                    DOMDocumentFactory::fromFile(
                        dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_Reference.xml',
                    )->documentElement,
                ),
            ],
            'cba321',
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($signedInfo),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $signedInfo = SignedInfo::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('cba321', $signedInfo->getId());

        $this->assertEquals(
            C::C14N_EXCLUSIVE_WITHOUT_COMMENTS,
            $signedInfo->getCanonicalizationMethod()->getAlgorithm(),
        );
        $this->assertEquals(
            C::SIG_RSA_SHA256,
            $signedInfo->getSignatureMethod()->getAlgorithm(),
        );

        $references = $signedInfo->getReferences();
        $this->assertCount(1, $references);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($signedInfo),
        );
    }


    /**
     *
     */
    public function canonicalization(\DOMElement $xml, SignedInfo $signedInfo): void
    {
        $this->assertEquals(
            $xml->C14N(true, false),
            $signedInfo->canonicalize(C::C14N_EXCLUSIVE_WITHOUT_COMMENTS),
        );
        $this->assertEquals(
            $xml->C14N(false, false),
            $signedInfo->canonicalize(C::C14N_INCLUSIVE_WITHOUT_COMMENTS),
        );
        $this->assertEquals(
            $xml->C14N(true, true),
            $signedInfo->canonicalize(C::C14N_EXCLUSIVE_WITH_COMMENTS),
        );
        $this->assertEquals(
            $xml->C14N(false, true),
            $signedInfo->canonicalize(C::C14N_INCLUSIVE_WITH_COMMENTS),
        );
    }


    /**
     * Test that canonicalization works fine.
     */
    public function testCanonicalization(): void
    {
        $xml =  DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignedInfoWithComments.xml',
        )->documentElement;
        $signedInfo = SignedInfo::fromXML($xml);
        $this->canonicalization($xml, $signedInfo);
    }


    /**
     * Test that canonicalization works fine even after serializing and unserializing
     */
    public function testCanonicalizationAfterSerialization(): void
    {
        $xml =  DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(__FILE__))) . '/resources/xml/ds_SignedInfoWithComments.xml',
        )->documentElement;
        $signedInfo = unserialize(serialize(SignedInfo::fromXML($xml)));
        $this->canonicalization($xml, $signedInfo);
    }
}
