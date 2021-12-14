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

namespace auth_saml2;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use pix_icon;
use auth_saml2\admin\saml2_settings;

global $CFG;
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/login/lib.php');
require_once(__DIR__.'/../locallib.php');

/**
 * Plugin for Saml2 authentication.
 *
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth extends \auth_plugin_base {
    /**
     * @var array $metadataentities List of active IdPs configured.
     */
    public $metadataentities;

    /**
     * @var array $defaults The config defaults
     */
    public $defaults = [
        'idpname'            => '',
        'idpdefaultname'     => '', // Set in constructor.
        'idpmetadata'        => '',
        'multiidp'           => false,
        'defaultidp'         => null,
        'debug'              => 0,
        'duallogin'          => saml2_settings::OPTION_DUAL_LOGIN_YES,
        'autologin'          => saml2_settings::OPTION_AUTO_LOGIN_NO,
        'autologincookie'    => '',
        'anyauth'            => 1,
        'idpattr'            => 'uid',
        'mdlattr'            => 'username',
        'tolower'            => saml2_settings::OPTION_TOLOWER_EXACT,
        'autocreate'         => 0,
        'spmetadatasign'     => true,
        'showidplink'        => true,
        'alterlogout'        => '',
        'idpmetadatarefresh' => 0,
        'logtofile'          => 0,
        'logdir'             => '/tmp/',
        'nameidasattrib'     => 0,
        'flagresponsetype'   => saml2_settings::OPTION_FLAGGED_LOGIN_MESSAGE,
        'flagredirecturl'    => '',
        'flagmessage'        => '' // Set in constructor.
    ];

    /**
     * Constructor.
     */
    public function __construct() {
        global $CFG, $DB;
        $this->defaults['idpdefaultname'] = get_string('idpnamedefault', 'auth_saml2');
        $this->defaults['flagmessage'] = get_string('flagmessage_default', 'auth_saml2');
        $this->authtype = 'saml2';
        $mdl = new moodle_url($CFG->wwwroot);
        $this->spname = $mdl->get_host();
        $this->certpem = $this->get_file("{$this->spname}.pem");
        $this->certcrt = $this->get_file("{$this->spname}.crt");
        $this->config = (object) array_merge($this->defaults, (array) get_config('auth_saml2') );

        // Parsed IdP metadata, either a list of IdP metadata urls or a single XML blob.
        $parser = new idp_parser();
        $this->metadatalist = $parser->parse($this->config->idpmetadata);

        // Active entitiyIDs provided by the metadata.
        $this->metadataentities = auth_saml2_get_idps(true);

        // Check if we have mutiple IdPs configured.
        // If we have mutliple metadata entries set multiidp to true.
        $this->multiidp = false;

        if (count($this->metadataentities) > 1) {
            $this->multiidp = true;
        } else {
            // If we have mutliple IdP entries for a metadata set multiidp to true.
            foreach ($this->metadataentities as $idpentities) {
                if (count($idpentities) > 1) {
                    $this->multiidp = true;
                }
            }
        }

        $this->defaultidp = auth_saml2_get_default_idp();
    }

    /**
     * If debug mode enabled for plugin.
     *
     * @return bool
     */
    public function is_debugging() {
        return (bool) $this->config->debug;
    }

    public function get_saml2_directory() {
        global $CFG;
        $directory = "{$CFG->dataroot}/saml2";
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        return $directory;
    }

    public function get_file($file) {
        return $this->get_saml2_directory() . '/' . $file;
    }

    public function get_file_sp_metadata_file() {
        return $this->get_file($this->spname . '.xml');
    }

    /**
     * @param string|array $url The string with the URL or an array with all URLs as keys.
     * @return string Metadata file path.
     */
    public function get_file_idp_metadata_file($url) {
        if (is_object($url)) {
            $url = (array)$url;
        }

        if (is_array($url)) {
            $url = array_keys($url);
            $url = implode("\n", $url);
        }

        $filename = md5($url) . '.idp.xml';
        return $this->get_file($filename);
    }

    /**
     * A debug function, dumps to the php log
     *
     * @param string $msg Log message
     */
    private function log($msg) {
        if ($this->is_debugging()) {
            // @codingStandardsIgnoreLine
            error_log('auth_saml2: ' . $msg);

            // If SSP logs to tmp file we want these to also go there.
            if ($this->config->logtofile) {
                require_once(__DIR__.'/../setup.php');
                \SimpleSAML\Logger::debug('auth_saml2: ' . $msg);
            }
        }
    }

    /**
     * Returns a list of potential IdPs that this authentication plugin supports.
     * This is used to provide links on the login page.
     *
     * @param string $wantsurl the relative url fragment the user wants to get to.
     *
     * @return array of IdP's
     */
    public function loginpage_idp_list($wantsurl) {
        $conf = $this->config;

        // If we have disabled the visibility of the idp link, return with an empty array right away.
        if (!$conf->showidplink) {
            return [];
        }

        // If the plugin has not been configured then do not return an IdP link.
        if ($this->is_configured() === false) {
            return [];
        }

        // The array of IdPs to return.
        $idplist = [];

        foreach ($this->metadatalist as $metadata) {
            if (!array_key_exists($metadata->idpurl, $this->metadataentities)) {
                $message = "Missing identity configuration for '{$metadata->idpurl}': " .
                           'Please check/save SAML2 configuration or if able to inspect the database, check: ' .
                           "SELECT * FROM {auth_saml2_idps} WHERE metadataurl='{$metadata->idpurl}' " .
                           '-- Remember to purge caches if you make changes in the database.';
                debugging($message);
                continue;
            }

            foreach ($this->metadataentities[$metadata->idpurl] as $idpentityid => $idp) {
                $params = [
                    'wants' => $wantsurl,
                    'idp' => $idpentityid,
                ];

                // The wants url may already be routed via login.php so don't re-re-route it.
                if (strpos($wantsurl, '/auth/saml2/login.php')) {
                    $idpurl = new moodle_url($wantsurl);
                } else {
                    $idpurl = new moodle_url('/auth/saml2/login.php', $params);
                }
                $idpurl->param('passive', 'off');

                // A default icon.
                $idpiconurl = null;
                $idpicon = null;
                if (!empty($idp->logo)) {
                    $idpiconurl = new moodle_url($idp->logo);
                } else {
                    $idpicon = new pix_icon('i/user', 'Login');
                }

                // Initially use the default name. This is suitable for a single IdP.
                $idpname = $conf->idpdefaultname;

                // When multiple IdPs are configured, use a different default based on the IdP.
                if ($this->multiidp) {
                    $host = parse_url($idp->entityid, PHP_URL_HOST);
                    $idpname = get_string('idpnamedefault_varaible', 'auth_saml2', $host);
                }

                // Use a forced override set in the idpmetadata field.
                if (!empty($metadata->idpname)) {
                    $idpname = $metadata->idpname;
                }

                // Try to use the <mdui:DisplayName> if it exists.
                if (!empty($idp->name)) {
                    $idpname = $idp->name;
                }

                // Has the IdP label override been set in the admin configuration?
                // This is best used with a single IdP. Multiple IdP overrides are different.
                if (!empty($conf->idpname)) {
                    $idpname = $conf->idpname;
                }

                $idplist[] = [
                    'url'  => $idpurl,
                    'icon' => $idpicon,
                    'iconurl' => $idpiconurl,
                    'name' => $idpname,
                ];
            }
        }

        return $idplist;
    }

    /**
     * We don't manage passwords internally.
     *
     * @return bool Always false
     */
    public function is_internal() {
        return false;
    }

    /**
     * Checks to see if the plugin has been configured and the IdP/SP metadata files exist.
     *
     * @return bool
     */
    public function is_configured() {
        $file = $this->certcrt;
        if (!file_exists($file)) {
            $this->log(__FUNCTION__ . ' file not found, ' . $file);
            return false;
        }

        $file = $this->certpem;
        if (!file_exists($file)) {
            $this->log(__FUNCTION__ . ' file not found, ' . $file);
            return false;
        }

        // Requires at least one active IdP to work.
        if (!count($this->metadataentities)) {
            $this->log(__FUNCTION__ . ' no active IdPs');
            return false;
        }

        foreach ($this->metadataentities as $metadataid => $idps) {
            $file = $this->get_file_idp_metadata_file($metadataid);
            if (!file_exists($file)) {
                $this->log(__FUNCTION__ . ' file not found, ' . $file);
                return false;
            }
        }

        return true;
    }

    /**
     * Shows an error page for various authentication issues.
     *
     * @param string $msg The error message.
     */
    public function error_page($msg) {
        global $PAGE, $OUTPUT;

        $logouturl = new moodle_url('/auth/saml2/logout.php');

        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/auth/saml2/error.php');
        $PAGE->set_title(get_string('error', 'auth_saml2'));
        $PAGE->set_heading(get_string('error', 'auth_saml2'));
        echo $OUTPUT->header();
        echo $OUTPUT->box($msg);
        echo \html_writer::link($logouturl, get_string('logout'));
        echo $OUTPUT->footer();
        exit;
    }

    /**
     * All the checking happens before the login page in this hook
     */
    public function pre_loginpage_hook() {

        global $SESSION;

        $this->log(__FUNCTION__ . ' enter');

        // If we previously tried to force saml on, but then navigated
        // away, and come in from another deep link while dual auth is
        // on, then reset the previous session memory of forcing SAML.
        if (isset($SESSION->saml)) {
            $this->log(__FUNCTION__ . ' unset $SESSION->saml');
            unset($SESSION->saml);
        }

        $this->loginpage_hook();
        $this->log(__FUNCTION__ . ' exit');
    }

    /**
     * All the checking happens before the login page in this hook
     */
    public function loginpage_hook() {
        global $SESSION;

        $this->execute_callback('auth_saml2_loginpage_hook');

        $this->log(__FUNCTION__ . ' enter');

        // For Behat tests, clear the wantsurl if it has ended up pointing to the fixture. This
        // happens in older browsers which don't support the Referrer-Policy header used by fixture.
        if (defined('BEHAT_SITE_RUNNING') && !empty($SESSION->wantsurl) &&
                strpos($SESSION->wantsurl, '/auth/saml2/tests/fixtures/') !== false) {
            unset($SESSION->wantsurl);
        }

        // If the plugin has not been configured then do NOT try to use saml2.
        if ($this->is_configured() === false) {
            return;
        }

        $redirect = $this->should_login_redirect();
        if (is_string($redirect)) {
            redirect($redirect);
        } else if ($redirect === true) {
            $this->saml_login();
        } else {
            $this->log(__FUNCTION__ . ' exit');
            return;
        }

    }

    /**
     * Determines if we will redirect to the SAML login.
     *
     * @return bool|string If this returns true then we redirect to the SAML login.
     */
    public function should_login_redirect() {
        global $SESSION;

        $this->log(__FUNCTION__ . ' enter');

        $saml = optional_param('saml', null, PARAM_BOOL);
        $multiidp = optional_param('multiidp', false, PARAM_BOOL);
        // Also support noredirect param - used by other auth plugins.
        $noredirect = optional_param('noredirect', 0, PARAM_BOOL);
        if (!empty($noredirect)) {
            $saml = 0;
        }

        // Never redirect on POST.
        if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST')) {
            $this->log(__FUNCTION__ . ' skipping due to method=post');
            return false;
        }

        // Never redirect if requested so.
        if ($saml === 0) {
            $SESSION->saml = $saml;
            $this->log(__FUNCTION__ . ' skipping due to saml=off parameter');
            return false;
        }

        if ($this->check_whitelisted_ip_redirect()) {
            $this->log(__FUNCTION__ . ' redirecting due to ip found in idp whitelist');
            return true;
        }

        // Redirect to the select IdP page if requested so.
        if ($multiidp) {
            $this->log(__FUNCTION__ . ' redirecting due to multiidp=on parameter');
            $idpurl = new moodle_url('/auth/saml2/selectidp.php');
            return $idpurl->out();
        }

        // Never redirect if has error.
        if (!empty($_GET['SimpleSAML_Auth_State_exceptionId'])) {
            $this->log(__FUNCTION__ . ' skipping due to SimpleSAML_Auth_State_exceptionId');
            return false;
        }

        // If dual auth then stop and show login page.
        if ($this->config->duallogin == saml2_settings::OPTION_DUAL_LOGIN_YES && $saml == 0) {
            $this->log(__FUNCTION__ . ' skipping due to dual auth');
            return false;
        }

        // If ?saml=on even when duallogin is on, go directly to IdP.
        if ($saml == 1) {
            $this->log(__FUNCTION__ . ' redirecting due to query param ?saml=on');
            return true;
        }

        // Check whether we've skipped saml already.
        // This is here because loginpage_hook is called again during form
        // submission (all of login.php is processed) and ?saml=off is not
        // preserved forcing us to the IdP.
        //
        // This isn't needed when duallogin is on because $saml will default to 0
        // and duallogin is not part of the request.
        if ((isset($SESSION->saml) && $SESSION->saml == 0) && $this->config->duallogin == saml2_settings::OPTION_DUAL_LOGIN_NO) {
            $this->log(__FUNCTION__ . ' skipping due to no sso session');
            return false;
        }

        // If passive mode always redirect, except if saml=off. It will redirect back to login page.
        // The second time around saml=0 will be set in the session.
        if ($this->config->duallogin == saml2_settings::OPTION_DUAL_LOGIN_PASSIVE) {
            $this->log(__FUNCTION__ . ' redirecting due to passive mode.');
            return true;
        }

        // If ?saml=off even when duallogin is off, then always show the login page.
        // Additionally store this in the session so if the password fails we get
        // the login page again, and don't get booted to the IdP on the second
        // attempt to login manually.
        $saml = optional_param('saml', 1, PARAM_BOOL);
        $noredirect = optional_param('noredirect', 0, PARAM_BOOL);
        if (!empty($noredirect)) {
            $saml = 0;
        }

        if ($saml == 0) {
            $SESSION->saml = $saml;
            $this->log(__FUNCTION__ . ' skipping due to ?saml=off');
            return false;
        }

        // We are off to SAML land so reset the force in SESSION.
        if (isset($SESSION->saml)) {
            $this->log(__FUNCTION__ . ' unset SESSION->saml');
            unset($SESSION->saml);
        }

        return true;
    }

    /**
     * All the checking happens before the login page in this hook
     */
    public function saml_login() {

        // @codingStandardsIgnoreStart
        global $CFG, $DB, $USER, $SESSION, $saml2auth;
        // @codingStandardsIgnoreEnd

        require_once(__DIR__.'/../setup.php');
        require_once("$CFG->dirroot/login/lib.php");

        // Set the default IdP to be the first in the list. Used when dual login is disabled.
        $arr = array_reverse($saml2auth->metadataentities);
        $metadataentities = array_pop($arr);
        $idpentity = array_pop($metadataentities);
        $idp = md5($idpentity->entityid);

        // Specify the default IdP to use.
        $SESSION->saml2idp = $idp;

        // We store the IdP in the session to generate the config/config.php array with the default local SP.
        $idpalias = optional_param('idpalias', '', PARAM_TEXT);
        if (!empty($idpalias)) {
            $idpfound = false;

            foreach ($saml2auth->metadataentities as $idpentities) {
                foreach ($idpentities as $md5idpentityid => $idpentity) {
                    if ($idpalias == $idpentity->alias) {
                        $SESSION->saml2idp = $md5idpentityid;
                        $idpfound = true;
                        break 2;
                    }
                }
            }

            if (!$idpfound) {
                $this->error_page(get_string('noidpfound', 'auth_saml2', $idpalias));
            }
        } else if (isset($_GET['idp'])) {
            $SESSION->saml2idp = $_GET['idp'];
        } else if (!is_null($saml2auth->defaultidp)) {
            $SESSION->saml2idp = md5($saml2auth->defaultidp->entityid);
        } else if ($saml2auth->multiidp) {
            // At this stage there is no alias, get-param or default IdP configured.
            // On a multi-idp system, now check for any whitelisted IP address redirection.
            $entitiyid = $this->check_whitelisted_ip_redirect();
            if ($entitiyid !== null) {
                $SESSION->saml2idp = $entitiyid;
            } else {
                $idpurl = new moodle_url('/auth/saml2/selectidp.php');
                redirect($idpurl);
            }
        }

        if (isset($_GET['rememberidp']) && $_GET['rememberidp'] == 1) {
            $this->set_idp_cookie($SESSION->saml2idp);
        }

        // Configure passive authentication.
        $passive = $this->config->duallogin == saml2_settings::OPTION_DUAL_LOGIN_PASSIVE;
        $passive = (bool)optional_param('passive', $passive, PARAM_BOOL);
        $params = ['isPassive' => $passive];
        if ($passive) {
            $params['ErrorURL'] = (new moodle_url('/login/index.php', ['saml' => 0]))->out(false);
        }

        $auth = new \SimpleSAML\Auth\Simple($this->spname);
        // Redirect to IdP login page for authentication.
        $auth->requireAuth($params);

        // Complete login process.
        $attributes = $auth->getAttributes();
        $this->saml_login_complete($attributes);
    }


    /**
     * The user has done the SAML handshake now we can log them in
     *
     * This is split so we can handle SP and IdP first login flows.
     */
    public function saml_login_complete($attributes) {

        // @codingStandardsIgnoreStart
        global $CFG, $DB, $USER, $SESSION, $saml2auth;
        // @codingStandardsIgnoreEnd

        if ($this->config->attrsimple) {
            $attributes = $this->simplify_attr($attributes);
        }

        $attr = $this->config->idpattr;
        if (empty($attributes[$attr]) ) {
            $this->error_page(get_string('noattribute', 'auth_saml2', $attr));
        }

        $user = null;
        foreach ($attributes[$attr] as $key => $uid) {
            if ($this->config->tolower == saml2_settings::OPTION_TOLOWER_LOWER_CASE) {
                $this->log(__FUNCTION__ . " to lowercase for $key => $uid");
                $uid = strtolower($uid);
            }
            if ($this->config->tolower == saml2_settings::OPTION_TOLOWER_CASE_INSENSITIVE) {
                $this->log(__FUNCTION__ . " case insensitive compare for $key => $uid");
                $user = $DB->get_record_select('user', "LOWER({$this->config->mdlattr}) = LOWER(:uid) AND deleted = 0", ['uid' => $uid]);
            } else {
                $user = $DB->get_record('user', [$this->config->mdlattr => $uid, 'deleted' => 0]);
            }
            if (!empty($user)) {
                continue;
            }
        }

        // Testing user's groups and allow access decided on preferences.
        if (!$this->is_access_allowed_for_member($attributes)) {
            $this->handle_blocked_access();
        }

        $newuser = false;
        if (!$user) {
            if ($this->config->autocreate) {
                $email = $this->get_email_from_attributes($attributes);
                // If can't have accounts with the same emails, check if email is taken before create a new user.
                if (empty($CFG->allowaccountssameemail) && $this->is_email_taken($email)) {
                    $this->log(__FUNCTION__ . " user '$uid' can't be autocreated as email '$email' is taken");
                    $this->error_page(get_string('emailtaken', 'auth_saml2', $email));
                }

                // Honor the core allowemailaddresses setting #412.
                $error = email_is_not_allowed($email);
                if ($error) {
                    $this->log(__FUNCTION__ . " '$email' " . $error);
                    $this->handle_blocked_access();
                }

                $this->log(__FUNCTION__ . " user '$uid' is not in moodle so autocreating");
                $user = create_user_record($uid, '', 'saml2');
                $newuser = true;
            } else {
                $this->log(__FUNCTION__ . " user '$uid' is not in moodle so error");
                $this->error_page(get_string('nouser', 'auth_saml2', $uid));
            }
        } else {
            // Prevent access to users who are suspended.
            if ($user->suspended) {
                $this->error_page(get_string('suspendeduser', 'auth_saml2', $uid));
            }
            // Make sure all user data is fetched.
            $user = get_complete_user_data('username', $user->username);
            if (!$user) {
                $this->log(__FUNCTION__ . ' did not find user ' . $user->username);
                $this->error_page(get_string('nouser', 'auth_saml2', $uid));
            }

            $this->log(__FUNCTION__ . ' found user '.$user->username);
        }

        if (!$this->config->anyauth && $user->auth != 'saml2') {
            $this->log(__FUNCTION__ . " user $uid is auth type: $user->auth");
            $this->error_page(get_string('wrongauth', 'auth_saml2', $uid));
        }

        if ($this->config->anyauth && !is_enabled_auth($user->auth) ) {
            $this->log(__FUNCTION__ . " user $uid's auth type: $user->auth is not enabled");
            $this->error_page(get_string('anyauthotherdisabled', 'auth_saml2', array(
                'username' => $uid, 'auth' => $user->auth,
            )));
        }

        // Do we need to update any user fields? Unlike ldap, we can only do
        // this now. We cannot query the IdP at any time.
        $this->update_user_profile_fields($user, $attributes, $newuser);

        // If admin has been set for this IdP we make the user an admin.
        $adminidp = false;
        foreach ($saml2auth->metadataentities as $idpentities) {
            foreach ($idpentities as $md5idpentityid => $idpentity) {

                if (!empty($SESSION->saml2idp) && $SESSION->saml2idp == $md5idpentityid) {
                    $adminidp = $idpentity->adminidp;
                    break 2;
                }
            }
        }

        if ($adminidp) {
            $admins = array();
            foreach (explode(',', $CFG->siteadmins) as $admin) {
                $admin = (int)$admin;
                if ($admin) {
                    $admins[$admin] = $admin;
                }
            }

            $admins[$user->id] = $user->id;
            set_config('siteadmins', implode(',', $admins));
        }

        // Make sure all user data is fetched.
        $user = get_complete_user_data('username', $user->username);

        complete_user_login($user);
        $USER->loggedin = true;
        $USER->site = $CFG->wwwroot;
        set_moodle_cookie($USER->username);

        $wantsurl = core_login_get_return_url();
        // If we are not on the page we want, then redirect to it.
        if ( qualified_me() !== $wantsurl ) {
            $this->log(__FUNCTION__ . " redirecting to $wantsurl");
            unset($SESSION->wantsurl);
            redirect($wantsurl);
            exit;
        } else {
            $this->log(__FUNCTION__ . " continuing onto " . qualified_me() );
        }

        return;
    }

    /**
     * Redirect SAML2 login if a flagredirecturl has been configured.
     *
     * @throws \moodle_exception
     */
    protected function redirect_blocked_access() {

        if (!empty($this->config->flagredirecturl)) {
            redirect(new moodle_url($this->config->flagredirecturl));
        } else {
            $this->log(__FUNCTION__ . ' no redirect URL value set.');
            // Fallback to flag message if redirect URL not set.
            $this->error_page($this->config->flagmessage);
        }
    }

    /**
     * Handles blocked access based on configuration.
     */
    protected function handle_blocked_access() {
        switch ($this->config->flagresponsetype) {
            case saml2_settings::OPTION_FLAGGED_LOGIN_REDIRECT :
                $this->redirect_blocked_access ();
                break;
            case saml2_settings::OPTION_FLAGGED_LOGIN_MESSAGE :
            default :
                $this->error_page ( $this->config->flagmessage );
                break;
        }
    }

    /**
     * Checks configuration of the multiple IdP IP whitelist field. If the users IP matches, this will
     * return the $md5idpentityid on true. Or false if not found.
     *
     * This is used in two places, firstly to determine if a saml redirect is to happen.
     * Secondly to determine which IdP to force the redirect to.
     *
     * @return bool|string
     */
    protected function check_whitelisted_ip_redirect() {
        foreach ($this->metadataentities as $idpentities) {
            foreach ($idpentities as $md5idpentityid => $idpentity) {
                if (!$idpentity->activeidp) {
                    continue;
                }
                if (\core\ip_utils::is_ip_in_subnet_list(getremoteaddr(), $idpentity->whitelist)) {
                    return $md5idpentityid;
                }
            }
        }
        return false;
    }

    /**
     * Testing user's groups attribute and allow access decided on preferences.
     *
     * @param array $attributes A list of attributes from the request
     * @return bool
     */
    public function is_access_allowed_for_member($attributes) {

        // If there is no encumberance attribute configured in Moodle, let them pass.
        if (empty($this->config->grouprules) ) {
            return true;
        }

        $uid = $attributes[$this->config->idpattr][0];
        $rules = group_rule::get_list($this->config->grouprules);
        $userhasgroups = false;

        foreach ($rules as $rule) {
            if (empty($attributes[$rule->get_attribute()])) {
                continue;
            }

            $userhasgroups = true; // At least one encumberance attribute is detected.

            foreach ($attributes[$rule->get_attribute()] as $group) {
                if ($group == $rule->get_group()) {
                    if ($rule->is_allowed()) {
                        $this->log(__FUNCTION__ . " user '$uid' is in allowed group. Access allowed.");
                        return true;
                    } else {
                        $this->log(__FUNCTION__ . " user '$uid' is in restricted group. Access denied.");
                        return false;
                    }
                }
            }
        }

        // If a user has no encumberance attribute let them into Moodle.
        if (empty($userhasgroups)) {
            return true;
        }

        $this->log(__FUNCTION__ . " user '$uid' isn't in allowed. Access denied.");
        return false;
    }

    /**
     * Simplifies attribute key names
     *
     * Rather than attempting to have an explicity mapping this simply
     * detects long key names which contain non word characters and then
     * grabs the last useful component of the string. Note it creates new
     * keys, doesn't remove the old ones, and will not overwrite keys either.
     */
    public function simplify_attr($attributes) {

        foreach ($attributes as $key => $val) {
            if (preg_match("/\W/", $key)) {
                $parts = preg_split("/\W/", $key);
                $simple = $parts[count($parts) - 1];
                $attributes[$simple] = $attributes[$key];
            }
        }
        return $attributes;
    }

    /**
     * Checks the field map config for values that update onlogin or when a new user is created
     * and returns true when the fields have been merged into the user object.
     *
     * @param $attributes
     * @param bool $newuser
     * @return bool true on success
     */
    public function update_user_profile_fields(&$user, $attributes, $newuser = false) {
        global $CFG;

        $mapconfig = get_config('auth_saml2');
        $allkeys = array_keys(get_object_vars($mapconfig));
        $update = false;

        foreach ($allkeys as $key) {
            if (preg_match('/^field_updatelocal_(.+)$/', $key, $match)) {
                $field = $match[1];
                if (!empty($mapconfig->{'field_map_'.$field})) {
                    $attr = $mapconfig->{'field_map_'.$field};
                    $updateonlogin = $mapconfig->{'field_updatelocal_'.$field} === 'onlogin';

                    if ($newuser || $updateonlogin) {
                        // Basic error handling, check to see if the attributes exist before mapping the data.
                        if (array_key_exists($attr, $attributes)) {
                            // Handing an empty array of attributes.
                            if (!empty($attributes[$attr])) {

                                // If can't have accounts with the same emails, check if email is taken before update a new user.
                                if ($field == 'email' && empty($CFG->allowaccountssameemail)) {
                                    $email = $attributes[$attr][0];
                                    if ($this->is_email_taken($email, $user->username)) {
                                        $this->log(__FUNCTION__ .
                                            " user '$user->username' email can't be updated as '$email' is taken");
                                        // Warn user that we are not able to update his email.
                                        \core\notification::warning(get_string('emailtakenupdate', 'auth_saml2', $email));

                                        continue;
                                    }
                                }

                                // Custom profile fields have the prefix profile_field_ and will be saved as profile field data.
                                $user->$field = $attributes[$attr][0];
                                $update = true;
                            }
                        }
                    }
                }
            }
        }

        if ($update) {
            require_once($CFG->dirroot.'/user/lib.php');
            if ($user->description === true) {
                // Function get_complete_user_data() sets description = true to avoid keeping in memory.
                // If set to true - don't update based on data from this call.
                unset($user->description);
            }
            // We should save the profile fields first so they are present and
            // then we update the user which also fires events which other
            // plugins listen to so they have the correct user data.
            profile_save_data($user);
            user_update_user($user, false);
        }

        return $update;
    }

    /**
     * Get email address from attributes.
     *
     * @param array $attributes A list of attributes.
     *
     * @return bool
     */
    public function get_email_from_attributes(array $attributes) {
        if (!empty($this->config->field_map_email) && !empty($attributes[$this->config->field_map_email])) {
            return $attributes[$this->config->field_map_email][0];
        }

        return false;
    }

    /**
     * Check if given email is taken by other user(s).
     *
     * @param string | bool $email Email to check.
     * @param string | null $excludeusername A user name to exclude.
     *
     * @return bool
     */
    public function is_email_taken($email, $excludeusername = null) {
        global $CFG, $DB;

        if (!empty($email)) {
            // Make a case-insensitive query for the given email address.
            $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid AND deleted = :deleted';
            $params = array(
                'email' => $email,
                'mnethostid' => $CFG->mnet_localhost_id,
                'deleted' => 0
            );

            if ($excludeusername) {
                $select .= ' AND username <> :username';
                $params['username'] = $excludeusername;
            }

            // If there are other user(s) that already have the same email, display an error.
            if ($DB->record_exists_select('user', $select, $params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hook for overriding behaviour of logout page.
     * This method is called from login/logout.php page.
     *
     * There are 3 sessions we need to logout:
     * 1) The moodle session.
     * 2) The SimpleSAML SP session.
     * 3) The IdP session, if the IdP supports SingleSignout.
     */
    public function logoutpage_hook() {
        global $SESSION, $redirect;

        $this->execute_callback('auth_saml2_logoutpage_hook');

        // Lets capture the saml2idp hash.
        $idp = $this->spname;
        if (!empty($SESSION->saml2idp)) {
            $idp = $SESSION->saml2idp;
        }

        $this->log(__FUNCTION__ . ' Do moodle logout');
        // Do the normal moodle logout first as we may redirect away before it
        // gets called by the normal core process.
        require_logout();

        // Do not attempt to log out of the IdP.
        if (!$this->config->attemptsignout) {

            $alterlogout = $this->config->alterlogout;
            if (!empty($alterlogout)) {
                // If we don't sign out of the IdP we still want to honor the
                // alternate logout page.
                $this->log(__FUNCTION__ . " Do SSP alternate URL logout $alterlogout");
                redirect(new moodle_url($alterlogout));
            }
            return;
        }

        require_once(__DIR__.'/../setup.php');

        // We just loaded the SP session which replaces the Moodle so we lost
        // the session data, lets temporarily restore the IdP.
        $SESSION->saml2idp = $idp;
        $auth = new \SimpleSAML\Auth\Simple($this->spname);

        // Only log out of the IdP if we logged in via the IdP. TODO check session timeouts.
        if ($auth->isAuthenticated()) {
            $this->log(__FUNCTION__ . ' Do SSP logout');
            $alterlogout = $this->config->alterlogout;
            if (!empty($alterlogout)) {
                $this->log(__FUNCTION__ . " Do SSP alternate URL logout $alterlogout");
                $redirect = $alterlogout;
            }
            $auth->logout([
                'ReturnTo' => $redirect,
                'ReturnCallback' => ['\auth_saml2\api', 'after_logout_from_sp'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param object $config
     * @return boolean
     */
    public function process_config($config) {
        $haschanged = false;

        foreach (array_keys($this->defaults) as $key) {
            if ($config->$key != $this->config->$key) {
                set_config($key, $config->$key, 'auth_saml2');
                $haschanged = true;
            }
        }

        if ($haschanged) {
            $file = $this->get_file_sp_metadata_file();
            @unlink($file);
        }
        return true;
    }

    /**
     * A simple GUI tester which shows the raw API output
     */
    public function test_settings() {
        include(__DIR__.'/../tester.php');
    }

    /**
     * Returns the version of SSP that this plugin is using.
     *
     * @return string
     */
    public function get_ssp_version() {
        require_once(__DIR__.'/../setup.php');
        $config = new \SimpleSAML\Configuration(array(), '');
        return $config->getVersion();
    }

    /**
     * Allow saml2 auth method to be manually set for users e.g. bulk uploading users.
     */

    public function can_be_manually_set() {
        return true;
    }

    /**
     * Sets a preferred IdP in a cookie for faster subsequent logging in.
     *
     * @param string $idp a md5 encoded IdP entityid
     */
    public function set_idp_cookie($idp) {
        global $CFG;

        if (NO_MOODLE_COOKIES) {
            return;
        }

        $cookiename = 'MOODLEIDP1_'.$CFG->sessioncookie;

        $cookiesecure = is_moodle_cookie_secure();

        // Delete old cookie.
        setcookie($cookiename, '', time() - HOURSECS, $CFG->sessioncookiepath, $CFG->sessioncookiedomain,
                  $cookiesecure, $CFG->cookiehttponly);

        if ($idp !== '') {
            // Set username cookie for 60 days.
            setcookie($cookiename, $idp, time() + (DAYSECS * 60), $CFG->sessioncookiepath, $CFG->sessioncookiedomain,
                      $cookiesecure, $CFG->cookiehttponly);
        }
    }

    /**
     * Gets a preferred IdP from a cookie for faster subsequent logging in.
     *
     * @return string $idp a md5 encoded IdP entityid
     */
    public function get_idp_cookie() {
        global $CFG;

        if (NO_MOODLE_COOKIES) {
            return '';
        }

        $cookiename = 'MOODLEIDP1_'.$CFG->sessioncookie;

        if (empty($_COOKIE[$cookiename])) {
            return '';
        } else {
            return $_COOKIE[$cookiename];
        }
    }

    /**
     * Execute callback function
     * @param $function name of the callback function to be executed
     * @param string $file file to find the function
     */
    private function execute_callback($function, $file = 'lib.php') {
        if (function_exists('get_plugins_with_function')) {
            $pluginsfunction = get_plugins_with_function($function, $file);
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    $pluginfunction();
                }
            }
        }
    }
}
