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

namespace auth_saml2\external;

use advanced_testcase;
use auth_saml2\external\manage_idp;
use dml_exception;
use invalid_parameter_exception;
use moodle_exception;

/**
 * Tests for SAML2
 *
 * @package    auth_saml2
 * @category   test
 * @copyright  2026 University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class manage_idp_test extends advanced_testcase {
    /**
     * Create a sample IdP record in auth_saml2_idps.
     *
     * @return \stdClass The created IdP record.
     * @throws dml_exception
     */
    private function create_test_idp(): \stdClass {
        global $DB;

        $this->resetAfterTest();

        $record = (object) [
            'metadataurl' => 'https://metadata.example.org/idps',
            'entityid'    => 'https://idp.example.org/idp/shibboleth',
            'defaultname' => 'Example.com test IDP',
            'displayname' => '',
            'activeidp'   => 0,
            'defaultidp'  => 0,
            'adminidp'    => 0,
            'whitelist'   => '',
        ];

        $record->id = $DB->insert_record('auth_saml2_idps', $record);

        return $record;
    }

    /**
     * Set up an admin user with the required capability.
     */
    private function set_admin_user_with_capability(): void {
        $admin = get_admin();
        $this->setUser($admin);
    }

    /**
     * Test updating a single field (displayname).
     */
    public function test_update_displayname(): void {
        global $DB;

        $idp = $this->create_test_idp();
        $this->set_admin_user_with_capability();

        $result = manage_idp::execute($idp->id, [
            [
                'field' => 'displayname',
                'value' => 'New Display Name',
            ],
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue((bool)$result['success']);

        $updated = $DB->get_record('auth_saml2_idps', ['id' => $idp->id], '*', MUST_EXIST);
        $this->assertSame('New Display Name', $updated->displayname);
    }

    /**
     * Test updating multiple boolean / string fields at once.
     */
    public function test_update_multiple_fields(): void {
        global $DB;

        $idp = $this->create_test_idp();
        $this->set_admin_user_with_capability();

        $fields = [
            ['field' => 'activeidp',   'value' => '1'],
            ['field' => 'defaultidp',  'value' => 1],
            ['field' => 'adminidp',    'value' => 'true'],
            ['field' => 'whitelist',   'value' => "user1@example.org\nuser2@example.org"],
        ];

        $result = manage_idp::execute($idp->id, $fields);

        $this->assertTrue((bool)$result['success']);

        $updated = $DB->get_record('auth_saml2_idps', ['id' => $idp->id], '*', MUST_EXIST);

        // Booleans are cast with (int)!empty($value).
        $this->assertSame(1, (int)$updated->activeidp);
        $this->assertSame(1, (int)$updated->defaultidp);
        $this->assertSame(1, (int)$updated->adminidp);
        $this->assertSame("user1@example.org\nuser2@example.org", $updated->whitelist);
    }

    /**
     * Test that whitespace in whitelist is trimmed.
     */
    public function test_whitelist_is_trimmed(): void {
        global $DB;

        $idp = $this->create_test_idp();
        $this->set_admin_user_with_capability();

        $result = manage_idp::execute($idp->id, [
            [
                'field' => 'whitelist',
                'value' => "   user@example.org  \n ",
            ],
        ]);

        $this->assertTrue((bool)$result['success']);

        $updated = $DB->get_record('auth_saml2_idps', ['id' => $idp->id], '*', MUST_EXIST);
        $this->assertSame('user@example.org', $updated->whitelist);
    }

    /**
     * Test that an invalid field name throws invalid_parameter_exception.
     */
    public function test_invalid_field_throws_exception(): void {
        $idp = $this->create_test_idp();
        $this->set_admin_user_with_capability();

        $this->expectException(invalid_parameter_exception::class);
        $this->expectExceptionMessage("The field 'invalidfield' is invalid for updating the IdP table");

        manage_idp::execute($idp->id, [
            [
                'field' => 'invalidfield',
                'value' => 'something',
            ],
        ]);
    }

    /**
     * Test that missing capability causes a moodle_exception (require_capability).
     */
    public function test_missing_capability_throws_exception(): void {
        global $DB;

        $idp = $this->create_test_idp();

        // Create and set a normal user without moodle/site:config.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);

        manage_idp::execute($idp->id, [
            [
                'field' => 'displayname',
                'value' => 'No Permission',
            ],
        ]);
    }

    /**
     * Test that a non‑existing IdP id results in MUST_EXIST error.
     */
    public function test_non_existing_idp_throws_dml_exception(): void {
        $this->resetAfterTest();
        $this->set_admin_user_with_capability();

        $this->expectException(dml_exception::class);

        // Use a ID that does not exist.
        manage_idp::execute(999999, [
            [
                'field' => 'displayname',
                'value' => 'Does not matter',
            ],
        ]);
    }
}
