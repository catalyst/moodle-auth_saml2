<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\Alg\Encryption;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

/**
 * Tests for \SimpleSAML\XMLSecurity\Alg\Encryption\AES.
 *
 * @package simplesamlphp/xml-security
 */
class AESEncryptionTest extends TestCase
{
    /** @var \SimpleSAML\XMLSecurity\Key\SymmetricKey */
    protected SymmetricKey $skey;

    /** @var EncryptionAlgorithmFactory */
    protected EncryptionAlgorithmFactory $factory;


    public function setUp(): void
    {
        $this->skey = new SymmetricKey(hex2bin('7392d8ec40a862fbb02786bab41481b2'));
        $this->factory = new EncryptionAlgorithmFactory([]);
    }


    /**
     * Test AES encryption.
     */
    public function testEncrypt(): void
    {
        $aes = $this->factory->getAlgorithm(C::BLOCK_ENC_AES128, $this->skey);
        $ciphertext = $aes->encrypt('plaintext');
        $this->assertNotEmpty($ciphertext);
        $this->assertEquals(32, strlen($ciphertext));
    }


    /**
     * Test AES decryption.
     */
    public function testDecrypt(): void
    {
        $ciphertext = "r0YRkEixBnAKU032/ux7avHcVTH1CIIyKaPA2qr4KlIs0LVZp5CuwQKRRi6lji4cnaFbH4jETtJhMSEfbpSdvg==";
        $aes = $this->factory->getAlgorithm(C::BLOCK_ENC_AES128, $this->skey);
        $plaintext = $aes->decrypt(base64_decode($ciphertext));
        $this->assertEquals("\n  <Value>\n\tHello, World!\n  </Value>\n", $plaintext);
    }
}
