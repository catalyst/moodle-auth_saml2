<?php

namespace SimpleSAML\XMLSecurity\Test\Alg\Signature;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Alg\Signature\HMAC;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;
use SimpleSAML\XMLSecurity\Key\X509Certificate;
use TypeError;

use function bin2hex;

/**
 * Tests for SimpleSAML\XMLSecurity\Alg\Signature\HMAC.
 *
 * @package SimpleSAML\Signature
 */
final class HMACSignatureTest extends TestCase
{
    /** @var string */
    protected string $plaintext = 'plaintext';

    /** @var string */
    protected string $secret = 'de54fbd0f10c34df6e800b11043024fa';

    /** @var \SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory */
    protected SignatureAlgorithmFactory $factory;


    public function setUp(): void
    {
        $this->factory = new SignatureAlgorithmFactory([]);
    }


    /**
     * Test that signing works.
     */
    public function testSign(): void
    {
        $key = new SymmetricKey($this->secret);

        // test HMAC-SHA1
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA1, $key);
        $this->assertEquals('655c3b4277b39f31dedf5adc7f4cc9f07da7102c', bin2hex($hmac->sign($this->plaintext)));

        // test HMAC-SHA224
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA224, $key);
        $this->assertEquals(
            '645405ccc725e10022e5a89e98cc33db07c0cd89ba78c21caf931f40',
            bin2hex($hmac->sign($this->plaintext)),
        );

        // test HMAC-SHA256
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA256, $key);
        $this->assertEquals(
            '721d8385785a3d4c8d16c7b4a96b343728a11e221656e6dd9502d540d4e87ef2',
            bin2hex($hmac->sign($this->plaintext)),
        );

        // test HMAC-SHA384
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA384, $key);
        $this->assertEquals(
            'b3ad2e39a057fd7a952cffd503d30eca295c6698dc23ddf0bebf98631a0162da0db0105db156a220dec78cebaf2c202c',
            bin2hex($hmac->sign($this->plaintext)),
        );

        // test HMAC-SHA512
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA512, $key);
        $this->assertEquals(
            '9cc73c95f564a142b28340cf6e1d6b509a9e97dab6577e5d0199760a858105185252e203b6b096ad24708a2b7e34a0f506776d8' .
            '8e2f47fff055fc51342b69cdc',
            bin2hex($hmac->sign($this->plaintext)),
        );

        // test HMAC-RIPEMD160
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_RIPEMD160, $key);
        $this->assertEquals('a9fd77b68644464d08be0ba2cd998eab3e2a7b1d', bin2hex($hmac->sign($this->plaintext)));
    }


    /**
     * Test that signature verification works.
     */
    public function testVerify(): void
    {
        $key = new SymmetricKey($this->secret);

        // test HMAC-SHA1
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA1, $key);
        $this->assertTrue($hmac->verify($this->plaintext, hex2bin('655c3b4277b39f31dedf5adc7f4cc9f07da7102c')));

        // test HMAC-SHA224
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA224, $key);
        $this->assertTrue($hmac->verify(
            $this->plaintext,
            hex2bin('645405ccc725e10022e5a89e98cc33db07c0cd89ba78c21caf931f40'),
        ));

        // test HMAC-SHA256
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA256, $key);
        $this->assertTrue($hmac->verify(
            $this->plaintext,
            hex2bin('721d8385785a3d4c8d16c7b4a96b343728a11e221656e6dd9502d540d4e87ef2'),
        ));

        // test HMAC-SHA384
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA384, $key);
        $this->assertTrue($hmac->verify(
            $this->plaintext,
            hex2bin('b3ad2e39a057fd7a952cffd503d30eca295c6698dc23ddf0bebf98631a0162da0db0105db156a220dec78cebaf2c202c'),
        ));

        // test HMAC-SHA512
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA512, $key);
        $this->assertTrue($hmac->verify(
            $this->plaintext,
            hex2bin(
                '9cc73c95f564a142b28340cf6e1d6b509a9e97dab6577e5d0199760a858105185252e203b6b096ad24708a2b7e34a0f5067' .
                '76d88e2f47fff055fc51342b69cdc',
            ),
        ));

        // test HMAC-RIPEMD160
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_RIPEMD160, $key);
        $this->assertTrue($hmac->verify($this->plaintext, hex2bin('a9fd77b68644464d08be0ba2cd998eab3e2a7b1d')));
    }


    /**
     * Test that signature verification fails properly.
     */
    public function testVerificationFailure(): void
    {
        $key = new SymmetricKey($this->secret);

        // test wrong plaintext
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA1, $key);
        $this->assertFalse($hmac->verify($this->plaintext . '.', hex2bin('655c3b4277b39f31dedf5adc7f4cc9f07da7102c')));

        // test wrong signature
        $this->assertFalse($hmac->verify($this->plaintext, hex2bin('655c3b4277b39f31dedf5adc7f4cc9f07da7102d')));

        // test wrong key
        $wrongKey = new SymmetricKey('de54fbd0f10c34df6e800b11043024fb');
        $hmac = $this->factory->getAlgorithm(C::SIG_HMAC_SHA1, $wrongKey);
        $this->assertFalse($hmac->verify($this->plaintext, hex2bin('655c3b4277b39f31dedf5adc7f4cc9f07da7102c')));
    }


    /**
     * Test that verification fails when the wrong type of key is passed.
     */
    public function testVerifyWithCertificate(): void
    {
        $cert = X509Certificate::fromFile('tests/mycert.pem');

        // test type errors when possible
        $this->expectException(TypeError::class);
        new HMAC($cert);
    }


    /**
     * Test that verification fails when the wrong type of key is passed.
     */
    public function testVerifyWithPublicKey(): void
    {
        $key = PublicKey::fromFile('tests/pubkey.pem');

        // test type errors when possible
        $this->expectException(TypeError::class);
        new HMAC($key);
    }


    /**
     * Test that verification fails when the wrong type of key is passed.
     */
    public function testVerifyWithPrivateKey(): void
    {
        $key = PrivateKey::fromFile('tests/privkey.pem');

        // test type errors when possible
        $this->expectException(TypeError::class);
        new HMAC($key);
    }
}
