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
 * Authenticate using an embeded SimpleSamlPhp instance
 *
 * @package   auth_saml2
 * @copyright Brendan Heywood <brendan@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/authlib.php');

/**
 * Plugin for Saml2 authentication.
 *
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_saml2 extends auth_plugin_base {

    public $defaults = array(
        'ssourl'          => '',
        'slourl'          => '',
        // 'certfingerprint' => 0,
        // 'memcacheurl'     => '',
        'debug'           => 0,
    );

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'saml2';
        $this->config = (object) array_merge($this->defaults, (array) get_config('auth_saml2') );
    }

    /**
     * A debug function, dumps to the php log as well as into the
     * response headers for easy curl based debugging
     *
     */
    private function log($msg) {
        if ($this->config->debug) {
            error_log('auth_saml2: ' . $msg);
        }
    }

    /**
     * All the checking happens before the login page in this hook
     */
    public function pre_loginpage_hook() {

        $this->log(__FUNCTION__ . ' enter');
        // $this->loginpage_hook();
        $this->log(__FUNCTION__ . ' exit');
    }

    /**
     * All the checking happens before the login page in this hook
     */
    public function loginpage_hook() {

        global $CFG, $DB, $USER, $SESSION;

        $this->log(__FUNCTION__);

        // TODO everything goes here

    }

    /**
     * Returns false regardless of the username and password as we never get
     * to the web form. If we do, some other auth plugin will handle it
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login ($username, $password) {
        return false;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param object $config
     * @param object $err
     * @param array $userfields
     */
    public function config_form($config, $err, $userfields) {
        $config = (object) array_merge($this->defaults, (array) $config );
        include("config.php");
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param object $config
     */
    public function process_config($config) {
        foreach ($this->defaults as $key => $value) {
            set_config($key, $config->$key, 'auth_saml2');
        }
        return true;
    }

    /*
     * A simple GUI tester which shows the raw API output
     */
    public function test_settings() {
        include('test.php');
    }
}

