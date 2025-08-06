<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\Alg\Encryption;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Alg\Encryption\AES;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\TripleDES;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\BlacklistedAlgorithmException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

/**
 * Tests for \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory.
 *
 * @package simplesamlphp/xml-security
 */
class EncryptionAlgorithmFactoryTest extends TestCase
{
    /** @var \SimpleSAML\XMLSecurity\Key\SymmetricKey */
    protected SymmetricKey $skey;


    public function setUp(): void
    {
        $this->skey = SymmetricKey::generate(16);
    }


    /**
     * Test for unsupported algorithms.
     */
    public function testGetUnknownAlgorithm(): void
    {
        $factory = new EncryptionAlgorithmFactory([]);
        $this->expectException(UnsupportedAlgorithmException::class);
        $factory->getAlgorithm('Unsupported algorithm identifier', $this->skey);
    }


    /**
     * Test the default blacklisted algorithms.
     */
    public function testDefaultBlacklistedAlgorithms(): void
    {
        $factory = new EncryptionAlgorithmFactory();
        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES128, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES128, $algorithm->getAlgorithmId());
        $this->assertEquals($this->skey, $algorithm->getKey());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES128_GCM, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES128_GCM, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES192, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES192, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES192_GCM, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES192_GCM, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES256, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES256, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES256_GCM, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES256_GCM, $algorithm->getAlgorithmId());

        $this->expectException(BlacklistedAlgorithmException::class);
        $factory->getAlgorithm(C::BLOCK_ENC_3DES, $this->skey);
    }


    /**
     * Test for manually blacklisted algorithms.
     */
    public function testBlacklistedAlgorithm(): void
    {
        $factory = new EncryptionAlgorithmFactory([C::BLOCK_ENC_AES256_GCM]);
        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_3DES, $this->skey);
        $this->assertInstanceOf(TripleDES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_3DES, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES128, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES128, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES128_GCM, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES128_GCM, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES192, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES192, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES192_GCM, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES192_GCM, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::BLOCK_ENC_AES256, $this->skey);
        $this->assertInstanceOf(AES::class, $algorithm);
        $this->assertEquals(C::BLOCK_ENC_AES256, $algorithm->getAlgorithmId());

        $this->expectException(BlacklistedAlgorithmException::class);
        $factory->getAlgorithm(C::BLOCK_ENC_AES256_GCM, $this->skey);
    }
}
