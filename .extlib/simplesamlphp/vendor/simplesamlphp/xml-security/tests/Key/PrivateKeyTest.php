<?php

namespace SimpleSAML\XMLSecurity\Test\Key;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Key\PrivateKey;

use function file_get_contents;
use function openssl_pkey_get_details;
use function openssl_pkey_get_private;

/**
 * Tests for SimpleSAML\XMLSecurity\Key\PrivateKey
 *
 * @package SimpleSAML\XMLSecurity\Key
 */
final class PrivateKeyTest extends TestCase
{
    /** @var resource */
    protected $privKey;

    /** @var string */
    protected string $f;


    /**
     * Initialize the test by loading the file ourselves.
     */
    protected function setUp(): void
    {
        $this->f = file_get_contents('tests/privkey.pem');
        $this->privKey = openssl_pkey_get_details(openssl_pkey_get_private($this->f));
    }


    /**
     * Cover basic creation and retrieval.
     */
    public function testCreation(): void
    {
        $k = new PrivateKey($this->f);
        $keyDetails = openssl_pkey_get_details($k->get());
        $this->assertEquals($this->privKey['key'], $keyDetails['key']);
    }


    /**
     * Test creation from a file containing the PEM-encoded private key.
     */
    public function testFromFile(): void
    {
        $k = PrivateKey::fromFile('tests/privkey.pem');
        $keyDetails = openssl_pkey_get_details($k->get());
        $this->assertEquals($this->privKey['key'], $keyDetails['key']);
    }
}
