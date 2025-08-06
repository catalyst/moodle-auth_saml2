<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\Alg\KeyTransport;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\RSA;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\BlacklistedAlgorithmException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Key\PublicKey;

/**
 * Tests for \SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportALgorithmFactory
 *
 * @package simplesamlphp/xml-security
 */
class KeyTransportAlgorithmFactoryTest extends TestCase
{
    /** @var \SimpleSAML\XMLSecurity\Key\PublicKey */
    protected PublicKey $pkey;


    public function setUp(): void
    {
        $this->pkey = PublicKey::fromFile('tests/pubkey.pem');
    }


    /**
     * Test for unsupported algorithms.
     */
    public function testGetUnknownAlgorithm(): void
    {
        $factory = new KeyTransportAlgorithmFactory([]);
        $this->expectException(UnsupportedAlgorithmException::class);
        $factory->getAlgorithm('Unsupported algorithm identifier', $this->pkey);
    }


    /**
     * Test the default blacklisted algorithms.
     */
    public function testDefaultBlacklistedAlgorithm(): void
    {
        $factory = new KeyTransportAlgorithmFactory();
        $algorithm = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP, $this->pkey);
        $this->assertInstanceOf(RSA::class, $algorithm);
        $this->assertEquals(C::KEY_TRANSPORT_OAEP, $algorithm->getAlgorithmId());

        $algorithm = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->pkey);
        $this->assertInstanceOf(RSA::class, $algorithm);
        $this->assertEquals(C::KEY_TRANSPORT_OAEP_MGF1P, $algorithm->getAlgorithmId());

        $this->expectException(BlacklistedAlgorithmException::class);
        $factory->getAlgorithm(C::KEY_TRANSPORT_RSA_1_5, $this->pkey);
    }


    /**
     * Test for manually blacklisted algorithms.
     */
    public function testBlacklistedAlgorithm(): void
    {
        $factory = new KeyTransportAlgorithmFactory([C::KEY_TRANSPORT_OAEP_MGF1P]);
        $algorithm = $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP, $this->pkey);
        $this->assertInstanceOf(RSA::class, $algorithm);
        $this->assertEquals(C::KEY_TRANSPORT_OAEP, $algorithm->getAlgorithmId());
        $this->assertEquals($this->pkey, $algorithm->getKey());

        $algorithm = $factory->getAlgorithm(C::KEY_TRANSPORT_RSA_1_5, $this->pkey);
        $this->assertInstanceOf(RSA::class, $algorithm);
        $this->assertEquals(C::KEY_TRANSPORT_RSA_1_5, $algorithm->getAlgorithmId());

        $this->expectException(BlacklistedAlgorithmException::class);
        $factory->getAlgorithm(C::KEY_TRANSPORT_OAEP_MGF1P, $this->pkey);
    }
}
