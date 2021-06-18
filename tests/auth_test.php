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

namespace auth_saml2;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for auth class.
 *
 * @package     auth_saml2
 * @category    test
 * @group       auth_saml2
 * @covers      \auth_saml2\auth
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @copyright   2021 Moodle Pty Ltd <support@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_testcase extends \advanced_testcase {
    /**
     * Set up
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test test_is_configured
     */
    public function test_is_configured(): void {
        global $DB;

        // Add a fake IdP.
        $url = 'http://www.example.com';
        $recordid = $DB->insert_record('auth_saml2_idps', array(
            'metadataurl' => $url,
            'entityid'    => $url,
            'name'        => 'Test IdP',
            'activeidp'   => 1));

        /** @var auth_plugin_saml2 $auth */
        $auth = get_auth_plugin('saml2');

        $files = array(
            'crt' => $auth->certcrt,
            'pem' => $auth->certpem,
            'xml' => $auth->get_file(md5($url) . '.idp.xml'),
        );

        // Setup, remove the phpuunit dataroot temp files for saml2.
        foreach ($files as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        $this->assertFalse($auth->is_configured());

        // File crt: true.
        // File pem: false.
        // File xml: false.
        // File result: failure.
        touch($files['crt']);
        $this->assertFalse($auth->is_configured());

        // File crt: true.
        // File pem: true.
        // File xml: false.
        // File result: failure.
        touch($files['pem']);
        $this->assertFalse($auth->is_configured());

        // File crt: true.
        // File pem: true.
        // File xml: true.
        // File result: success.
        touch($files['xml']);
        $this->assertTrue($auth->is_configured());

        // Make IdP inactive.
        $DB->update_record('auth_saml2_idps', [
            'id' => $recordid,
            'activeidp' => 0,
        ]);
        $auth = get_auth_plugin('saml2');

        $this->assertFalse($auth->is_configured());
    }

    public function test_is_configured_works_with_multi_idp_in_one_xml() {
        global $DB;

        // Add two fake IdPs.
        $metadataurl = 'https://idp.example.org/idp/shibboleth';
        $DB->insert_record('auth_saml2_idps', array(
            'metadataurl' => $metadataurl,
            'entityid'    => 'https://idp1.example.org/idp/shibboleth',
            'name'        => 'Test IdP 1',
            'activeidp'   => 1));
        $DB->insert_record('auth_saml2_idps', array(
            'metadataurl' => $metadataurl,
            'entityid'    => 'https://idp2.example.org/idp/shibboleth',
            'name'        => 'Test IdP 2',
            'activeidp'   => 1));

        /** @var auth_plugin_saml2 $auth */
        $auth = get_auth_plugin('saml2');

        touch($auth->certcrt);
        touch($auth->certpem);

        $this->assertFalse($auth->is_configured());

        $xmlfile = md5("https://idp.example.org/idp/shibboleth");
        touch($auth->get_file("{$xmlfile}.idp.xml"));

        $this->assertTrue($auth->is_configured());
    }
}
