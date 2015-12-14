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
 *
 * @SuppressWarnings(PHPMD)
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

}

