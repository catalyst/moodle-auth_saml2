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
use auth_saml2\user_fields;

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/auth/saml2/locallib.php');

    $sections = [
        'idpsettings',
        'spsettings',
        'usersettings',
        'logoutsettings',
        'groupsettings',
        'debugsettings',
    ];
    $toc = '<ol>';
    foreach ($sections as $key => $section) {
        $toc .= '<li>';
        $toc .= '<a href="#:~:text=' . ($key + 1) . '. ' . get_string($section, 'auth_saml2');
        $toc .= '">';
        $toc .= get_string($section, 'auth_saml2');
        $toc .= '</a>';
    }
    $toc .= '</ol>';
    $settings->add(new admin_setting_heading('samltoc', 'SAML settings', $toc));

    // -----------------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('samlidp', '1. ' . get_string('idpsettings', 'auth_saml2'), ''));
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


    // -----------------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('samluser', '2. ' . get_string('spsettings', 'auth_saml2'), ''));

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

    $settings->add(new admin_setting_configpasswordunmask(
        'auth_saml2/privatekeypass',
        get_string('privatekeypass', 'auth_saml2'),
        get_string('privatekeypass_help', 'auth_saml2'),
        get_site_identifier(),
        PARAM_TEXT));

    // SP Metadata.
    $settings->add(new setting_textonly(
           'auth_saml2/spmetadata',
           get_string('spmetadata', 'auth_saml2'),
           get_string('spmetadata_help', 'auth_saml2', $CFG->wwwroot . '/auth/saml2/sp/metadata.php')
           ));

    // SP Metadata signature.
    $spmetadatasign = new admin_setting_configselect(
            'auth_saml2/spmetadatasign',
            get_string('spmetadatasign', 'auth_saml2'),
            get_string('spmetadatasign_help', 'auth_saml2'),
            0, $yesno);
    $spmetadatasign->set_updatedcallback('auth_saml2_update_sp_metadata');
    $settings->add($spmetadatasign);

    $entityid = new admin_setting_configtext(
        'auth_saml2/spentityid',
        get_string('spentityid', 'auth_saml2'),
        get_string('spentityid_help', 'auth_saml2'),
        ''
    );
    $entityid->set_updatedcallback('auth_saml2_update_sp_metadata');
    $settings->add($entityid);

    $wantassertionssigned = new admin_setting_configselect(
        'auth_saml2/wantassertionssigned',
        get_string('wantassertionssigned', 'auth_saml2'),
        get_string('wantassertionssigned_help', 'auth_saml2'),
        0, $yesno
    );
    $wantassertionssigned->set_updatedcallback('auth_saml2_update_sp_metadata');
    $settings->add($wantassertionssigned);

    $assertionsconsumerservices = [
        'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST' => 'HTTP Post',
        'urn:oasis:names:tc:SAML:1.0:profiles:browser-post' => 'Browser post profile',
        'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact' => 'HTTP Artifact',
        'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01' => 'Artifact 01 profile',
        'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser' => 'Holder-of-Key Web Browser SSO',
    ];

    $acssetting = new admin_setting_configmultiselect(
        'auth_saml2/assertionsconsumerservices',
        get_string('assertionsconsumerservices', 'auth_saml2'),
        get_string('assertionsconsumerservices_help', 'auth_saml2'),
        array(),
        $assertionsconsumerservices
    );
    $acssetting->set_updatedcallback('auth_saml2_update_sp_metadata');
    $settings->add($acssetting);

    $settings->add(new admin_setting_configselect(
        'auth_saml2/allowcreate',
        get_string('allowcreate', 'auth_saml2'),
        get_string('allowcreate_help', 'auth_saml2'),
        0, $yesno
    ));

    $settings->add(new admin_setting_configtext(
        'auth_saml2/authncontext',
        get_string('authncontext', 'auth_saml2'),
        get_string('authncontext_help', 'auth_saml2'),
        '', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configselect(
        'auth_saml2/signaturealgorithm',
        get_string('signaturealgorithm', 'auth_saml2'),
        get_string('signaturealgorithm_help', 'auth_saml2'),
        ssl_algorithms::get_default_saml_signature_algorithm(),
        ssl_algorithms::get_valid_saml_signature_algorithms()));

    // -----------------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('samlusersettings', '3. ' . get_string('usersettings', 'auth_saml2'), ''));

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
    $nameidpolicy = new admin_setting_configselect(
        'auth_saml2/nameidpolicy',
        get_string('nameidpolicy', 'auth_saml2'),
        get_string('nameidpolicy_help', 'auth_saml2'),
        'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        array_combine($nameidlist, $nameidlist));
    $nameidpolicy->set_updatedcallback('auth_saml2_update_sp_metadata');
    $settings->add($nameidpolicy);


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

    // Auto login.
    $autologinoptions = [
        saml2_settings::OPTION_AUTO_LOGIN_NO => get_string('no'),
        saml2_settings::OPTION_AUTO_LOGIN_SESSION => get_string('autologinbysession', 'auth_saml2'),
        saml2_settings::OPTION_AUTO_LOGIN_COOKIE => get_string('autologinbycookie', 'auth_saml2'),
    ];
    $settings->add(new admin_setting_configselect(
            'auth_saml2/autologin',
            get_string('autologin', 'auth_saml2'),
            get_string('autologin_help', 'auth_saml2'),
            saml2_settings::OPTION_AUTO_LOGIN_NO,
            $autologinoptions));
    $settings->add(new admin_setting_configtext(
            'auth_saml2/autologincookie',
            get_string('autologincookie', 'auth_saml2'),
            get_string('autologincookie_help', 'auth_saml2'),
            '', PARAM_TEXT));

    // Allow any auth type.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/anyauth',
            get_string('anyauth', 'auth_saml2'),
            get_string('anyauth_help', 'auth_saml2'),
            0, $yesno));

    // Simplify attributes.
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
    $settings->add(new admin_setting_configselect(
            'auth_saml2/mdlattr',
            get_string('mdlattr', 'auth_saml2'),
            get_string('mdlattr_help', 'auth_saml2'),
            'username', user_fields::get_supported_fields()));

    // Lowercase.
    $toloweroptions = [
        saml2_settings::OPTION_TOLOWER_EXACT => get_string('tolower:exact', 'auth_saml2'),
        saml2_settings::OPTION_TOLOWER_LOWER_CASE => get_string('tolower:lowercase', 'auth_saml2'),
        saml2_settings::OPTION_TOLOWER_CASE_INSENSITIVE => get_string('tolower:caseinsensitive', 'auth_saml2'),
        saml2_settings::OPTION_TOLOWER_CASE_AND_ACCENT_INSENSITIVE => get_string('tolower:caseandaccentinsensitive', 'auth_saml2'),
    ];
    $settings->add(new admin_setting_configselect(
            'auth_saml2/tolower',
            get_string('tolower', 'auth_saml2'),
            get_string('tolower_help', 'auth_saml2'),
            saml2_settings::OPTION_TOLOWER_EXACT,
            $toloweroptions));

    // Autocreate Users.
    $settings->add(new admin_setting_configselect(
            'auth_saml2/autocreate',
            get_string('autocreate', 'auth_saml2'),
            get_string('autocreate_help', 'auth_saml2'),
            0, $yesno));

    // Requested Attributes.
    $settings->add(new admin_setting_configtextarea(
        'auth_saml2/requestedattributes',
        get_string('requestedattributes', 'auth_saml2'),
        get_string('requestedattributes_help', 'auth_saml2', ['example' => "<pre>
eduPersonPrincipalName urn:mace:dir:attribute-def:eduPersonPrincipalName
urn:mace:dir:attribute-def:mail *</pre>"]),
        '',
        PARAM_TEXT));

    // Formats for request attributes.
    $settings->add(new admin_setting_configtext(
            'auth_saml2/requestedattributesformat',
            get_string('requestedattributesformat', 'auth_saml2'),
            get_string('requestedattributesformat_help', 'auth_saml2'),
            'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'));

    // -----------------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('samllogoutsettings', '4. ' . get_string('logoutsettings', 'auth_saml2'), ''));

    // Alternative Logout URL.
    $settings->add(new admin_setting_configtext(
            'auth_saml2/alterlogout',
            get_string('alterlogout', 'auth_saml2'),
            get_string('alterlogout_help', 'auth_saml2'),
            '',
            PARAM_URL));

    // Attempt Single Sign out.
    $settings->add(new admin_setting_configselect(
        'auth_saml2/attemptsignout',
        get_string('attemptsignout', 'auth_saml2'),
        get_string('attemptsignout_help', 'auth_saml2'),
        1,
        $yesno));

    // -----------------------------------------------------------------------------------------------------
    // User block and redirect feature setting section.
    $settings->add(new admin_setting_heading('auth_saml2/groupsettings', '5. ' . get_string('groupsettings', 'auth_saml2'),
        new lang_string('auth_saml2blockredirectdescription', 'auth_saml2')));

    // Group access rules.
    $settings->add(new admin_setting_configtextarea(
        'auth_saml2/grouprules',
        get_string('grouprules', 'auth_saml2'),
        get_string('grouprules_help', 'auth_saml2'),
        '',
        PARAM_TEXT));

    // Flagged login response options.
    $flaggedloginresponseoptions = [
        saml2_settings::OPTION_FLAGGED_LOGIN_MESSAGE => get_string('flaggedresponsetypemessage', 'auth_saml2'),
        saml2_settings::OPTION_FLAGGED_LOGIN_REDIRECT => get_string('flaggedresponsetyperedirect', 'auth_saml2')
    ];

    // Flagged login response options selector.
    $settings->add(new admin_setting_configselect(
        'auth_saml2/flagresponsetype',
        get_string('flagresponsetype', 'auth_saml2'),
        get_string('flagresponsetype_help', 'auth_saml2'),
        saml2_settings::OPTION_FLAGGED_LOGIN_REDIRECT,
        $flaggedloginresponseoptions));


    // Set the http OR https fully qualified scheme domain name redirect destination for flagged accounts.
    $settings->add(new admin_setting_configtext(
        'auth_saml2/flagredirecturl',
        get_string('flagredirecturl', 'auth_saml2'),
        get_string('flagredirecturl_help', 'auth_saml2'),
        '',
        PARAM_URL));

    // Set the displayed message for flagged accounts.
    $settings->add(new admin_setting_configtextarea(
        'auth_saml2/flagmessage',
        get_string('flagmessage', 'auth_saml2'),
        get_string('flagmessage_help', 'auth_saml2'),
        get_string('flagmessage_default', 'auth_saml2'),
        PARAM_TEXT,
        50,
        3));

    // -----------------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('samldebugsettings', '6. ' . get_string('debugsettings', 'auth_saml2'), ''));

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

    // SAMLPHP version.
    $authplugin = get_auth_plugin('saml2');
    $settings->add(new setting_textonly(
            'auth_saml2/sspversion',
            get_string('sspversion', 'auth_saml2'),
            $authplugin->get_ssp_version()
            ));

    // -----------------------------------------------------------------------------------------------------

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

    // The field delimiter to use for multiple value fields from IdP.
    $settings->add(new admin_setting_configtext(
            'auth_saml2/fielddelimiter',
            get_string('fielddelimiter', 'auth_saml2'),
            get_string('fielddelimiter_help', 'auth_saml2'),
            ',',
            PARAM_TEXT,
            5));
}
