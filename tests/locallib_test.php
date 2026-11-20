<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace auth_saml2;

use SimpleXMLElement;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../locallib.php');
require_once(__DIR__ . '/psr17_factory_pin.php');

/**
 * Tests for SAML
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends \advanced_testcase {
    /**
     * Regression test for Issue 132.
     */
    public function test_it_can_initialise_more_than_once(): void {
        global $CFG, $DB;
        $this->resetAfterTest(true);

        // Add a fake IdP.
        $DB->insert_record('auth_saml2_idps', [
            'metadataurl' => 'http://www.example.com',
            'entityid'    => 'http://www.example.com',
            'name'        => 'Test IdP',
            'activeidp'   => 1]);

        for ($i = 0; $i < 3; $i++) {
            require($CFG->dirroot . '/auth/saml2/setup.php');
            $xml = auth_saml2_get_sp_metadata();
            self::assertNotNull($xml);
            self::resetAllData(false);
        }
    }

    public function test_auth_saml2_sp_metadata(): void {
        global $CFG;

        $this->resetAfterTest();

        // Set just enough config to generate SP metadata.
        $email = 'test@test.com';
        $url = 'http://www.example.com';
        set_config('supportemail', $email);
        set_config('idpmetadata', $url, 'auth_saml2');
        set_config('idpentityids', json_encode([$url => $url]), 'auth_saml2');

        require($CFG->dirroot . '/auth/saml2/setup.php');

        $rawxml = auth_saml2_get_sp_metadata();

        $xml = new SimpleXMLElement($rawxml);
        $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $contact = $xml->xpath('//md:EntityDescriptor/md:ContactPerson');
        $this->assertNotNull($contact);
    }

    /**
     * If locked do not generate the cert, if unlocked then generate the cert.
     */
    public function test_setup_no_cert_generate_if_locked(): void {
        $this->resetAfterTest();
        $auth = get_auth_plugin('saml2');
        set_config('certs_locked', 1, 'auth_saml2');

        // Make sure we have no files.
        $crt = file_exists($auth->certcrt);
        if ($crt) {
            unlink($auth->certcrt);
        }
        $this->assertFalse($crt);

        // Call setup.php and see that it doesn't generate a cert.
        require(dirname(__FILE__) . '/../setup.php');
        $this->assertDebuggingCalled();
        $crt = file_exists($auth->certcrt);
        $this->assertFalse($crt);

        // Set config unlocked.
        set_config('certs_locked', 0, 'auth_saml2');

        // Call setup.php and see that it generates the certificate.
        require(dirname(__FILE__) . '/../setup.php');
        $crt = file_exists($auth->certcrt);
        $this->assertTrue($crt);
    }

    /**
     * If locked and we try to generate certs, throw an exception and do not generate the certs.
     */
    public function test_create_certificates_if_locked(): void {
        $this->resetAfterTest();
        $auth = get_auth_plugin('saml2');
        set_config('certs_locked', 1, 'auth_saml2');

        // Call the create_certificates function directly to assert that
        // it throws an exception and does not generate a cert.
        try {
            create_certificates($auth);
            // Fail if the exception is not thrown.
            $this->fail();
        } catch (\saml2_exception $e) {
            $this->assertFalse(file_exists($auth->certcrt));
        }
    }

    /**
     * Test SAML request ID validation.
     *
     * @dataProvider provider_is_valid_saml_id
     * @param string $id
     * @param bool $expected
     */
    public function test_auth_saml2_is_valid_saml_id(string $id, bool $expected): void {
        $this->assertSame($expected, auth_saml2_is_valid_saml_id($id));
    }

    /**
     * Data provider for {@see self::test_auth_saml2_is_valid_saml_id}.
     *
     * @return array
     */
    public static function provider_is_valid_saml_id(): array {
        return [
            'typical request id' => ['_8e8dc5f69a98cc4c1ff3427e5ce34606fd672f91e6', true],
            'letters, digits, dot and dash' => ['a1.b-2_c', true],
            'non ascii letters' => ['_café', true],
            'very long' => ['_' . str_repeat('a', 1000), true],
            'empty' => ['', false],
            'starts with digit' => ['1abc', false],
            'contains colon' => ['a:b', false],
            'trailing newline' => ["_abc\n", false],
            'attribute breakout' => ['_a" Destination="https://evil.example', false],
            'markup' => ['_a><b', false],
            'space' => ['_a b', false],
            'invalid utf8' => ["_a\xff", false],
        ];
    }

    /**
     * Test parsing of the IdP service provider list.
     */
    public function test_auth_saml2_parse_idp_splist(): void {
        $splist = "https://sp1.example/metadata https://sp1.example/acs  https://sp1.example/acs2\r\n"
            . "\r\n"
            . "  https://sp2.example/metadata  \n"
            . "https://sp1.example/metadata https://sp1.example/acs https://sp1.example/acs3\n";

        $this->assertSame([
            'https://sp1.example/metadata' => [
                'https://sp1.example/acs',
                'https://sp1.example/acs2',
                'https://sp1.example/acs3',
            ],
            'https://sp2.example/metadata' => [],
        ], auth_saml2_parse_idp_splist($splist));

        $this->assertSame([], auth_saml2_parse_idp_splist(''));
    }

    /**
     * Test that only exactly registered http(s) ACS URLs are accepted.
     *
     * @dataProvider provider_is_registered_acs
     * @param array $registered
     * @param string $acs
     * @param bool $expected
     */
    public function test_auth_saml2_is_registered_acs(array $registered, string $acs, bool $expected): void {
        $this->assertSame($expected, auth_saml2_is_registered_acs($registered, $acs));
    }

    /**
     * Data provider for {@see self::test_auth_saml2_is_registered_acs}.
     *
     * @return array
     */
    public static function provider_is_registered_acs(): array {
        $registered = ['https://sp.example/acs', 'https://sp.example/other'];
        return [
            'first registered' => [$registered, 'https://sp.example/acs', true],
            'second registered' => [$registered, 'https://sp.example/other', true],
            'attacker host' => [$registered, 'https://attacker.example/collect', false],
            'different path on same host' => [$registered, 'https://sp.example/anything', false],
            'prefix of registered' => [$registered, 'https://sp.example/acs/extra', false],
            'different case' => [$registered, 'https://SP.example/acs', false],
            'different scheme' => [$registered, 'http://sp.example/acs', false],
            'userinfo host trick' => [$registered, 'https://sp.example@attacker.example/acs', false],
            'backslash trick' => [$registered, 'https://attacker.example\\@sp.example/acs', false],
            'javascript scheme' => [['javascript://sp.example/%0Aalert(1)'], 'javascript://sp.example/%0Aalert(1)', false],
            'empty request value' => [$registered, '', false],
            'nothing registered' => [[], 'https://sp.example/acs', false],
        ];
    }
}
