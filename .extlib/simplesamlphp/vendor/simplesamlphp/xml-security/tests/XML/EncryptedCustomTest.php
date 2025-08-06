<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML;

use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\EncryptedCustomTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\EncryptableElementTrait
 * @covers \SimpleSAML\XMLSecurity\XML\EncryptedElementTrait
 * @covers \SimpleSAML\XMLSecurity\Test\XML\EncryptedCustom
 *
 * @package simplesamlphp/xml-security
 */
class EncryptedCustomTest extends TestCase
{
    /** @var \DOMElement */
    private DOMElement $signedDocument;

    /** @var PrivateKey */
    protected PrivateKey $privKey;

    /** @var PublicKey */
    protected PublicKey $pubKey;


    /**
     */
    public function setUp(): void
    {
        $this->signedDocument = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/custom_CustomSigned.xml',
        )->documentElement;

        $this->privKey = PrivateKey::fromFile(dirname(dirname(__FILE__)) . '/resources/keys/privkey.pem');
        $this->pubKey = PublicKey::fromFile(dirname(dirname(__FILE__)) . '/resources/keys/pubkey.pem');
    }


    /**
     * Test encrypting an object and then decrypting it.
     */
    public function testEncryptAndDecryptSharedSecret(): void
    {
        // instantiate
        $customSigned = CustomSignable::fromXML($this->signedDocument);
        $sharedKey = SymmetricKey::generate(16);

        // encrypt
        $factory = new EncryptionAlgorithmFactory();
        $encryptor = $factory->getAlgorithm(C::BLOCK_ENC_AES128, $sharedKey);
        $encryptedCustom = new EncryptedCustom($customSigned->encrypt($encryptor));

        // decrypt
        $decryptedCustom = $encryptedCustom->decrypt($encryptor);

        $this->assertEquals($customSigned, $decryptedCustom);
    }


    /**
     * Test encrypting an object with a session key and asymmetric encryption, then decrypting it.
     */
    public function testEncryptAndDecryptSessionKey(): void
    {
        // instantiate
        $customSigned = CustomSignable::fromXML($this->signedDocument);

        // encrypt
        $factory = new KeyTransportAlgorithmFactory();
        $encryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->pubKey);
        $encryptedCustom = new EncryptedCustom($customSigned->encrypt($encryptor));

        // decrypt
        $decryptor = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->privKey);
        $decryptedCustom = $encryptedCustom->decrypt($decryptor);

        $this->assertEquals($customSigned, $decryptedCustom);
    }
}
