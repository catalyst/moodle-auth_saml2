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
        'entityid'        => '',
        'ssourl'          => '',
        'slourl'          => '',
        'certfingerprint' => '',
        'debug'           => 0,
    );

    /**
     * Constructor.
     */
    public function __construct() {
        global $CFG;
        $this->authtype = 'saml2';
        $mdl = new moodle_url($CFG->wwwroot);
        $this->spname = $mdl->get_host();
        $this->config = (object) array_merge($this->defaults, (array) get_config('auth_saml2') );
    }

    /**
     * A debug function, dumps to the php log
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
        // $this->loginpage_hook(); TODO
        $this->log(__FUNCTION__ . ' exit');
    }

    /**
     * All the checking happens before the login page in this hook
     */
    public function loginpage_hook() {

        global $CFG, $DB, $USER, $SESSION, $SITE, $PAGE, $OUTPUT, $saml2auth;

        $this->log(__FUNCTION__);

        require_once('setup.php');
        $auth = new SimpleSAML_Auth_Simple($this->spname);
        $auth->requireAuth();
        $attributes = $auth->getAttributes();

        $username = $attributes['uid'][0];
        if ($user = $DB->get_record('user', array( 'username' => $username ))) {

            $this->log(__FUNCTION__ . ' found user '.$user->username);
            complete_user_login($user);

            if (isset($SESSION->wantsurl) && !empty($SESSION->wantsurl)) {
                $urltogo = $SESSION->wantsurl;
            } else if (isset($_GET['wantsurl'])) {
                $urltogo = $_GET['wantsurl'];
            } else {
                $urltogo = $CFG->wwwroot;
            }

            $USER->loggedin = true;
            $USER->site = $CFG->wwwroot;
            set_moodle_cookie($USER->username);

            // If we are not on the page we want, then redirect to it.
            if ( qualified_me() !== $urltogo ) {
                $this->log(__FUNCTION__ . " redirecting to $urltogo");
                redirect($urltogo);
                exit;
            } else {
                $this->log(__FUNCTION__ . " continuing onto " . qualified_me() );
            }
        } else {
            $this->log(__FUNCTION__ . ' user ' . $username . ' is not in moodle');
            $PAGE->set_course($SITE);
            echo $OUTPUT->header();
            echo $OUTPUT->box(get_string('nouser', 'auth_saml2', $username));
            echo $OUTPUT->footer();
            // TODO kill session to enable login as somebody else with an account.
        }
        exit;

    }

    public function logoutpage_hook() {
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
        global $CFG;
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

