<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\Utils;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Utils\Certificate;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @covers \SimpleSAML\XMLSecurity\Utils\Certificate
 * @package simplesamlphp/xml-security
 */
final class CertificateTest extends TestCase
{
    /**
     * @group utilities
     * @test
     */
    public function testValidStructure(): void
    {
        $result = Certificate::hasValidStructure(
            PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY),
        );
        $this->assertTrue($result);

        $result = Certificate::hasValidStructure(
            PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::BROKEN_PUBLIC_KEY),
        );
        $this->assertFalse($result);
    }


    /**
     * @group utilities
     * @test
     */
    public function testConvertToCertificate(): void
    {
        $result = Certificate::convertToCertificate(PEMCertificatesMock::getPlainPublicKeyContents());
        // the formatted public key in PEMCertificatesMock is stored with unix newlines
        $this->assertEquals(trim(PEMCertificatesMock::getPlainPublicKey()), $result);
    }


    /**
     */
    public function testParseIssuer(): void
    {
        // Test string input
        $result = Certificate::parseIssuer('test');
        $this->assertEquals('test', $result);

        // Test array input
        $result = Certificate::parseIssuer(
            [
                'C' => 'US',
                'S' => 'Hawaii',
                'L' => 'Honolulu',
                'O' => 'SimpleSAMLphp HQ',
                'CN' => 'SimpleSAMLphp Testing CA',
            ],
        );
        $this->assertEquals('CN=SimpleSAMLphp Testing CA,O=SimpleSAMLphp HQ,L=Honolulu,S=Hawaii,C=US', $result);
    }
}
