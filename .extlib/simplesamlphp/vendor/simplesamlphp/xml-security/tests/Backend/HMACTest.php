<?php

namespace SimpleSAML\XMLSecurity\Test\Backend;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Backend\HMAC;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

use function bin2hex;
use function hex2bin;

/**
 * Test for SimpleSAML\XMLSecurity\Backend\HMAC.
 *
 * @package SimpleSAML\XMLSecurity\Backend
 */
final class HMACTest extends TestCase
{
    public const PLAINTEXT = "plaintext";

    public const SIGNATURE = "61b85d9e800ed0eca556a304cc9e1ac7ae8eecb3";

    public const SECRET = 'secret key';

    /** @var \SimpleSAML\XMLSecurity\Key\SymmetricKey */
    protected SymmetricKey $key;


    /**
     * Initialize shared key.
     */
    protected function setUp(): void
    {
        $this->key = new SymmetricKey(self::SECRET);
    }


    /**
     * Test signing of messages.
     */
    public function testSign(): void
    {
        $backend = new HMAC();
        $backend->setDigestAlg(C::DIGEST_SHA1);
        $this->assertEquals(self::SIGNATURE, bin2hex($backend->sign($this->key, self::PLAINTEXT)));
    }


    /**
     * Test for wrong digests.
     */
    public function testSetUnknownDigest(): void
    {
        $backend = new HMAC();
        $this->expectException(InvalidArgumentException::class);
        $backend->setDigestAlg('foo');
    }


    /**
     * Test verification of signatures.
     */
    public function testVerify(): void
    {
        // test successful verification
        $backend = new HMAC();
        $backend->setDigestAlg(C::DIGEST_SHA1);
        $this->assertTrue($backend->verify($this->key, self::PLAINTEXT, hex2bin(self::SIGNATURE)));

        // test failure to verify with different plaintext
        $this->assertFalse($backend->verify($this->key, 'foo', hex2bin(self::SIGNATURE)));

        // test failure to verify with different signature
        $this->assertFalse($backend->verify(
            $this->key,
            self::PLAINTEXT,
            hex2bin('12345678901234567890abcdefabcdef12345678'),
        ));

        // test failure to verify with wrong key
        $key = new SymmetricKey('wrong secret');
        $this->assertFalse($backend->verify($key, self::PLAINTEXT, hex2bin(self::SIGNATURE)));

        // test failure to verify with wrong digest algorithm
        $backend->setDigestAlg(C::DIGEST_RIPEMD160);
        $this->assertFalse($backend->verify($this->key, self::PLAINTEXT, hex2bin(self::SIGNATURE)));
    }
}
