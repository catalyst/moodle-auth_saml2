<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\xenc;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\RSA;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;
use SimpleSAML\XMLSecurity\Utils\XPath;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;
use SimpleSAML\XMLSecurity\XMLSecurityDsig;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\xenc\EncryptedKeyTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\xenc\AbstractXencElement
 * @covers \SimpleSAML\XMLSecurity\XML\xenc\AbstractEncryptedType
 * @covers \SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey
 *
 * @package simplesamlphp/xml-security
 */
final class EncryptedKeyTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /** @var PrivateKey */
    protected PrivateKey $privKey;

    /** @var PublicKey */
    protected PublicKey $pubKey;

    /**
     */
    public function setup(): void
    {
        $this->testedClass = EncryptedKey::class;

        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/xenc-schema.xsd';

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/tests/resources/xml/xenc_EncryptedKey.xml',
        );

        $this->privKey = PrivateKey::fromFile(dirname(dirname(dirname(__FILE__))) . '/privkey.pem');
        $this->pubKey = PublicKey::fromFile(dirname(dirname(dirname(__FILE__))) . '/pubkey.pem');
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $encryptedKey = new EncryptedKey(
            new CipherData(new CipherValue('3W3C4UoWshi02yrqsLC2z8Qr1FjdTz7LV9CvpunilOX4teGKsjKqNbS92DKcXLwS8s'
                . '4eHBdHejiL1bySDQT5diN/TVo8zz0AmPwX3/eHPQE91NWzceB+yaoEDauMPvi7twUdoipbLZa7cyT4QR+RO9w5P5wf4wDoTPUoQ'
                . 'V6dF9YSJqehuRFCqVJprIDZNfrKnm7WfwMiaMLvaLVdLWgXjuVdiH0lT/F4KJrhJwAnjp57KGn9mhAcwkFe+qDIMSi8Ond6I0FO'
                . 'V3SOx8NxpSTHYfZ4qE1Xn/dvUUXqgRnEFPHAw4JFmJPjgTSCPU6BdwBLzqVjh1pCLoCn66P/Zt7I9Q==')),
            'Encrypted_KEY_ID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'urn:x-simplesamlphp:encoding',
            'some_ENTITY_ID',
            new CarriedKeyName('Name of the key'),
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
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
            new ReferenceList([new DataReference('#Encrypted_DATA_ID')]),
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($encryptedKey),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $encryptedKey = new EncryptedKey(
            new CipherData(new CipherValue('3W3C4UoWshi02yrqsLC2z8Qr1FjdTz7LV9CvpunilOX4teGKsjKqNbS92DKcXLwS8s'
                . '4eHBdHejiL1bySDQT5diN/TVo8zz0AmPwX3/eHPQE91NWzceB+yaoEDauMPvi7twUdoipbLZa7cyT4QR+RO9w5P5wf4wDoTPUoQ'
                . 'V6dF9YSJqehuRFCqVJprIDZNfrKnm7WfwMiaMLvaLVdLWgXjuVdiH0lT/F4KJrhJwAnjp57KGn9mhAcwkFe+qDIMSi8Ond6I0FO'
                . 'V3SOx8NxpSTHYfZ4qE1Xn/dvUUXqgRnEFPHAw4JFmJPjgTSCPU6BdwBLzqVjh1pCLoCn66P/Zt7I9Q==')),
            'Encrypted_KEY_ID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'urn:x-simplesamlphp:encoding',
            'some_ENTITY_ID',
            new CarriedKeyName('Name of the key'),
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
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
            new ReferenceList([new DataReference('#Encrypted_DATA_ID')]),
        );

        // Marshall it to a \DOMElement
        $encryptedKeyElement = $encryptedKey->toXML();

        $xpCache = XPath::getXPath($encryptedKeyElement);
        // Test for a ReferenceList
        $encryptedKeyElements = XPath::xpQuery(
            $encryptedKeyElement,
            './xenc:ReferenceList',
            $xpCache,
        );
        $this->assertCount(1, $encryptedKeyElements);

        // Test ordering of EncryptedKey contents
        $encryptedKeyElements = XPath::xpQuery(
            $encryptedKeyElement,
            './xenc:ReferenceList/following-sibling::*',
            $xpCache,
        );
        $this->assertCount(1, $encryptedKeyElements);
        $this->assertEquals('xenc:CarriedKeyName', $encryptedKeyElements[0]->tagName);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $encryptedKey = EncryptedKey::fromXML($this->xmlRepresentation->documentElement);

        $cipherData = $encryptedKey->getCipherData();
        $this->assertEquals(
            '3W3C4UoWshi02yrqsLC2z8Qr1FjdTz7LV9CvpunilOX4teGKsjKqNbS92DKcXLwS8s4eHBdHejiL1bySDQT5diN/TVo8zz0A'
            . 'mPwX3/eHPQE91NWzceB+yaoEDauMPvi7twUdoipbLZa7cyT4QR+RO9w5P5wf4wDoTPUoQV6dF9YSJqehuRFCqVJprIDZNfrKnm7WfwM'
            . 'iaMLvaLVdLWgXjuVdiH0lT/F4KJrhJwAnjp57KGn9mhAcwkFe+qDIMSi8Ond6I0FOV3SOx8NxpSTHYfZ4qE1Xn/dvUUXqgRnEFPHAw4'
            . 'JFmJPjgTSCPU6BdwBLzqVjh1pCLoCn66P/Zt7I9Q==',
            $cipherData->getCipherValue()->getContent(),
        );

        $encryptionMethod = $encryptedKey->getEncryptionMethod();
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#rsa-1_5', $encryptionMethod->getAlgorithm());

        $keyInfo = $encryptedKey->getKeyInfo();
        $info = $keyInfo->getInfo();
        $this->assertCount(1, $info);

        $encKey = $info[0];
        $this->assertInstanceOf(EncryptedKey::class, $encKey);

        $referenceList = $encryptedKey->getReferenceList();
        $this->assertEmpty($referenceList->getKeyReferences());
        $dataRefs = $referenceList->getDataReferences();
        $this->assertCount(1, $dataRefs);
        $this->assertEquals('#Encrypted_DATA_ID', $dataRefs[0]->getURI());

        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $encryptedKey->getType());
        $this->assertEquals('urn:x-simplesamlphp:encoding', $encryptedKey->getEncoding());
        $this->assertEquals('text/plain', $encryptedKey->getMimeType());
        $this->assertEquals('Encrypted_KEY_ID', $encryptedKey->getID());
        $this->assertEquals('some_ENTITY_ID', $encryptedKey->getRecipient());
        $this->assertEquals('Name of the key', $encryptedKey->getCarriedKeyName()->getContent());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($encryptedKey),
        );
    }


    /**
     * Test encryption and decryption with PKCS1 RSA 1.5.
     */
    public function testPKCS1Encryption(): void
    {
        $factory = new KeyTransportAlgorithmFactory([]);
        $encryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_RSA_1_5, $this->pubKey);
        $symmetricKey = SymmetricKey::generate(8);
        $encryptedKey = EncryptedKey::fromKey(
            $symmetricKey,
            $encryptor,
            new EncryptionMethod(C::KEY_TRANSPORT_RSA_1_5),
        );

        $decryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_RSA_1_5, $this->privKey);
        $decryptedKey = $encryptedKey->decrypt($decryptor);

        $this->assertEquals(bin2hex($symmetricKey->get()), bin2hex($decryptedKey));
    }


    /**
     * Test encryption and decryption with RSA OAEP
     */
    public function testOAEPEncryption(): void
    {
        $factory = new KeyTransportAlgorithmFactory([]);
        $encryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP, $this->pubKey);
        $symmetricKey = SymmetricKey::generate(16);
        $encryptedKey = EncryptedKey::fromKey(
            $symmetricKey,
            $encryptor,
            new EncryptionMethod(C::KEY_TRANSPORT_OAEP),
        );

        $decryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP, $this->privKey);
        $decryptedKey = $encryptedKey->decrypt($decryptor);

        $this->assertEquals(bin2hex($symmetricKey->get()), bin2hex($decryptedKey));
    }


    /**
     * Test encryption and decryption with RSA OAEP-MGF1P
     */
    public function testOAEMGF1PPEncryption(): void
    {
        $factory = new KeyTransportAlgorithmFactory([]);
        $encryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->pubKey);
        $symmetricKey = SymmetricKey::generate(16);
        $encryptedKey = EncryptedKey::fromKey(
            $symmetricKey,
            $encryptor,
            new EncryptionMethod(C::KEY_TRANSPORT_OAEP_MGF1P),
        );

        $decryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->privKey);
        $decryptedKey = $encryptedKey->decrypt($decryptor);

        $this->assertEquals(bin2hex($symmetricKey->get()), bin2hex($decryptedKey));
    }
}
