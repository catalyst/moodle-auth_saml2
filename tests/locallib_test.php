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

/**
 * SAML2 SP metadata tests.
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for SAML
 *
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_locallib_testcase extends advanced_testcase {

    public function test_auth_saml2_sp_metadata() {
        global $CFG, $DB, $saml2auth;

        $this->resetAfterTest();

        // Set just enough config to generate SP metadata.
        $email = 'test@test.com';
        set_config('supportemail', $email);

        require_once($CFG->dirroot . '/auth/saml2/setup.php');
        require_once($CFG->dirroot . '/auth/saml2/locallib.php');

        $auth = get_auth_plugin('saml2');

        $rawxml = auth_saml2_get_sp_metadata();

        $xml = new SimpleXMLElement($rawxml);
        $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $contact = $xml->xpath('//md:EntityDescriptor/md:ContactPerson');
        $this->assertNotNull($contact);

    }

    /**
     * Test test_should_login_redirect
     *
     * @dataProvider should_login_redirect_testcases
     * @param bool $duallogin
     * @param bool $param
     * @param bool $session
     * @param bool $expected The expected return value
     */
    public function test_should_login_redirect($duallogin, $param, $session, $expected) {
        global $SESSION;

        $this->resetAfterTest();

        set_config('duallogin', $duallogin, 'auth/saml2');

        $SESSION->saml = $session;

        // HTML get param optional_param('saml', 0, PARAM_BOOL).
        if ($param !== null) {
            $_GET['saml'] = $param;
        }

        $auth = get_auth_plugin('saml2');
        $result = $auth->should_login_redirect();

        $this->assertTrue($result === $expected);

        unset($_GET['saml']);
        unset($SESSION->saml);
    }

    /**
     * Dataprovider for the test_should_login_redirect testcase
     *
     * @return array of testcases
     */
    public function should_login_redirect_testcases() {
        return [
            "1. DUALcfg: true, SAMLparam: null, SAMLsession: false" => [true, null, false, false],  // Login normal, dual login on.
            "2. DUALcfg: true, SAMLparam: off, SAMLsession: false"  => [true, 'off', false, false], // Login normal, dual login on.
            "3. DUALcfg: true, SAMLparam: on, SAMLsession: false"   => [true, 'on', false, true], // SAML redirect, ?saml=on.

            "4. DUALcfg: false, SAMLparam: null, SAMLsession: false" => [false, null, false, false],  // Login normal, $SESSION->saml=0.
            "5. DUALcfg: false, SAMLparam: off, SAMLsession: false"  => [false, 'off', false, false], // Login normal, ?saml=off.
            "6. DUALcfg: false, SAMLparam: on, SAMLsession: false"   => [false, 'on', false, true], // SAML redirect, ?saml=on.

            "7. DUALcfg: false, SAMLparam: null, SAMLsession: true" => [false, null, true, true], // SAML redirect, $SESSION->saml=1.
            "8. DUALcfg: false, SAMLparam: off, SAMLsession: true"  => [false, 'off', true, false], // Login normal, ?saml=off.
            "9. DUALcfg: false, SAMLparam: on, SAMLsession: true"   => [false, 'on', true, true], // SAML redirect, ?saml=on.
        ];
    }

}

