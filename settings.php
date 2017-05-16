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
 * Admin config settings page
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Warning for missing mcrypt
    $settings->add(new admin_setting_php_extension_enabled(
            'auth_saml2/mcrypt',
            get_string('mcrypt', 'auth_saml2'),
            get_string('mcryptnotfound', 'auth_saml2'),
            'mcrypt'));

    $yesno = array(
            new lang_string('no'),
            new lang_string('yes'),
    );

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_saml2/pluginname', '',
        new lang_string('auth_saml2description', 'auth_saml2')));

    // IDP Metadata. 
    $settings->add(new admin_setting_configtextarea(
            'auth_saml2/idpmetadata',
            get_string('idpmetadata', 'auth_saml2'),
            get_string('idpmetadata_help', 'auth_saml2'),
            '', PARAM_RAW, 80, 5));

    // IDP  name
    $settings->add(new admin_setting_configtext(
            'auth_saml2/idpname',
            get_string('idpname', 'auth_saml2'),
            get_string('idpname_help', 'auth_saml2'),
            get_string('idpnamedefault', 'auth_saml2'),
            PARAM_ALPHANUMEXT));

    // Display IDP Link.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/showidplink',
            get_string('showidplink', 'auth_saml2'),
            get_string('showidplink_help', 'auth_saml2'),
            0, $yesno));

    // Debugging.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/debug',
            get_string('debug', 'auth_saml2'),
            get_string('debug_help', 'auth_saml2', "$CFG->wwwroot/auth/saml2/debug.php"),
            0, $yesno));

    // Lock certificate.

    // Regenerate certificate.

    // SP Metadata.

    // SP Metadata signature.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/spmetadatasign',
            get_string('spmetadatasign', 'auth_saml2'),
            get_string('spmetadatasign_help', 'auth_saml2'),
            0, $yesno));

    // Dual Login.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/duallogin',
            get_string('duallogin', 'auth_saml2'),
            get_string('duallogin_help', 'auth_saml2'),
            0, $yesno));

    // Allow any auth type
    $settings->add(new admin_setting_configselect(
            'auth_saml2/anyauth',
            get_string('anyauth', 'auth_saml2'),
            get_string('anyauth_help', 'auth_saml2'),
            0, $yesno));

    // IDP to Moodle mapping.

    // Lowercase.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/tolower',
            get_string('tolower', 'auth_saml2'),
            get_string('tolower_help', 'auth_saml2'),
            0, $yesno));

    // Autocreate Users.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/autocreate',
            get_string('autocreate', 'auth_saml2'),
            get_string('autocreate_help', 'auth_saml2'),
            0, $yesno));

    // Alternative Logout URL.
    $settings->add(new admin_setting_configtext(
            'auth_saml2/alterlogout',
            get_string('alterlogout', 'auth_saml2'),
            get_string('alterlogout_help', 'auth_saml2'),
            '',
            PARAM_URL));
  
    // SAMLPHP version.


    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('email');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            get_string('auth_fieldlocks_help', 'auth'), false, false);
}
