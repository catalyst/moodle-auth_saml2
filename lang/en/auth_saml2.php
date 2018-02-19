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
 * Anobody can login using saml2
 *
 * @package   auth_saml2
 * @copyright Brendan Heywood <brendan@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['alterlogout'] = 'Alternative Logout URL';
$string['alterlogout_help'] = 'The URL to redirect a user after all internal logout mechanisms are run';
$string['anyauth'] = 'Allowed any auth type';
$string['anyauth_help'] = 'Yes: Allow SAML login for all users? No: Only users who have saml2 as their type.';
$string['auth_saml2description'] = 'Authenticate with a SAML2 IdP';
$string['autocreate'] = 'Auto create users';
$string['autocreate_help'] = 'If users are in the IdP but not in moodle create a moodle account.';
$string['certificatedetails'] = 'Certificate details';
$string['certificatedetailshelp'] = '<h1>SAML2 auto generated public certificate contents</h1><p>The path for the cert is here:</p>';
$string['certificate_help'] = 'Regenerate the Private Key and Certificate used by this SP. | <a href=\'{$a}\'>View SP certificate</a>';
$string['certificatelock_help'] = 'Locking the certificates will prevent them from being overwritten once generated.';
$string['certificatelock'] = 'Lock certificate';
$string['certificatelock_locked'] = 'The certificate is locked';
$string['certificatelock_warning'] = 'Warning. You are about to lock the certificates, are you sure you want to do this?';
$string['certificate'] = 'Regenerate certificate';
$string['commonname'] = 'Common Name';
$string['countryname'] = 'Country';
$string['debug'] = 'Debugging';
$string['debug_help'] = '<p>This adds extra debugging to the normal moodle log | <a href=\'{$a}\'>View SSP config</a></p>';
$string['duallogin'] = 'Dual login';
$string['duallogin_help'] = '
<p>If on, then users will see both manual and a SAML login button. If off they will always be taken directly to the IdP login page.</p>
<p>If passive, then the users that are already authenticated into the IDP will be automatically logged in, otherwise they will be sent to Moodle login page.</p>
<p>If off, then admins can still see the manual login page via /login/index.php?saml=off</p>
<p>If on, then external pages can deep link into moodle using saml eg /course/view.php?id=45&saml=on</p>';
$string['errorparsingxml'] = 'Error parsing XML: {$a}';
$string['exception'] = 'SAML2 exception: {$a}';
$string['expirydays'] = 'Expiry in Days';
$string['idpattr_help'] = 'Which IdP attribute should be matched against a Moodle user field?';
$string['idpattr'] = 'Mapping IdP';
$string['idpmetadata_badurl'] = 'The url didn\'t return any data';
$string['idpmetadata_help'] = 'To use multiple IdPs enter each public metadata url on a new line.<br/>To override a name, place text before the http. eg. "Forced IdP Name http://ssp.local/simplesaml/saml2/idp/metadata.php"';
$string['idpmetadata'] = 'IdP metadata xml OR public xml URL';
$string['idpmetadata_invalid'] = 'The IdP XML isn\'t valid';
$string['idpmetadata_noentityid'] = 'The IdP XML has no entityID';
$string['idpmetadatarefresh_help'] = 'Run a scheduled task to update IdP metadata from IdP metadata URL';
$string['idpmetadatarefresh'] = 'IdP metadata refresh';
$string['idpnamedefault'] = 'Login via SAML2';
$string['idpnamedefault_varaible'] = 'Login via SAML2 ({$a})';
$string['idpname_help'] = 'eg myUNI - this is detected from the metadata and will show on the dual login page (if enabled)';
$string['idpname'] = 'IdP label override';
$string['localityname'] = 'Locality';
$string['logdirdefault'] = '/tmp/';
$string['logdir_help'] = 'The log directory SSPHP will write to, the file will be named simplesamlphp.log';
$string['logdir'] = 'Log Directory';
$string['logtofile'] = 'Enable logging to file';
$string['logtofile_help'] = 'Turning this on will redirect SSPHP log output to a file in the logdir';
$string['mcrypt'] = 'Mcrypt library';
$string['mcryptnotfound'] = 'ERROR: The mcrypt php library is required and isn\'t installed. Please refer to:<br>
<a href="https://github.com/catalyst/moodle-auth_saml2#installation">https://github.com/catalyst/moodle-auth_saml2#installation</a>';
$string['mdlattr_help'] = 'Which Moodle user field should the IdP attribute be matched to?';
$string['mdlattr'] = 'Mapping Moodle';
$string['metadatafetchfailed'] = 'Metadata fetch failed: {$a}';
$string['metadatafetchfailedstatus'] = 'Metadata fetch failed: Status code {$a}';
$string['metadatafetchfailedunknown'] = 'Metadata fetch failed: Unknown cURL error';
$string['nameidasattrib'] = 'Expose NameID as attribute';
$string['nameidasattrib_help'] = 'The NameID claim will be exposed to SSPHP as an attribute named nameid';
$string['noattribute'] = 'You have logged in succesfully but we could not find your \'{$a}\' attribute to associate you to an account in Moodle.';
$string['nouser'] = 'You have logged in succesfully as \'{$a}\' but do not have an account in Moodle.';
$string['nullprivatecert'] = 'Creation of Private Certificate failed.';
$string['nullpubliccert'] = 'Creation of Public Certificate failed.';
$string['organizationalunitname'] = 'Organisational Unit';
$string['organizationname'] = 'Organisation';
$string['passivemode'] = 'Passive mode';
$string['pluginname'] = 'SAML2';
$string['regenerate_submit'] = 'Regenerate';
$string['required'] = 'This field is required';
$string['requireint'] = 'This field is required and needs to be a positive integer';
$string['showidplink'] = 'Display IdP link';
$string['showidplink_help'] = 'This will display the IdP link when the site is configured.';
$string['spmetadata_help'] = '<a href=\'{$a}\'>View Service Provider Metadata</a> | <a href=\'{$a}?download=1\'>Download SP Metadata</a>
<p>You may need to give this to the IdP admin to whitelist you.</p>';
$string['spmetadatasign_help'] = 'Sign the SP Metadata.';
$string['spmetadatasign'] = 'SP Metadata signature';
$string['spmetadata'] = 'SP Metadata';
$string['sspversion'] = 'SimpleSAMLphp version';
$string['stateorprovincename'] = 'State or Province';
$string['suspendeduser'] = 'You have logged in succesfully as \'{$a}\' but your account has been suspended in Moodle.';
$string['taskmetadatarefresh'] = 'Metadata refresh task';
$string['test_auth'] = '<a href="{$a}">Test isAuthenticated and login</a>';
$string['test_auth'] = '<a href="{$a}">Test isAuthenticated and login</a>';
$string['test_auth_button_login'] = 'IdP Login';
$string['test_auth_button_logout'] = 'IdP Logout';
$string['test_auth_str'] = 'Test isAuthenticated and login';
$string['test_passive'] = '<a href="{$a}">Test using isPassive</a>';
$string['test_passive_str'] = 'Test using isPassive';
$string['tolower_help'] = 'Apply lowercase to IdP attribute before matching?';
$string['tolower'] = 'Lowercase';
$string['wrongauth'] = 'You have logged in succesfully as \'{$a}\' but are not authorized to access Moodle.';
$string['auth_data_mapping'] = 'Data mapping';
$string['auth_fieldlockfield'] = 'Lock value ({$a})';
$string['auth_fieldmapping'] = 'Data mapping ({$a})';
$string['auth_fieldlock_expl'] = '<p><b>Lock value:</b> If enabled, will prevent Moodle users and admins from editing the field directly. Use this option if you are maintaining this data in the external auth system. </p>';
$string['auth_fieldlocks'] = 'Lock user fields';
$string['auth_updatelocalfield'] = 'Update local ({$a})';
$string['auth_updateremotefield'] = 'Update external ({$a})';
$string['cannotmapfield'] = 'Mapping collision detected - two fields maps to the same grade item {$a}';
$string['locked'] = 'Locked';
$string['unlocked'] = 'Unlocked';
$string['unlockedifempty'] = 'Unlocked if empty';
$string['update_never'] = 'Never';
$string['update_oncreate'] = 'On creation';
$string['update_onlogin'] = 'On every login';
$string['update_onupdate'] = 'On update';
$string['phone1'] = 'Phone';
$string['phone2'] = 'Mobile phone';
