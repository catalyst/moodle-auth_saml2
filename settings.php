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

use auth_saml2\admin\saml2_settings;
use auth_saml2\admin\setting_button;
use auth_saml2\admin\setting_textonly;
use auth_saml2\ssl_algorithms;

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/auth/saml2/locallib.php');

    $yesno = array(
            new lang_string('no'),
            new lang_string('yes'),
    );

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_saml2/pluginname', '',
        new lang_string('auth_saml2description', 'auth_saml2')));

    // IDP Metadata.
    $idpmetadata = new \auth_saml2\admin\setting_idpmetadata();
    $idpmetadata->set_updatedcallback('auth_saml2_update_idp_metadata');
    $settings->add($idpmetadata);

    // IDP name.
    $settings->add(new admin_setting_configtext(
            'auth_saml2/idpname',
            get_string('idpname', 'auth_saml2'),
            get_string('idpname_help', 'auth_saml2'),
            get_string('idpnamedefault', 'auth_saml2'),
            PARAM_TEXT));

    // Manage available IdPs.
    $settings->add(new setting_button(
        'auth_saml2/availableidps',
        get_string('availableidps', 'auth_saml2'),
        get_string('availableidps_help', 'auth_saml2'),
        get_string('availableidps', 'auth_saml2'),
        $CFG->wwwroot . '/auth/saml2/availableidps.php'
        ));

    // Display IDP Link.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/showidplink',
            get_string('showidplink', 'auth_saml2'),
            get_string('showidplink_help', 'auth_saml2'),
            1, $yesno));

    // IDP Metadata refresh.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/idpmetadatarefresh',
            get_string('idpmetadatarefresh', 'auth_saml2'),
            get_string('idpmetadatarefresh_help', 'auth_saml2'),
            1, $yesno));

    // Debugging.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/debug',
            get_string('debug', 'auth_saml2'),
            get_string('debug_help', 'auth_saml2', $CFG->wwwroot . '/auth/saml2/debug.php'),
            0, $yesno));

    // Logging.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/logtofile',
            get_string('logtofile', 'auth_saml2'),
            get_string('logtofile_help', 'auth_saml2'),
            0, $yesno));
    $settings->add(new admin_setting_configtext(
            'auth_saml2/logdir',
            get_string('logdir', 'auth_saml2'),
            get_string('logdir_help', 'auth_saml2'),
            get_string('logdirdefault', 'auth_saml2'),
            PARAM_TEXT));

    // See section 8.3 from http://docs.oasis-open.org/security/saml/v2.0/saml-core-2.0-os.pdf for more information.
    $nameidlist = [
        'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
        'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
        'urn:oasis:names:tc:SAML:1.1:nameid-format:WindowsDomainQualifiedName',
        'urn:oasis:names:tc:SAML:2.0:nameid-format:kerberos',
        'urn:oasis:names:tc:SAML:2.0:nameid-format:entity',
        'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    ];
    $settings->add(new admin_setting_configselect(
        'auth_saml2/nameidpolicy',
        get_string('nameidpolicy', 'auth_saml2'),
        get_string('nameidpolicy_help', 'auth_saml2'),
        'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        array_combine($nameidlist, $nameidlist)));

    // Add NameID as attribute.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/nameidasattrib',
            get_string('nameidasattrib', 'auth_saml2'),
            get_string('nameidasattrib_help', 'auth_saml2'),
            0, $yesno));

    // Lock certificate.
    $settings->add(new setting_button(
            'auth_saml2/certificatelock',
            get_string('certificatelock', 'auth_saml2'),
            get_string('certificatelock_help', 'auth_saml2'),
            get_string('certificatelock', 'auth_saml2'),
            $CFG->wwwroot . '/auth/saml2/certificatelock.php'
            ));

    // Regenerate certificate.
    $settings->add(new setting_button(
            'auth_saml2/certificate',
            get_string('certificate', 'auth_saml2'),
            get_string('certificate_help', 'auth_saml2', $CFG->wwwroot . '/auth/saml2/cert.php'),
            get_string('certificate', 'auth_saml2'),
            $CFG->wwwroot . '/auth/saml2/regenerate.php'
            ));

    // SP Metadata.
    $settings->add(new setting_textonly(
           'auth_saml2/spmetadata',
           get_string('spmetadata', 'auth_saml2'),
           get_string('spmetadata_help', 'auth_saml2', $CFG->wwwroot . '/auth/saml2/sp/metadata.php')
           ));

    // SP Metadata signature.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/spmetadatasign',
            get_string('spmetadatasign', 'auth_saml2'),
            get_string('spmetadatasign_help', 'auth_saml2'),
            0, $yesno));

    $settings->add(new admin_setting_configselect(
        'auth_saml2/signaturealgorithm',
        get_string('signaturealgorithm', 'auth_saml2'),
        get_string('signaturealgorithm_help', 'auth_saml2'),
        ssl_algorithms::get_default_saml_signature_algorithm(),
        ssl_algorithms::get_valid_saml_signature_algorithms()));

    // Dual Login.
    $dualloginoptions = [
        saml2_settings::OPTION_DUAL_LOGIN_NO      => get_string('no'),
        saml2_settings::OPTION_DUAL_LOGIN_YES     => get_string('yes'),
        saml2_settings::OPTION_DUAL_LOGIN_PASSIVE => get_string('passivemode', 'auth_saml2'),
    ];
    $dualloginoptions = $yesno;
    $dualloginoptions[] = get_string('passivemode', 'auth_saml2');
    $settings->add(new admin_setting_configselect(
            'auth_saml2/duallogin',
            get_string('duallogin', 'auth_saml2'),
            get_string('duallogin_help', 'auth_saml2'),
            saml2_settings::OPTION_DUAL_LOGIN_YES,
            $dualloginoptions));

    // Allow any auth type.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/anyauth',
            get_string('anyauth', 'auth_saml2'),
            get_string('anyauth_help', 'auth_saml2'),
            0, $yesno));

    // Simplify attributes
    $settings->add(new admin_setting_configselect(
            'auth_saml2/attrsimple',
            get_string('attrsimple', 'auth_saml2'),
            get_string('attrsimple_help', 'auth_saml2'),
            1, $yesno));

    // IDP to Moodle mapping.
    // IDP attribute.
    $settings->add(new admin_setting_configtext(
            'auth_saml2/idpattr',
            get_string('idpattr', 'auth_saml2'),
            get_string('idpattr_help', 'auth_saml2'),
            'uid', PARAM_TEXT));

    // Moodle Field.
    $fields = [
        'username'      => get_string('username'),
        'idnumber'      => get_string('idnumber'),
        'email'         => get_string('email'),
        'alternatename' => get_string('alternatename'),
    ];
    $settings->add(new admin_setting_configselect(
            'auth_saml2/mdlattr',
            get_string('mdlattr', 'auth_saml2'),
            get_string('mdlattr_help', 'auth_saml2'),
            'username', $fields));

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

    // Multi IdP display type.
    $multiidpdisplayoptions = [
        saml2_settings::OPTION_MULTI_IDP_DISPLAY_DROPDOWN => get_string('multiidpdropdown', 'auth_saml2'),
        saml2_settings::OPTION_MULTI_IDP_DISPLAY_BUTTONS => get_string('multiidpbuttons', 'auth_saml2')
    ];
    $settings->add(new admin_setting_configselect(
        'auth_saml2/multiidpdisplay',
        get_string('multiidpdisplay', 'auth_saml2'),
        get_string('multiidpdisplay_help', 'auth_saml2'),
        saml2_settings::OPTION_MULTI_IDP_DISPLAY_DROPDOWN,
        $multiidpdisplayoptions));

    // Attempt Single Sign out.
    $settings->add(new admin_setting_configselect(
        'auth_saml2/attemptsignout',
        get_string('attemptsignout', 'auth_saml2'),
        get_string('attemptsignout_help', 'auth_saml2'),
        1,
        $yesno));

    // SAMLPHP version.
    $authplugin = get_auth_plugin('saml2');
    $settings->add(new setting_textonly(
            'auth_saml2/sspversion',
            get_string('sspversion', 'auth_saml2'),
            $authplugin->get_ssp_version()
            ));


    // Display locking / mapping of profile fields.
    $help = get_string('auth_updatelocal_expl', 'auth');
    $help .= get_string('auth_fieldlock_expl', 'auth');
    $help .= get_string('auth_updateremote_expl', 'auth');

    if (moodle_major_version() < '3.3') {
        auth_saml2_display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields, $help, true, true,
            $authplugin->get_custom_user_profile_fields());
    } else {
        display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields, $help, true, true,
            $authplugin->get_custom_user_profile_fields());
    }
}
