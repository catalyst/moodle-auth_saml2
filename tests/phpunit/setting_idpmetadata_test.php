<?php
// This file is part of SAML2 Authentication Plugin
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

/**
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_saml2\admin\setting_idpmetadata;
use auth_saml2\idp_data;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../_autoload.php');

class setting_idpmetadata_test extends advanced_testcase {
    /** @var setting_idpmetadata */
    private $config;

    protected function setUp() {
        parent::setUp();
        $this->config = new setting_idpmetadata();
    }

    private function get_test_metadata_url() {
        if (!defined('AUTH_SAML2_TEST_IDP_METADATA')) {
            $this->markTestSkipped();
        }
        return AUTH_SAML2_TEST_IDP_METADATA;
    }

    public function test_it_validates_the_xml() {
        $this->resetAfterTest();
        $xml = file_get_contents(__DIR__ . '/../fixtures/metadata.xml');
        $data = $this->config->validate($xml);
        self::assertTrue($data);
    }

    public function test_it_saves_all_idp_information() {
        global $CFG;

        $this->resetAfterTest();

        $xml = file_get_contents(__DIR__ . '/../fixtures/metadata.xml');
        $this->config->write_setting($xml);
        $actual = get_config('auth_saml2');

        self::assertSame($xml, $actual->idpmetadata, 'Invalid config metadata.');
        self::assertSame('{"xml":"https:\/\/idp.example.org\/idp\/shibboleth"}', $actual->idpentityids);
        self::assertSame('{"xml":"Example.com test IDP"}', $actual->idpmduinames);

        $file = md5('https://idp.example.org/idp/shibboleth') . '.idp.xml';
        $file = "{$CFG->dataroot}/saml2/{$file}";
        self::assertFileExists($file);
        $actual = file_get_contents($file);
        self::assertSame(trim($xml), $actual, "Invalid saved XML contents for: {$file}");
    }

    public function test_it_saves_all_idps_information_from_single_xml() {
        global $CFG;

        $this->resetAfterTest();

        $xml = file_get_contents(__DIR__ . '/../fixtures/dualmetadata.xml');
        $this->config->write_setting($xml);
        $actual = get_config('auth_saml2');

        self::assertSame($xml, $actual->idpmetadata, 'Invalid config metadata.');
        $expected = json_encode([
                                    'xml' => [
                                        'https://idp1.example.org/idp/shibboleth' => 0,
                                        'https://idp2.example.org/idp/shibboleth' => 0,
                                    ],
                                ]);
        self::assertSame($expected, $actual->idpentityids);
        $expected = json_encode([
                                    'xml' => [
                                        'https://idp1.example.org/idp/shibboleth' => 'First Test IDP',
                                        'https://idp2.example.org/idp/shibboleth' => 'Second Test IDP',
                                    ],
                                ]);
        self::assertSame($expected, $actual->idpmduinames);

        $file = md5("https://idp1.example.org/idp/shibboleth\nhttps://idp2.example.org/idp/shibboleth") . '.idp.xml';
        $file = "{$CFG->dataroot}/saml2/{$file}";
        self::assertFileExists($file);
        $actual = file_get_contents($file);
        self::assertSame(trim($xml), $actual, "Invalid saved XML contents for: {$file}");
    }

    public function test_it_allows_empty_values() {
        self::assertTrue($this->config->validate(''), 'Validate empty string.');
        self::assertTrue($this->config->validate('  '), ' Should trim spaces.');
        self::assertTrue($this->config->validate("\n \n"), 'Should trim newlines.');
    }

    public function test_it_gets_idp_data_for_xml() {
        $xml = file_get_contents(__DIR__ . '/../fixtures/metadata.xml');
        $data = $this->config->get_idps_data($xml);
        self::assertCount(1, $data);
        $this->validate_idp_data_array($data);
    }

    public function test_it_gets_idp_data_for_two_urls() {
        $url = $this->get_test_metadata_url();
        $url = "{$url}\n{$url}?second";
        $data = $this->config->get_idps_data($url);
        self::assertCount(2, $data);
        $this->validate_idp_data_array($data);
    }

    public function test_it_returns_error_if_metadata_url_is_not_valid() {
        $error = $this->config->validate('http://invalid.url.metadata.test');
        self::assertContains('Invalid metadata', $error);
        self::assertContains('http://invalid.url.metadata.test', $error);
    }

    /**
     * @param idp_data[] $idps
     */
    private function validate_idp_data_array($idps) {
        foreach ($idps as $idp) {
            self::assertInstanceOf(idp_data::class, $idp);
            self::assertNotNull($idp->get_rawxml());
        }
    }
}
