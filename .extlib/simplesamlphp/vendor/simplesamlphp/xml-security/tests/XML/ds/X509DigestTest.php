<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ds;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key;
use SimpleSAML\XMLSecurity\Test\XML\XMLDumper;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\X509Digest;

use function base64_encode;
use function dirname;
use function hex2bin;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ds\X509DigestTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ds\AbstractDsElement
 * @covers \SimpleSAML\XMLSecurity\XML\ds\X509Digest
 *
 * @package simplesamlphp/xml-security
 */
final class X509DigestTest extends TestCase
{
    use SerializableXMLTestTrait;

    /** @var \SimpleSAML\XMLSecurity\Key\X509Certificate */
    private Key\X509Certificate $key;

    /** @var string */
    private string $digest;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = X509Digest::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/tests/resources/xml/ds_X509Digest.xml',
        );
        $this->key = new Key\X509Certificate(
            PEMCertificatesMock::getPlainPublicKey(),
        );

        $this->digest = base64_encode(hex2bin($this->key->getRawThumbprint(C::DIGEST_SHA256)));
    }


    /**
     */
    public function testMarshalling(): void
    {
        $x509digest = new X509Digest($this->digest, C::DIGEST_SHA256);

        $this->assertEquals(
            XMLDumper::dumpDOMDocumentXMLWithBase64Content($this->xmlRepresentation),
            strval($x509digest),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $x509digest = X509Digest::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals($this->digest, $x509digest->getContent());
        $this->assertEquals(C::DIGEST_SHA256, $x509digest->getAlgorithm());
    }
}
