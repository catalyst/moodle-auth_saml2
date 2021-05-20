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
use auth_saml2\admin\setting_idpmetadata;

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
        global $CFG, $DB;
        $this->resetAfterTest(true);

        // Add a fake IdP.
        $DB->insert_record('auth_saml2_idps', array(
            'metadataurl' => 'http://www.example.com',
            'entityid'    => 'http://www.example.com',
            'name'        => 'Test IdP',
            'activeidp'   => 1));

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
     * @param array $cfg core config
     * @param array $config plugin config
     * @param bool $param
     * @param bool $multiidp
     * @param bool $session
     * @param bool $expected The expected return value
     */
    public function test_should_login_redirect($cfg, $config, $param, $multiidp, $session, $expected) {
        global $SESSION;

        $this->resetAfterTest();

        foreach ($config as $key => $value) {
            set_config($key, $value, 'auth_saml2');
        }

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

        // HTML get param optional_param('multiidp', 0, PARAM_BOOL).
        if ($multiidp === true) {
            $_GET['multiidp'] = true;
        }

        /** @var auth_plugin_saml2 $auth */
        $auth = get_auth_plugin('saml2');
        $result = $auth->should_login_redirect();

        $this->assertEquals($expected, $result);

        unset($_GET['saml']);
        unset($SESSION->saml);
    }

    /**
     * Dataprovider for the test_should_login_redirect testcase
     *
     * @return array of testcases
     */
    public function should_login_redirect_testcases() {
        $midp = (new moodle_url('/auth/saml2/selectidp.php'))->out();
        return [
            // Login normal, dual login on.
            "1. dual: y, param: null, multiidp: false, session: false" => [
                [],
                ['duallogin' => true],
                null, false, false,
                false],

            // Login normal, dual login on.
            "2. dual: y, param: off, multiidp: false, session: false" => [
                [],
                ['duallogin' => true],
                'off', false, false,
                false],

            // SAML redirect, ?saml=on.
            "3. dual: y, param: on, multiidp: false, session: false" => [
                [],
                ['duallogin' => true],
                'on', false, false,
                true],

            // Login normal, $SESSION->saml=0.
            "4. dual: n, param: null, multiidp: false, session: false" => [
                [],
                ['duallogin' => false],
                null, false, false,
                false],

            // Login normal, ?saml=off.
            "5. dual: n, param: off, multiidp: false, session: false" => [
                [],
                ['duallogin' => false],
                'off', false, false,
                false],

            // SAML redirect, ?saml=on.
            "6. dual: n, param: on, multiidp: false, session: false" => [
                [],
                ['duallogin' => false],
                'on', false, false,
                true],

            // SAML redirect, $SESSION->saml=1.
            "7. dual: n, param: null, multiidp: false, session: true" => [
                [],
                ['duallogin' => false],
                null, false, true,
                true],

            // Login normal, ?saml=off.
            "8. dual: n, param: off, multiidp: false, session: true" => [
                [],
                ['duallogin' => false],
                'off', false, true,
                false],

            // SAML redirect, ?saml=on.
            "9. dual: n, param: on, multiidp: false, session: true" => [
                [],
                ['duallogin' => false],
                'on', false, true,
                true],

            // For passive mode always redirect, SAML2 will redirect back if not logged in.
            "10. dual: p, param: null, multiidp: false, session: true" => [
                [],
                ['duallogin' => 'passive'],
                null, false, true,
                true],

            // Except if ?saml=off.
            "11. dual: p, param: off, multiidp: false, session: true" => [
                [],
                ['duallogin' => 'passive'],
                'off', false, true,
                false],

            "12. dual: p, param: on, multiidp: false, session: true" => [
                [],
                ['duallogin' => 'passive'],
                'on', false, true,
                true],

            // Except if ?saml=off.
            "14. dual: p, param: off, multiidp: false, session: false" => [
                [],
                ['duallogin' => 'passive'],
                'off', false, false,
                false],

            "15. dual: p, param: on, multiidp: false, session: false" => [
                [],
                ['duallogin' => 'passive'],
                'on', false, false,
                true],

            // Passive redirect back.
            "16. dual: p, with SAMLerror" => [
                [],
                ['duallogin' => 'passive'],
                'error', false, false,
                false],

            // POSTing.
            "17. dual: p using POST" => [
                [],
                ['duallogin' => 'passive'],
                'post', false, false,
                false],

            // Param multi-idp.
            // Login normal, dual login on. Multi IdP true.
            "18. dual: y, param: null, multiidp: true, session: false" => [
                [],
                ['duallogin' => true],
                null, true, false,
                $midp],

            // Login normal, dual login on. Multi IdP true.
            "19. dual: y, param: off, multiidp: true, session: false" => [
                [],
                ['duallogin' => true],
                'off', true, false,
                false],

            // SAML redirect, ?saml=on. Multi IdP true.
            "20. dual: y, param: on, multiidp: true, session: false" => [
                [],
                ['duallogin' => true],
                'on', true, false,
                $midp],
        ];
    }

    /**
     * Test test_should_login_redirect
     *
     * @dataProvider check_whitelisted_ip_redirect_testcases
     * @param string $whitelist
     * @param bool $expected The expected return value
     */
    public function test_check_whitelisted_ip_redirect($saml, $remoteip, $active, $whitelist, $expected) {
        $this->resetAfterTest();

        // Setting an address here as getremoteaddr() will return default 0.0.0.0 which then is ignored by the address_in_subnet
        // function.
        $_SERVER['REMOTE_ADDR'] = $remoteip;

        /** @var auth_plugin_saml2 $auth */
        $auth = get_auth_plugin('saml2');

        $auth->metadataentities = [
            md5('idp') => [
                'entity' => (object)[
                        'whitelist' => $whitelist,
                        'activeidp' => $active
                ]
            ]
        ];

        if ($saml !== null) {
            $_GET['saml'] = $saml;
        }

        $result = $auth->should_login_redirect();
        $this->assertTrue($result === $expected);
    }

    /**
     * Dataprovider for the test_check_whitelisted_ip_redirect testcase
     *
     * @return array of testcases
     */
    public function check_whitelisted_ip_redirect_testcases() {
        return [
            'saml off, no ip, active idp, no redirect'              => ['off', '1.2.3.4', true, '', false],
            'saml not specified, active idp, junk, no redirect'     => [null, '1.2.3.4', true, 'qwer1234!@#qwer', false],
            'saml not specified, active idp, junk+ip, yes redirect' => [null, '1.2.3.4', true, "qwer1234!@#qwer\n1.2.3.4", true],
            'saml not specified, active idp, localip, yes redirect' => [null, '1.2.3.4', true, "127.0.0.\n1.", true],
            'saml not specified, disabled idp, localip, no redirect' => [null, '1.2.3.4', false, "127.0.0.\n1.", false],
            'saml not specified, active idp, wrongip, no redirect' => [null, '4.3.2.1', true, "127.0.0.\n1.", false],
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
        global $DB;

        $this->resetAfterTest();

        // Add a fake IdP.
        $url = 'http://www.example.com';
        $DB->insert_record('auth_saml2_idps', array(
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
    }

    public function test_is_configured_works_with_multi_idp_in_one_xml() {
        global $DB;

        $this->resetAfterTest();

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

    public function test_get_email_from_attributes() {
        $this->resetAfterTest();

        $auth = get_auth_plugin('saml2');
        $this->assertFalse($auth->get_email_from_attributes([]));
        $this->assertFalse($auth->get_email_from_attributes(['email' => ['test@test.com']]));

        set_config('field_map_email', 'test', 'auth_saml2');
        $auth = get_auth_plugin('saml2');

        $this->assertFalse($auth->get_email_from_attributes(['email' => ['test@test.com']]));

        set_config('field_map_email', 'email', 'auth_saml2');
        $auth = get_auth_plugin('saml2');
        $this->assertEquals('test@test.com', $auth->get_email_from_attributes(['email' => ['test@test.com']]));

        set_config('field_map_email', 'email', 'auth_saml2');
        $auth = get_auth_plugin('saml2');
        $this->assertEquals('test@test.com', $auth->get_email_from_attributes(['email' => ['test@test.com', 'test2@test.com']]));
    }

    public function test_is_email_taken() {
        $this->resetAfterTest();

        $auth = get_auth_plugin('saml2');
        $user = $this->getDataGenerator()->create_user();

        $this->assertFalse($auth->is_email_taken(''));
        $this->assertFalse($auth->is_email_taken('', $user->username));

        $this->assertTrue($auth->is_email_taken($user->email));
        $this->assertTrue($auth->is_email_taken(strtoupper($user->email)));
        $this->assertTrue($auth->is_email_taken(ucfirst($user->email)));
        $this->assertFalse($auth->is_email_taken($user->email, $user->username));
        $this->assertFalse($auth->is_email_taken(strtoupper($user->email), $user->username));
        $this->assertFalse($auth->is_email_taken(ucfirst($user->email), $user->username));

        // Create a new user with the same email, but different mnethostid.
        $user2 = $this->getDataGenerator()->create_user(['email' => $user->email, 'mnethostid' => 777]);

        // Delete original user.
        delete_user($user);
        $this->assertFalse($auth->is_email_taken($user->email));
        $this->assertFalse($auth->is_email_taken(strtoupper($user->email)));
        $this->assertFalse($auth->is_email_taken(ucfirst($user->email)));
        $this->assertFalse($auth->is_email_taken($user->email, $user->username));
        $this->assertFalse($auth->is_email_taken(strtoupper($user->email), $user->username));
        $this->assertFalse($auth->is_email_taken(ucfirst($user->email), $user->username));
    }

    /**
     * If locked do not generate the cert, if unlocked then generate the cert.
     */
    public function test_setup_no_cert_generate_if_locked() {
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
        require(dirname(__FILE__) . '/../../setup.php');
        $this->assertDebuggingCalled();
        $crt = file_exists($auth->certcrt);
        $this->assertFalse($crt);

        // Set config unlocked.
        set_config('certs_locked', 0, 'auth_saml2');

        // Call setup.php and see that it generates the certificate.
        require(dirname(__FILE__) . '/../../setup.php');
        $crt = file_exists($auth->certcrt);
        $this->assertTrue($crt);
    }

    /**
     * If locked and we try to generate certs, throw an exception and do not generate the certs.
     */
    public function test_create_certificates_if_locked() {
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
     * Data provided with the test attributes for is_access_allowed_for_member method.
     * @return array
     */
    public function is_access_allowed_data_provider() {
        return [
            '' => [[
                ['uid' => 'test'], // User don't have groups attribute.
                ['uid' => 'test', 'groups' => ['blocked']], // In blocked group.
                ['uid' => 'test', 'groups' => ['allowed']],  // In allowed group.
                ['uid' => 'test', 'groups' => ['allowed', 'blocked']], // In both allowed first.
                ['uid' => 'test', 'groups' => ['blocked', 'allowed']], // In both blocked first.
                ['uid' => 'test', 'groups' => []],  // Groups exists, but empty.
            ]]
        ];
    }

    /**
     * Test access allowed if required attributes are not configured.
     *
     * @dataProvider is_access_allowed_data_provider
     * @param $attributes
     */
    public function test_is_access_allowed_for_member_not_configured($attributes) {
        $this->resetAfterTest();

        set_config('idpattr', 'uid', 'auth_saml2');

        // User don't have groups attribute.
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[0]));

        // In blocked group.
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[1]));

        // In allowed group.
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[2]));

        // In both allowed first.
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[3]));

        // In both blocked first.
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[4]));

        // Groups exists, but empty.
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[5]));
    }

    /**
     * Test access allowed if configured, but restricted groups attribute is set to empty.
     *
     * @dataProvider is_access_allowed_data_provider
     * @param $attributes
     */
    public function test_is_access_allowed_for_member_blocked_empty($attributes) {
        $this->resetAfterTest();

        set_config('idpattr', 'uid', 'auth_saml2');
        set_config('grouprules', 'allow groups=allowed', 'auth_saml2');

        $auth = get_auth_plugin('saml2');

        // User don't have groups attribute.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[0]));

        // In blocked group.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[1]));

        // In allowed group.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[2]));

        // In both allowed first.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[3]));

        // In both blocked first.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[4]));

        // Groups exist, but empty.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[5]));
    }

    /**
     * Test access allowed if configured, but allowed groups attribute is set to empty.
     *
     * @dataProvider is_access_allowed_data_provider
     * @param $attributes
     */
    public function test_is_access_allowed_for_member_allowed_empty($attributes) {
        $this->resetAfterTest();

        set_config('idpattr', 'uid', 'auth_saml2');
        set_config('grouprules', 'deny groups=blocked', 'auth_saml2');
        $auth = get_auth_plugin('saml2');

        // User don't have groups attribute.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[0]));

        // In blocked group.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[1]));

        // In allowed group.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[2]));

        // In both allowed first.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[3]));

        // In both blocked first.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[4]));

        // Groups exist, but empty.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[5]));
    }

    /**
     * Test access allowed if fully configured.
     *
     * @dataProvider is_access_allowed_data_provider
     * @param $attributes
     */
    public function test_is_access_allowed_for_member_allowed_and_blocked($attributes) {
        $this->resetAfterTest();

        set_config('idpattr', 'uid', 'auth_saml2');
        set_config('grouprules', "deny groups=blocked\nallow groups=allowed", 'auth_saml2');

        $auth = get_auth_plugin('saml2');

        // User don't have groups attribute.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[0]));

        // In blocked group.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[1]));

        // In allowed group.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[2]));

        // In both allowed first.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[3]));

        // In both blocked first.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[4]));

        // Groups exist, but empty.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[5]));
    }

    /**
     * Test access allowed if fully configured and allowed priority is set to yes.
     *
     * @dataProvider is_access_allowed_data_provider
     * @param $attributes
     */
    public function test_is_access_allowed_for_member_allowed_and_blocked_with_allowed_priority($attributes) {
        $this->resetAfterTest();

        set_config('idpattr', 'uid', 'auth_saml2');
        set_config('grouprules', "allow groups=allowed\ndeny groups=blocked", 'auth_saml2');

        $auth = get_auth_plugin('saml2');

        // User don't have groups attribute.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[0]));

        // In blocked group.
        $this->assertFalse($auth->is_access_allowed_for_member($attributes[1]));

        // In allowed group.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[2]));

        // In both allowed first.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[3]));

        // In both blocked first.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[4]));

        // Groups exist, but empty.
        $this->assertTrue($auth->is_access_allowed_for_member($attributes[5]));
    }
}
