<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\xenc;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Utils\XPath;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XMLSecurityDsig;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\xenc\EncryptedDataTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\xenc\AbstractXencElement
 * @covers \SimpleSAML\XMLSecurity\XML\xenc\AbstractEncryptedType
 * @covers \SimpleSAML\XMLSecurity\XML\xenc\EncryptedData
 *
 * @package simplesamlphp/xml-security
 */
final class EncryptedDataTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->testedClass = EncryptedData::class;

        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/xenc-schema.xsd';

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/tests/resources/xml/xenc_EncryptedData.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $encryptedData = new EncryptedData(
            new CipherData(new CipherValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=')),
            'MyID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'urn:x-simplesamlphp:encoding',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo(
                [
                    new EncryptedKey(
                        new CipherData(new CipherValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=')),
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        new EncryptionMethod('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'),
                    ),
                ],
            ),
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($encryptedData),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $encryptedData = new EncryptedData(
            new CipherData(new CipherValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=')),
            'MyID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'urn:x-simplesamlphp:encoding',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo(
                [
                    new EncryptedKey(
                        new CipherData(new CipherValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=')),
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        new EncryptionMethod('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'),
                    ),
                ],
            ),
        );

        $encryptedDataElement = $encryptedData->toXML();
        $xpCache = XPath::getXPath($encryptedDataElement);

        // Test for an EncryptionMethod
        $encryptedDataElements = XPath::xpQuery($encryptedDataElement, './xenc:EncryptionMethod', $xpCache);
        $this->assertCount(1, $encryptedDataElements);

        // Test ordering of EncryptedData contents
        /** @psalm-var \DOMElement[] $encryptedDataElements */
        $encryptedDataElements = XPath::xpQuery(
            $encryptedDataElement,
            './xenc:EncryptionMethod/following-sibling::*',
            $xpCache
        );
        $this->assertCount(2, $encryptedDataElements);
        $this->assertEquals('ds:KeyInfo', $encryptedDataElements[0]->tagName);
        $this->assertEquals('xenc:CipherData', $encryptedDataElements[1]->tagName);

        // EncryptionProperties is currently not supported
        //$this->assertEquals('xenc:EncryptionProperties', $encryptedDataElements[2]->tagName);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $encryptedData = EncryptedData::fromXML($this->xmlRepresentation->documentElement);

        $cipherData = $encryptedData->getCipherData();
        $this->assertEquals(
            '/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=',
            $cipherData->getCipherValue()->getContent(),
        );

        $encryptionMethod = $encryptedData->getEncryptionMethod();
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#aes128-cbc', $encryptionMethod->getAlgorithm());

        $keyInfo = $encryptedData->getKeyInfo();
        $info = $keyInfo->getInfo();
        $this->assertCount(1, $info);

        $encKey = $info[0];
        $this->assertInstanceOf(EncryptedKey::class, $encKey);

        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $encryptedData->getType());
        $this->assertEquals('text/plain', $encryptedData->getMimeType());
        $this->assertEquals('MyID', $encryptedData->getID());
        $this->assertEquals('urn:x-simplesamlphp:encoding', $encryptedData->getEncoding());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($encryptedData),
        );
    }
}
