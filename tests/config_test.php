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
 * Testcase class for the auth/saml2 config class.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use auth_saml2\config;

defined('MOODLE_INTERNAL') || die();

/**
 * Testcase class for the auth/saml2 config class.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_config_testcase extends advanced_testcase {

    public function test_config_load() {
        $this->resetAfterTest(true);

        set_config('idpmetadata', 'http://idpmetadata.local', 'auth/saml2');
        set_config('anyauth', 0, 'auth/saml2');

        $config = new config();
        $this->assertEquals('http://idpmetadata.local', $config->idpmetadata);
        $this->assertEquals(0, (int) $config->anyauth);
        $this->assertEquals(get_string('idpnamedefault', 'auth_saml2'), $config->idpdefaultname);
    }

    public function test_config_noload() {
        $this->resetAfterTest(true);

        set_config('idpmetadata', 'http://idpmetadata.local', 'auth/saml2');
        set_config('anyauth', 0, 'auth/saml2');

        $config = new config(false);
        $this->assertEquals('', $config->idpmetadata);
        $this->assertEquals(1, (int) $config->anyauth);
        $this->assertEquals('', $config->idpdefaultname);
    }

    public function test_config_property_not_found() {
        $this->resetAfterTest(true);

        set_config('somenonexistentproperty', 'value', 'auth/saml2');

        new config();
        $this->assertDebuggingCalled('Setting not added to config class: somenonexistentproperty');
    }

    public function test_config_update_configs() {
        $this->resetAfterTest(true);

        set_config('idpdefaultname', 'Some name', 'auth/saml2');
        set_config('entityid', 'Entity ID', 'auth/saml2');

        $config = new config();

        $cfgs = ['idpdefaultname' => 'A new name', 'entityid' => 'New entity ID'];
        $config->update_configs($cfgs);

        $dbconfig = get_config('auth/saml2');
        $this->assertEquals('A new name', $config->idpdefaultname);
        $this->assertEquals('A new name', $dbconfig->idpdefaultname);
        $this->assertEquals('New entity ID', $config->entityid);
        $this->assertEquals('New entity ID', $dbconfig->entityid);
    }

    public function test_config_update_configs_bad_param() {
        $config = new config(false);

        try {
            $config->update_configs('string');
            // Fail if the exception wasn't thrown.
            $this->fail();
        } catch (\coding_exception $e) {
            $this->assertContains('Parameter to update_configs must be an array', $e->getMessage());
        }
    }

    public function test_config_update_configs_not_property() {
        $config = new config(false);

        try {
            $config->update_configs(['notaproperty' => 'value']);
            // Fail if the exception wasn't thrown.
        } catch (\coding_exception $e) {
            $this->assertContains('Not an auth/saml2 config: notaproperty', $e->getMessage());
        }
    }
}