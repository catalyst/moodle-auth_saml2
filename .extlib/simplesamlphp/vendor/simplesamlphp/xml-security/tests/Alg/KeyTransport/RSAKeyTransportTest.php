<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\Alg\KeyTransport;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;

/**
 * Tests for \SimpleSAML\XMLSecurity\Alg\KeyTransport\RSA.
 *
 * @package simplesamlphp/xml-security
 */
class RSAKeyTransportTest extends TestCase
{
    /** @var \SimpleSAML\XMLSecurity\Key\PrivateKey */
    protected PrivateKey $privateKey;

    /** @var \SimpleSAML\XMLSecurity\Key\PublicKey */
    protected PublicKey $publicKey;

    /** @var string */
    protected string $plaintext = 'plaintext';

    /** @var \SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory */
    protected KeyTransportAlgorithmFactory $factory;


    /**
     *
     */
    public function setUp(): void
    {
        $this->publicKey = PublicKey::fromFile('tests/pubkey.pem');
        $this->privateKey = PrivateKey::fromFile('tests/privkey.pem');
        $this->factory = new KeyTransportAlgorithmFactory([]);
    }


    /**
     * Test encrypting with RSA.
     */
    public function testEncrypt(): void
    {
        // test RSA 1.5
        $rsa = $this->factory->getAlgorithm(C::KEY_TRANSPORT_RSA_1_5, $this->publicKey);
        $encrypted = $rsa->encrypt($this->plaintext);
        $this->assertNotEmpty($encrypted);
        $this->assertEquals(256, strlen($encrypted));

        // test RSA-OAEP
        $rsa = $this->factory->getAlgorithm(C::KEY_TRANSPORT_OAEP, $this->publicKey);
        $encrypted = $rsa->encrypt($this->plaintext);
        $this->assertNotEmpty($encrypted);
        $this->assertEquals(256, strlen($encrypted));

        // test RSA-OAEP-MGF1P
        $rsa = $this->factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->publicKey);
        $encrypted = $rsa->encrypt($this->plaintext);
        $this->assertNotEmpty($encrypted);
        $this->assertEquals(256, strlen($encrypted));
    }


    /**
     * Test decrypting with RSA.
     */
    public function testDecrypt(): void
    {
        $secret = "7392d8ec40a862fbb02786bab41481b2";
        // test RSA-OAEP-MGF1P
        $ciphertext = "pKChAdt8YXFRkOfgARrW2IEwlnK1ZWEqnSvVhKK9VSiC5yICrf/dHL2BmkjJvG1wbOqBfJXCDCn/F+CeVDcZBa4kg/oQe"
                    . "UpIF7FMYaUK7Q9529uni/P5tMegQkOWeD7M76vlt5TXXbhRV/jZqCa5W5WIkhns53/2e97FKWOujPxrnydhvgzP9ztOPj"
                    . "OgbqIeJBkW432XQkgOSq6AANgfsgwrugQSusQcsJzuYRRhfSKTkH79t2sCGDlqV9XlAs1DOv2+elWEyL5G58/nDwaecRo"
                    . "Zgo3EV4EdOudeNesvrNnZrsNRaR/qchUH+G+R9RDnXO4l3qookkH+6o222osxcQ==";
        $rsa = $this->factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->privateKey);
        $plaintext = $rsa->decrypt(base64_decode($ciphertext));
        $this->assertEquals($secret, bin2hex($plaintext));

        // test RSA-OAEP (should behave the same as MGF1P)
        $rsa = $this->factory->getAlgorithm(C::KEY_TRANSPORT_OAEP, $this->privateKey);
        $plaintext = $rsa->decrypt(base64_decode($ciphertext));
        $this->assertEquals($secret, bin2hex($plaintext));

        // test RSA-1.5
        $ciphertext = "QFMBLk+OSLGcVsurQsW3DBjbUpfyxtNdalBiISgKvYnwt/K+iPuvttHpJ726eQdDPFlkpZCEtl2w4sd380tVyiqajoLb7"
                    . "3l8kSXG/KTedBDgeWOlxKpy2v57Xz1LrDDu4ZFCWx55fVyQ0g3J42GjViJL4qYKL8ccGly4RyRpcOwSGr5PCB5HK7F+Wl"
                    . "fjP4zGbgAyKzKuFo5ta7tI9kU+V9zT2QVeEDoTurFFJHA4zgYdSQNNcddBGunb1dAH4nAfBHF3Aq1UMAuXGk/C9Q9O123"
                    . "FJgLue/0dY0AsvwEoUdULRFfGN2N4cwBovHmyRvs3Qh+z9j9Okd5QcevEdT42ew==";
        $rsa = $this->factory->getAlgorithm(C::KEY_TRANSPORT_RSA_1_5, $this->privateKey);
        $plaintext = $rsa->decrypt(base64_decode($ciphertext));
        $this->assertEquals($secret, bin2hex($plaintext));
    }
}
