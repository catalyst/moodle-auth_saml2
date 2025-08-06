<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML;

use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\RuntimeException;
use SimpleSAML\XMLSecurity\Key\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function dirname;
use function file_get_contents;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\SignedElementTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\AbstractSignedXMLElement
 * @covers \SimpleSAML\XMLSecurity\XML\SignedElementTrait
 * @covers \SimpleSAML\XMLSecurity\Test\XML\CustomSignable
 *
 * @package simplesamlphp/xml-security
 */
final class SignedElementTest extends TestCase
{
    /** @var string */
    private string $certificate;

    /** @var \DOMElement */
    private DOMElement $signedDocumentWithComments;

    /** @var \DOMElement */
    private DOMElement $signedDocument;

    /** @var \DOMElement  */
    private DOMElement $tamperedDocument;


    /**
     */
    public function setUp(): void
    {
        $this->signedDocumentWithComments = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/custom_CustomSignedWithComments.xml',
        )->documentElement;

        $this->signedDocument = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/custom_CustomSigned.xml',
        )->documentElement;

        $this->tamperedDocument = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/custom_CustomSignedTampered.xml',
        )->documentElement;

        $this->certificate = file_get_contents(
            dirname(dirname(__FILE__)) . '/resources/certificates/selfsigned.simplesamlphp.org.crt',
        );
    }


    /**
     * Test creating a signed object from its XML representation.
     */
    public function testUnmarshalling(): void
    {
        $customSigned = CustomSignable::fromXML($this->signedDocument);

        $this->assertEquals(
            $this->signedDocument->ownerDocument->saveXML($this->signedDocument),
            strval($customSigned),
        );
    }


    /**
     * Test the verification of a signature with a given key.
     */
    public function testSuccessfulVerifyingWithGivenKey(): void
    {
        $customSigned = CustomSignable::fromXML($this->signedDocument);

        $this->assertTrue($customSigned->isSigned());
        $signature = $customSigned->getSignature();
        $this->assertInstanceOf(Signature::class, $signature);
        $sigAlg = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
        $this->assertEquals(C::SIG_RSA_SHA256, $sigAlg);
        $factory = new SignatureAlgorithmFactory();
        $certificate = new X509Certificate($this->certificate);
        $verifier = $factory->getAlgorithm($sigAlg, $certificate);

        $verified = $customSigned->verify($verifier);
        $this->assertInstanceOf(CustomSignable::class, $verified);
        $this->assertFalse($verified->isSigned());
        $this->assertEquals(
            '<ssp:CustomSignable xmlns:ssp="urn:ssp:custom"><ssp:Some>Chunk</ssp:Some></ssp:CustomSignable>',
            strval($verified),
        );
        $this->assertEquals($certificate, $verified->getVerifyingKey());
    }


    /**
     * Test the verification of a signature without passing a key, just what's in KeyInfo
     */
    public function testSuccessfulVerifyingWithoutKey(): void
    {
        $customSigned = CustomSignable::fromXML($this->signedDocument);

        $this->assertTrue($customSigned->isSigned());
        $signature = $customSigned->getSignature();
        $this->assertInstanceOf(Signature::class, $signature);
        $sigAlg = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
        $this->assertEquals(C::SIG_RSA_SHA256, $sigAlg);
        $certificate = new X509Certificate($this->certificate);

        $verified = $customSigned->verify();
        $this->assertInstanceOf(CustomSignable::class, $verified);
        $this->assertFalse($verified->isSigned());
        $this->assertEquals(
            '<ssp:CustomSignable xmlns:ssp="urn:ssp:custom"><ssp:Some>Chunk</ssp:Some></ssp:CustomSignable>',
            strval($verified),
        );
        $validatingKey = $verified->getVerifyingKey();
        $this->assertInstanceOf(X509Certificate::class, $validatingKey);
        /** @var \SimpleSAML\XMLSecurity\Key\X509Certificate $validatingKey */
        $this->assertEquals($certificate->getCertificate(), $validatingKey->getCertificate());
    }


    /**
     * Test that verifying a tampered signature, without giving a key for verification, fails as expected.
     */
    public function testVerifyingTamperedSignatureWithoutKeyFails(): void
    {
        $customSigned = CustomSignable::fromXML($this->tamperedDocument);

        $this->assertTrue($customSigned->isSigned());
        $signature = $customSigned->getSignature();
        $this->assertInstanceOf(Signature::class, $signature);
        $sigAlg = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
        $this->assertEquals(C::SIG_RSA_SHA256, $sigAlg);

        $this->expectException(RuntimeException::class);
        $this->expectDeprecationMessage('Failed to verify signature.');
        $customSigned->verify();
    }


    /**
     * Test that verifying a tampered signature with a given key fails as expected.
     */
    public function testVerifyingTamperedSignatureWithKeyFails(): void
    {
        $customSigned = CustomSignable::fromXML($this->tamperedDocument);

        $this->assertTrue($customSigned->isSigned());
        $signature = $customSigned->getSignature();
        $this->assertInstanceOf(Signature::class, $signature);
        $sigAlg = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
        $this->assertEquals(C::SIG_RSA_SHA256, $sigAlg);
        $factory = new SignatureAlgorithmFactory();
        $certificate = new X509Certificate($this->certificate);
        $verifier = $factory->getAlgorithm($sigAlg, $certificate);

        $this->expectException(RuntimeException::class);
        $this->expectDeprecationMessage('Failed to verify signature.');
        $customSigned->verify($verifier);
    }



    /**
     * Test the verification of a signature with a given key, for an element that has comments in it.
     *
     * In this case, canonicalization must remove the comments, and the object resulting the verification must NOT
     * have them.
     */
    public function testSuccessfulVerifyingDocumentWithComments(): void
    {
        $customSigned = CustomSignable::fromXML($this->signedDocumentWithComments);

        $this->assertTrue($customSigned->isSigned());
        $signature = $customSigned->getSignature();
        $this->assertInstanceOf(Signature::class, $signature);
        $sigAlg = $signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
        $this->assertEquals(C::SIG_RSA_SHA256, $sigAlg);
        $factory = new SignatureAlgorithmFactory();
        $certificate = new X509Certificate($this->certificate);
        $verifier = $factory->getAlgorithm($sigAlg, $certificate);

        // verify first that our dumb object normally retains comments
        $this->assertEquals(
            $this->signedDocumentWithComments->ownerDocument->saveXML($this->signedDocumentWithComments),
            strval($customSigned),
        );

        $verified = $customSigned->verify($verifier);
        $this->assertInstanceOf(CustomSignable::class, $verified);
        $this->assertFalse($verified->isSigned());
        $this->assertEquals(
            '<ssp:CustomSignable xmlns:ssp="urn:ssp:custom"><ssp:Some><!-- comment -->Chunk<!-- comment --></ssp:Some></ssp:CustomSignable>',
            strval($verified),
        );
        $this->assertEquals($certificate, $verified->getVerifyingKey());
    }
}
