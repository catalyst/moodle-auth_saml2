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
     * Get generator
     *
     * @return auth_saml2_generator
     */
    protected function get_generator(): \auth_saml2_generator {
        return $this->getDataGenerator()->get_plugin_generator('auth_saml2');
    }

    /**
     * Test test_is_configured
     */
    public function test_is_configured(): void {
        global $DB;
        // Add one IdP.
        $entity1 = $this->get_generator()->create_idp_entity([], false);

        $auth = get_auth_plugin('saml2');
        $files = array(
            'crt' => $auth->certcrt,
            'pem' => $auth->certpem,
            'xml' => $auth->get_file(md5($entity1->metadataurl) . '.idp.xml'),
        );

        // Sanity check.
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
            'id' => $entity1->id,
            'activeidp' => 0,
        ]);
        $auth = get_auth_plugin('saml2');

        $this->assertFalse($auth->is_configured());
    }

    public function test_is_configured_works_with_multi_idp_in_one_xml(): void {
        // Add two IdPs.
        $metadataurl = 'https://idp.example.org/idp/shibboleth';
        $this->get_generator()->create_idp_entity(['metadataurl' => $metadataurl], false);
        $this->get_generator()->create_idp_entity(['metadataurl' => $metadataurl], false);

        $auth = get_auth_plugin('saml2');
        touch($auth->certcrt);
        touch($auth->certpem);
        $this->assertFalse($auth->is_configured());

        // Create xml.
        touch($auth->get_file(md5($metadataurl). ".idp.xml"));
        $this->assertTrue($auth->is_configured());
    }

    public function test_class_constructor() {
        // Sanity check.
        $auth = get_auth_plugin('saml2');
        $this->assertFalse($auth->is_configured());
        $this->assertCount(0, $auth->metadataentities);

        // Create one entity.
        $entity1 = $this->get_generator()->create_idp_entity();
        $auth = get_auth_plugin('saml2');
        $this->assertTrue($auth->is_configured());
        $this->assertCount(1, $auth->metadataentities);

        // Name attribute is matching defaultname.
        $this->assertEquals($entity1->defaultname, reset($auth->metadataentities)->name);

        // Encoded entityid present as an attribute as well as the key.
        $this->assertArrayHasKey(md5($entity1->entityid), $auth->metadataentities);
        $this->assertEquals(md5($entity1->entityid), reset($auth->metadataentities)->md5entityid);

        // Multiidp flag is false.
        $reflector = new \ReflectionClass($auth);
        $property = $reflector->getParentClass()->getProperty('multiidp');
        $property->setAccessible(true);
        $this->assertFalse($property->getValue($auth));

        // DefaultIdP is not defined.
        $property = $reflector->getParentClass()->getProperty('defaultidp');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($auth));

        // Create non-active entity. Nothing should change.
        $preventities = $auth->metadataentities;
        $this->get_generator()->create_idp_entity(['activeidp' => 0]);
        $auth = get_auth_plugin('saml2');
        $this->assertCount(1, $auth->metadataentities);
        $this->assertEquals(reset($preventities), reset($auth->metadataentities));

        // Multiidp flag is false.
        $reflector = new \ReflectionClass($auth);
        $property = $reflector->getParentClass()->getProperty('multiidp');
        $property->setAccessible(true);
        $this->assertFalse($property->getValue($auth));

        // DefaultIdP is not defined.
        $property = $reflector->getParentClass()->getProperty('defaultidp');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($auth));

        // Create another entity with displayname and default flag set.
        $entity3 = $this->get_generator()->create_idp_entity(['displayname' => 'Login 1', 'defaultidp' => 1]);
        $auth = get_auth_plugin('saml2');
        $this->assertCount(2, $auth->metadataentities);

        // Check entity name.
        $this->assertEqualsCanonicalizing(['Login 1', $entity1->defaultname], array_column($auth->metadataentities, 'name'));

        // Encoded entityid present as an attribute as well as the key.
        $this->assertEqualsCanonicalizing([md5($entity1->entityid), md5($entity3->entityid)],
            array_column($auth->metadataentities, 'md5entityid'));
        $this->assertEqualsCanonicalizing([md5($entity1->entityid), md5($entity3->entityid)],
            array_keys($auth->metadataentities));

        // Multiidp flag is true.
        $reflector = new \ReflectionClass($auth);
        $property = $reflector->getParentClass()->getProperty('multiidp');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($auth));

        // DefaultIdP is defined and matching third entity.
        $property = $reflector->getParentClass()->getProperty('defaultidp');
        $property->setAccessible(true);
        $this->assertNotNull($property->getValue($auth));
        $this->assertEquals($auth->metadataentities[md5($entity3->entityid)], $property->getValue($auth));
    }

    public function test_loginpage_idp_list() {
        global $DB;

        // Add IdP entity.
        $entity1 = $this->get_generator()->create_idp_entity();

        // Single list item is expected.
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertCount(1, $list);

        // Inspect the plugin configured item name.
        $this->assertEquals(get_config('auth_saml2', 'idpname'), $list[0]['name']);

        // Inspect the item url.
        $url = $list[0]['url'];
        $this->assertInstanceOf(\moodle_url::class, $url);
        $this->assertEquals('/moodle/auth/saml2/login.php', $url->get_path());
        $this->assertEquals('/', $url->get_param('wants'));
        $this->assertEquals(md5($entity1->entityid), $url->get_param('idp'));
        $this->assertEquals('off', $url->get_param('passive'));

        // Wantsurl is pointing to auth/saml2/login.php
        $list = $auth->loginpage_idp_list('/auth/saml2/login.php');
        $url = $list[0]['url'];
        $this->assertInstanceOf(\moodle_url::class, $url);
        $this->assertEquals('/moodle/auth/saml2/login.php', $url->get_path());
        $this->assertNull($url->get_param('wants'));
        $this->assertNull($url->get_param('idp'));
        $this->assertEquals('off', $url->get_param('passive'));

        // Unset default name in config (used for overriding).
        set_config('idpname', '', 'auth_saml2');
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEquals($entity1->defaultname, $list[0]['name']);

        // Set metadata display name.
        $DB->update_record('auth_saml2_idps', [
            'id' => $entity1->id,
            'displayname' => 'Test',
        ]);
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEquals('Test', $list[0]['name']);

        // Unset metadata names, expect default.
        $DB->update_record('auth_saml2_idps', [
            'id' => $entity1->id,
            'displayname' => '',
            'defaultname' => '',
        ]);
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEquals($auth->config->idpdefaultname, $list[0]['name']);

        // Expect name in idpmetadata config to be used when no displayname
        // or defaultname are defined in entity.
        set_config('idpmetadata', 'Hello ' . $entity1->metadataurl, 'auth_saml2');
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEquals('Hello', $list[0]['name']);

        // Expect debug message if idpmetadata config does not match one stored in DB.
        set_config('idpmetadata', $entity1->metadataurl . 'modified', 'auth_saml2');
        $auth = get_auth_plugin('saml2');
        $auth->loginpage_idp_list('/');
        $this->assertDebuggingCalled();

        // Deactivate.
        $DB->update_record('auth_saml2_idps', [
            'id' => $entity1->id,
            'activeidp' => 0,
        ]);
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEmpty($list);
    }

    public function test_loginpage_idp_list_multiple() {
        global $DB;

        // Add two IdPs.
        $entity1 = $this->get_generator()->create_idp_entity(['displayname' => 'Login 1']);
        $entity2 = $this->get_generator()->create_idp_entity(['displayname' => 'Login 2']);

        // Two list items are expected.
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertCount(2, $list);

        // Unset default name in config (used for overriding).
        set_config('idpname', '', 'auth_saml2');
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEqualsCanonicalizing([$entity1->displayname, $entity2->displayname], array_column($list, 'name'));

        // Unset display name for first entity, it will be replaced by entity default name.
        $DB->update_record('auth_saml2_idps', [
            'id' => $entity1->id,
            'displayname' => '',
        ]);
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEqualsCanonicalizing([$entity1->defaultname, $entity2->displayname], array_column($list, 'name'));

        // Unset default name for first entity, it will be replaced by default with hostname mentioned.
        $DB->update_record('auth_saml2_idps', [
            'id' => $entity1->id,
            'defaultname' => '',
        ]);
        $idpname1 = get_string('idpnamedefault_varaible', 'auth_saml2', parse_url($entity1->entityid, PHP_URL_HOST));
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertEqualsCanonicalizing([$idpname1, $entity2->displayname], array_column($list, 'name'));

        // Deactivate first entity.
        $DB->update_record('auth_saml2_idps', [
            'id' => $entity1->id,
            'activeidp' => 0,
        ]);
        $auth = get_auth_plugin('saml2');
        $list = $auth->loginpage_idp_list('/');
        $this->assertCount(1, $list);
        $this->assertEquals($entity2->displayname, $list[0]['name']);
    }

    public function test_saml_login_complete_missing_idpattr() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('idpattr', 'blabla', 'auth_saml2');
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('noattribute', 'auth_saml2', 'blabla'), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_NOUSER, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_group_restriction() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
            'library' => ['overdue'],
        ];
        set_config('grouprules', 'deny library=overdue', 'auth_saml2');
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('flagmessage_default', 'auth_saml2'), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_UNAUTHORISED, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_email_taken() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('autocreate', '1', 'auth_saml2');
        set_config('field_map_email', 'email', 'auth_saml2');
        $this->getDataGenerator()->create_user(['email' => 'samluser1@example.com']);
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('emailtaken', 'auth_saml2', $attribs['email'][0]), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_FAILED, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_allowemailaddresses() {
        global $CFG;
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('autocreate', '1', 'auth_saml2');
        $CFG->allowemailaddresses = 'other.com';
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('flagmessage_default', 'auth_saml2'), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_FAILED, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_no_autocreate() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('autocreate', '0', 'auth_saml2');
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('nouser', 'auth_saml2', $attribs['uid'][0]), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_NOUSER, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_suspended() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        $this->getDataGenerator()->create_user(['username' => 'samlu1', 'suspended' => 1]);
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('suspendeduser', 'auth_saml2', $attribs['uid'][0]), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_SUSPENDED, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_wrong_auth() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('anyauth', '0', 'auth_saml2');
        $this->getDataGenerator()->create_user(['username' => 'samlu1', 'auth' => 'manual']);
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $this->assertStringContainsString(get_string('wrongauth', 'auth_saml2', $attribs['uid'][0]), $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_UNAUTHORISED, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_disabled_auth() {
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('anyauth', '1', 'auth_saml2');
        $this->getDataGenerator()->create_user(['username' => 'samlu1', 'auth' => 'shibboleth']);
        $auth = $this->get_mocked_auth();

        $sink = $this->redirectEvents();
        try {
            $auth->saml_login_complete($attribs);
            $this->fail('Exception expected');
        } catch (\coding_exception $e) {
            // Validate reason.
            $msg = get_string('anyauthotherdisabled', 'auth_saml2', [
                'username' => $attribs['uid'][0], 'auth' => 'shibboleth',
            ]);
            $this->assertStringContainsString($msg, $e->getMessage());
        }

        // Checking that the event contains the expected values.
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assertEquals(AUTH_LOGIN_UNAUTHORISED, $event->get_data()['other']['reason']);
    }

    public function test_saml_login_complete_new_account() {
        global $USER;
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('autocreate', '1', 'auth_saml2');
        set_config('field_map_email', 'email', 'auth_saml2');

        // Sanity check.
        $this->assertFalse(isloggedin());

        $sink = $this->redirectEvents();

        // Try to login, suppress output.
        $auth = new \auth_saml2\auth();
        @$auth->saml_login_complete($attribs);

        // Check global object.
        $this->assertEquals($attribs['uid'][0], $USER->username);
        $this->assertEquals($attribs['email'][0], $USER->email);

        // Checking that the events contain the expected values.
        $events = $sink->get_events();
        $this->assertCount(4, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_loggedin', $event);
        $this->assertEquals($USER->id, $event->get_data()['objectid']);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_updated', $event);
        $this->assertEquals($USER->id, $event->get_data()['objectid']);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_created', $event);
        $this->assertEquals($USER->id, $event->get_data()['objectid']);
    }

    public function test_saml_login_complete_existing_account() {
        global $USER;
        $attribs = [
            'uid' => ['samlu1'],
            'email' => ['samluser1@example.com'],
        ];
        set_config('field_map_email', 'email', 'auth_saml2');
        set_config('field_updatelocal_email', 'onlogin', 'auth_saml2');
        $user = $this->getDataGenerator()->create_user(['username' => 'samlu1', 'auth' => 'saml2']);

        // Sanity check.
        $this->assertFalse(isloggedin());
        $this->assertNotEquals($attribs['email'][0], $user->email);

        $sink = $this->redirectEvents();

        // Try to login, suppress output.
        $auth = new \auth_saml2\auth();
        @$auth->saml_login_complete($attribs);

        // Check global object, make sure email was updated.
        $this->assertEquals($attribs['uid'][0], $USER->username);
        $this->assertEquals($attribs['email'][0], $USER->email);

        // Checking that the events contain the expected values.
        $events = $sink->get_events();
        $this->assertCount(2, $events);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_loggedin', $event);
        $this->assertEquals($USER->id, $event->get_data()['objectid']);
        $event = array_pop($events);
        $this->assertInstanceOf('\core\event\user_updated', $event);
        $this->assertEquals($USER->id, $event->get_data()['objectid']);
    }

    /**
     * Retrieve mocked auth instance.
     *
     * @return \auth_saml2\auth
     */
    protected function get_mocked_auth(): \auth_saml2\auth {
        // Setup mock, make error_page throw exception containing argument as
        // exception message. This is needed to check $msg argument and stop
        // execution like original method does.
        $auth = $this->getMockBuilder(\auth_saml2\auth::class)
            ->setMethods(['error_page'])->getMock();

        $auth->expects($this->once())
            ->method('error_page')
            ->will($this->returnCallback(function($msg) {
                throw new \coding_exception($msg);
            }));
        return $auth;
    }
}
