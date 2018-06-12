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

use auth_saml2\admin\saml2_settings;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../locallib.php');

/**
 * Tests for SAML
 *
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_locallib_testcase extends advanced_testcase {
    /**
     * Regression test for Issue 132.
     */
    public function test_it_can_initialise_more_than_once() {
        global $CFG;
        $this->resetAfterTest(true);

        for ($i = 0; $i < 3; $i++) {
            require($CFG->dirroot . '/auth/saml2/setup.php');
            $xml = auth_saml2_get_sp_metadata();
            self::assertNotNull($xml);
            self::resetAllData(false);
        }
    }

    public function test_auth_saml2_sp_metadata() {
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
        $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $contact = $xml->xpath('//md:EntityDescriptor/md:ContactPerson');
        $this->assertNotNull($contact);

    }

    /**
     * Test test_should_login_redirect
     *
     * @dataProvider should_login_redirect_testcases
     * @param mixed $duallogin
     * @param bool $param
     * @param bool $session
     * @param bool $expected The expected return value
     */
    public function test_should_login_redirect($duallogin, $param, $session, $expected) {
        global $SESSION;

        $this->resetAfterTest();

        if ($duallogin === 'passive') {
            $duallogin = saml2_settings::OPTION_DUAL_LOGIN_PASSIVE;
        } else {
            $duallogin = $duallogin ? saml2_settings::OPTION_DUAL_LOGIN_YES : saml2_settings::OPTION_DUAL_LOGIN_NO;
        }

        set_config('duallogin', $duallogin, 'auth_saml2');

        $SESSION->saml = $session;

        // HTML get param optional_param('saml', 0, PARAM_BOOL).
        if ($param !== null) {
            if ($param == 'error') {
                $_GET['SimpleSAML_Auth_State_exceptionId'] = '...';
            } else if ($param == 'post') {
                $_SERVER['REQUEST_METHOD'] = 'POST';
            } else {
                $_GET['saml'] = $param;
            }
        }

        /** @var auth_plugin_saml2 $auth */
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
            "1. dual: y, param: null, session: false" => [true, null, false, false],  // Login normal, dual login on.
            "2. dual: y, param: off, session: false"  => [true, 'off', false, false], // Login normal, dual login on.
            "3. dual: y, param: on, session: false"   => [true, 'on', false, true], // SAML redirect, ?saml=on.

            "4. dual: n, param: null, session: false" => [false, null, false, false],  // Login normal, $SESSION->saml=0.
            "5. dual: n, param: off, session: false"  => [false, 'off', false, false], // Login normal, ?saml=off.
            "6. dual: n, param: on, session: false"   => [false, 'on', false, true], // SAML redirect, ?saml=on.

            "7. dual: n, param: null, session: true"    => [false, null, true, true], // SAML redirect, $SESSION->saml=1.
            "8. dual: n, param: off, session: true"     => [false, 'off', true, false], // Login normal, ?saml=off.
            "9. dual: n, param: on, session: true"      => [false, 'on', true, true], // SAML redirect, ?saml=on.

            // For passive mode always redirect, SAML2 will redirect back if not logged in.
            "10. dual: p, param: null, session: true" => ['passive', null, true, true],
            "11. dual: p, param: off, session: true"  => ['passive', 'off', true, false], // Except if ?saml=off.
            "12. dual: p, param: on, session: true"   => ['passive', 'on', true, true],

            "13. dual: p, param: null, session: false" => ['passive', null, false, true],
            "14. dual: p, param: off, session: false"  => ['passive', 'off', false, false], // Except if ?saml=off.
            "15. dual: p, param: on, session: false"   => ['passive', 'on', false, true],

            "16. dual: p, with SAMLerror" => ['passive', 'error', false, false], // Passive redirect back.
            "17. dual: p using POST"      => ['passive', 'post', false, false], // POSTing.
        ];
    }

    /**
     * Test test_update_custom_user_profile_fields
     *
     * @dataProvider get_update_custom_user_profile_fields
     */
    public function test_update_custom_user_profile_fields($attributes) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $this->resetAfterTest();

        $auth = get_auth_plugin('saml2');

        $user = $this->getDataGenerator()->create_user();

        $fieldname = key($attributes);

        // Add a custom profile field named $fieldname.
        $pid = $DB->insert_record('user_info_field', array(
            'shortname'  => $fieldname,
            'name'       => 'Test Field',
            'categoryid' => 1,
            'datatype'   => 'text'));

        // Check both are returned using normal options.
        if (moodle_major_version() < '2.7.1') {
            $fields = auth_saml2_profile_get_custom_fields();
        } else {
            $fields = profile_get_custom_fields();
        }
        $this->assertArrayHasKey($pid, $fields);
        $this->assertEquals($fieldname, $fields[$pid]->shortname);

        // Is the key the same?
        $customprofilefields = $auth->get_custom_user_profile_fields();
        $key = 'profile_field_' . $fields[$pid]->shortname;
        $this->assertTrue(in_array($key, $customprofilefields));

        // Function print_auth_lock_options creates variables in the config object.
        set_config("field_map_$key", $fieldname, 'auth_saml2');
        set_config("field_updatelocal_$key", 'onlogin', 'auth_saml2');
        set_config("field_lock_$key", 'locked', 'auth_saml2');

        $update = $auth->update_user_profile_fields($user, $attributes);
        $this->assertTrue($update);
    }

    /**
     * Dataprovider for the test_update_custom_user_profile_fields testcase
     *
     * @return array of testcases
     */
    public function get_update_custom_user_profile_fields() {
        return array(
            array(['testfield' => array('Test data')]),
            array(['secondfield' => array('A different string')]),
        );
    }

    /**
     * Test test_missing_user_custom_profile_fields
     * The custom profile field does not exist, but IdP attribute data is mapped.
     *
     * @dataProvider get_missing_user_custom_profile_fields
     */
    public function test_missing_user_custom_profile_fields($attributes) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $this->resetAfterTest();

        $auth = get_auth_plugin('saml2');

        $user = $this->getDataGenerator()->create_user();

        $fieldname = key($attributes);

        if (moodle_major_version() < '2.7.1') {
            $fields = auth_saml2_profile_get_custom_fields();
        } else {
            $fields = profile_get_custom_fields();
        }

        $key = 'profile_field_' . $fieldname;
        $this->assertFalse(in_array($key, $fields));

        // Function print_auth_lock_options creates variables in the config object.
        set_config("field_map_$key", $fieldname, 'auth_saml2');
        set_config("field_updatelocal_$key", 'onlogin', 'auth_saml2');
        set_config("field_lock_$key", 'locked', 'auth_saml2');

        $update = $auth->update_user_profile_fields($user, $attributes);
        $this->assertTrue($update);
    }

    /**
     * Dataprovider for the test_missing_user_custom_profile_fields testcase
     *
     * @return array of testcases
     */
    public function get_missing_user_custom_profile_fields() {
        return array(
            array(['missingfield' => array('Test data')]),
            array(['secondfield' => array('A different string')]),
        );
    }

    /**
     * Test test_invalid_map_user_profile_fields
     *
     * @dataProvider get_invalid_map_user_profile_fields
     */
    public function test_invalid_map_user_profile_fields($mapping, $attributes) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $this->resetAfterTest();

        $auth = get_auth_plugin('saml2');

        $user = $this->getDataGenerator()->create_user();

        $field = $mapping['field'];
        $map = $mapping['mapping'];

        // Function print_auth_lock_options creates variables in the config object.
        set_config("field_map_$field", $map, 'auth_saml2');
        set_config("field_updatelocal_$field", 'onlogin', 'auth_saml2');
        set_config("field_lock_$field", 'locked', 'auth_saml2');

        $updateprofile = $auth->update_user_profile_fields($user, $attributes);
        $this->assertFalse($updateprofile);
    }

    /**
     * Dataprovider for the test_invalid_map_user_profile_fields testcase
     *
     * @return array of testcases
     */
    public function get_invalid_map_user_profile_fields() {
        return array(
            array(
                ['field' => 'userame', 'mapping' => 'invalid'],
                ['attributefield' => array('Test data')],
            ),
        );
    }

    /**
     * Test test_is_configured
     */
    public function test_is_configured() {
        $this->resetAfterTest();

        $url = 'http://www.example.com';
        set_config('idpentityids', json_encode([$url => $url]), 'auth_saml2');

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
    }

    public function test_is_configured_works_with_multi_idp_in_one_xml() {
        $this->resetAfterTest();

        $idpentityids = json_encode([
                                        'xml' => [
                                            'https://idp1.example.org/idp/shibboleth' => 0,
                                            'https://idp2.example.org/idp/shibboleth' => 0,
                                        ],
                                    ]);
        set_config('idpentityids', $idpentityids, 'auth_saml2');

        /** @var auth_plugin_saml2 $auth */
        $auth = get_auth_plugin('saml2');

        touch($auth->certcrt);
        touch($auth->certpem);

        $this->assertFalse($auth->is_configured());

        $xmlfile = md5("https://idp1.example.org/idp/shibboleth\nhttps://idp2.example.org/idp/shibboleth");
        touch($auth->get_file("{$xmlfile}.idp.xml"));

        $this->assertTrue($auth->is_configured());
    }
}

