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
 * auth_saml2 create/edit page unit tests
 *
 * @package    auth_saml2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_saml2\form\regenerate;

defined('MOODLE_INTERNAL') || die();

/**
 * auth_saml2 form submission unit tests
 *
 * @package    auth_saml2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_form_regenerate_testcase extends advanced_testcase {
    public function test_regenerate_certificate_form() {
        global $CFG, $DB, $USER;
        $this->resetAfterTest();

        // To test event is emitted to logstore table.
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');

        // Set a user as submitting this form.
        $this->setAdminUser();

        $this->mock_regenerate_form_post();

        // Get an instance of the form and check the data submitted.
        $regenerateform = new regenerate();
        self::assertFalse($regenerateform->is_cancelled());
        $formdata = $regenerateform->get_data();
        require_once($CFG->dirroot.'/auth/saml2/locallib.php');
        require_once($CFG->dirroot.'/auth/saml2/auth.php');
        require_once($CFG->dirroot.'/auth/saml2/setuplib.php');
        auth_saml2_process_regenerate_form($formdata);
        self::assertSame('AU', $formdata->countryname);
        self::assertSame('moodle', $formdata->stateorprovincename);
        self::assertSame('moodleville', $formdata->localityname);
        self::assertSame('moodle', $formdata->stateorprovincename);
        self::assertSame('vetmoodle', $formdata->organizationname);
        self::assertSame('moodle', $formdata->organizationalunitname);
        self::assertSame('moodle', $formdata->commonname);
        self::assertSame('noreply@moodle.test', $formdata->email);
        self::assertSame(3650, $formdata->expirydays);
        self::assertSame('Regenerate', $formdata->submitbutton);

        // Get log records and check for presence of the cert_regenerated event.
        $records = $DB->get_records_select('logstore_standard_log', "eventname = ?", ['\auth_saml2\event\cert_regenerated']);
        self::assertEquals(1, count($records));
        $logrecord = reset($records);
        $expecteddata = ['reason' => "regenerated in saml settings page", 'userid' => $USER->id];
        self::assertEquals(json_encode($expecteddata), $logrecord->other);
        self::assertEquals('\auth_saml2\event\cert_regenerated', $logrecord->eventname);
    }
    /**
     * Mock a post request
     */
    private function mock_regenerate_form_post() {
        $_POST = [
              'sesskey' => sesskey(),
              '_qf__auth_saml2_form_regenerate' => 1,
              'countryname' => 'AU',
              'stateorprovincename' => 'moodle',
              'localityname' => 'moodleville',
              'organizationname' => 'vetmoodle',
              'organizationalunitname' => 'moodle',
              'commonname' => 'moodle',
              'email' => 'noreply@moodle.test',
              'expirydays' => 3650,
              'submitbutton' => 'Regenerate',
        ];
    }
}
