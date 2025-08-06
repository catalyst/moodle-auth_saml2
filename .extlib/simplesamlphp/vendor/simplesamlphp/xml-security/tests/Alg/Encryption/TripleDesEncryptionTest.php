<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\Alg\Encryption;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

/**
 * Tests for \SimpleSAML\XMLSecurity\Alg\Encryption\TripleDES.
 *
 * @package simplesamlphp/xml-security
 */
class TripleDesEncryptionTest extends TestCase
{
    /** @var \SimpleSAML\XMLSecurity\Key\SymmetricKey */
    protected SymmetricKey $skey;

    /** @var EncryptionAlgorithmFactory */
    protected EncryptionAlgorithmFactory $factory;


    public function setUp(): void
    {
        $this->skey = new SymmetricKey(hex2bin('0d6d02528a57fd9797a79db307ce1558761a454a1c1f4a57'));
        $this->factory = new EncryptionAlgorithmFactory([]);
    }


    /**
     * Test 3DES encryption.
     */
    public function testEncrypt(): void
    {
        $tripleDes = $this->factory->getAlgorithm(C::BLOCK_ENC_3DES, $this->skey);
        $ciphertext = $tripleDes->encrypt('plaintext');
        $this->assertNotEmpty($ciphertext);
        $this->assertEquals(24, strlen($ciphertext));
    }


    /**
     * Test 3DES decryption.
     */
    public function testDecrypt(): void
    {
        $ciphertext = "D+3dKq7MFK7U+8bqdlyRcvO12JV5Lahl5ALhF5eJXSfi+cbYKRbkRjvJsMKPp2Mk";
        $tripleDes = $this->factory->getAlgorithm(C::BLOCK_ENC_3DES, $this->skey);
        $plaintext = $tripleDes->decrypt(base64_decode($ciphertext));
        $this->assertEquals("\n  <Value>\n\tHello, World!\n  </Value>\n", $plaintext);
    }
}
