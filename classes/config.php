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
 * Wrapper class around the auth/saml2 config.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_saml2;

defined('MOODLE_INTERNAL') || die();

/**
 * Wrapper class around the auth/saml2 config.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config {
    public $idpname = '';
    public $entityid = '';
    public $idpdefaultname = '';
    public $idpmetadata = '';
    public $debug = 0;
    public $duallogin = 1;
    public $anyauth = 1;
    public $idpattr = 'uid';
    public $mdlattr = 'username';
    public $tolower = 0;
    public $autocreate = 0;
    public $idpmetadatarefresh = 0;

    /**
     * config constructor.
     * @param bool $loadconfig
     * @throws \coding_exception
     */
    public function __construct($loadconfig = true) {
        if ($loadconfig) {
            $this->load_config();
        }
    }

    /**
     * @param $configs
     * @throws \coding_exception
     */
    public function update_configs($configs) {
        if (!is_array($configs)) {
            throw new \coding_exception('Parameter to update_configs must be an array');
        }
        foreach ($configs as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \coding_exception('Not an auth/saml2 config: ' . $key);
            }
            set_config($key, $value, 'auth/saml2');
            $this->{$key} = $value;
        }

        // Reload the configs from the db.
        $this->load_config();
    }

    /**
     * Loads the object properties from the plugins' settings stored in the database.
     */
    private function load_config() {
        // Set the default here.
        $this->idpdefaultname = get_string('idpnamedefault', 'auth_saml2');
        $dbconfig = get_config('auth/saml2');
        if (empty($dbconfig)) {
            // Nothing to load.
            return;
        }
        foreach ($dbconfig as $prop => $value) {
            if (strpos($prop, 'field_') === 0) {
                // Don't bother with the field_* settings for this class.
                continue;
            }
            if ($prop === 'version') {
                // Don't care about the version config.
                continue;
            }
            if (!property_exists($this, $prop)) {
                // If there is a new setting that is not one of the field_* settings we need to know about it.
                debugging('Setting not added to config class: ' . $prop, DEBUG_DEVELOPER);
                continue;
            }
            $this->{$prop} = $value;
        }
    }
}