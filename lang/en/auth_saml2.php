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

$string['pluginname'] = 'SAML2';
$string['auth_saml2description'] = 'Authenticate with a SAML2 IdP';
$string['autocreate'] = 'Auto create users';
$string['autocreate_help'] = 'If users are in the IdP but not in moodle create a moodle account.';
$string['idpname'] = 'IdP label override';
$string['idpnamedefault'] = 'Login via SAML2';
$string['idpname_help'] = 'eg myUNI - this is detected from the metadata and will show on the dual login page (if enabled)';
$string['idpmetadata'] = 'IdP metadata xml OR public xml URL';
$string['idpmetadata_help'] = 'eg XML containing an EntityDescriptor element';
$string['idpmetadata_invalid'] = 'The IdP XML isn\'t valid';
$string['idpmetadata_noentityid'] = 'The IdP XML has no entityID';
$string['idpmetadata_badurl'] = 'The url didn\'t return any data';
$string['debug'] = 'Debugging';
$string['debug_help'] = '<p>This adds extra debugging to the normal moodle log | <a href=\'{$a}\'>View SSP config</a></p>';
$string['spmetadata'] = 'SP Metadata';
$string['spmetadata_help'] = '<a href=\'{$a}\'>View Service Provider Metadata</a> | <a href=\'{$a}?download=1\'>Download SP Metadata</a>
<p>You may need to give this to the IdP admin to whitelist you.</p>';
$string['spmetadatasign'] = 'SP Metadata signature';
$string['spmetadatasign_help'] = 'Sign the SP Metadata.';
$string['showidplink'] = 'Display IdP link';
$string['showidplink_help'] = 'This will display the IdP link when the site is configured.';
$string['duallogin'] = 'Dual login';
$string['duallogin_help'] = '
<p>If on, then users will see both manual and a SAML login button. If off they will always be taken directly to the IdP login page.</p>
<p>If off, then admins can still see the manual login page via /login/index.php?saml=off</p>
<p>If on, then external pages can deep link into moodle using saml eg /course/view.php?id=45&saml=on</p>
';
$string['anyauth'] = 'Allowed any auth type';
$string['anyauth_help'] = 'Yes: Allow SAML login for all users? No: Only users who have saml2 as their type.';
$string['wrongauth'] = 'You have logged in succesfully as \'{$a}\' but are not authorized to access Moodle.';
$string['nouser'] = 'You have logged in succesfully as \'{$a}\' but do not have an account in Moodle.';
$string['suspendeduser'] = 'You have logged in succesfully as \'{$a}\' but your account has been suspended in Moodle.';
$string['noattribute'] = 'You have logged in succesfully but we could not find your \'{$a}\' attribute to associate you to an account in Moodle.';
$string['tolower'] = 'Lowercase';
$string['tolower_help'] = 'Apply lowercase to IdP attribute before matching?';
$string['mapping'] = 'IdP to Moodle mapping';
$string['mapping_help'] = 'What attribute in the IdP should match which field in Moodle?';
$string['nullpubliccert'] = 'Creation of Public Certificate failed.';
$string['nullprivatecert'] = 'Creation of Private Certificate failed.';
$string['certificate'] = 'Regenerate certificate';
$string['certificate_help'] = 'Regenerate the Private Key and Certificate used by this SP. | <a href=\'{$a}\'>View SP certificate</a>';
$string['certificatelock'] = 'Lock certificate';
$string['certificatelock_help'] = 'Locking the certificates will prevent them from being overwritten once generated.';
$string['certificatelock_locked'] = 'The certificate is locked';
$string['certificatelock_warning'] = 'Warning. You are about to lock the certificates, are you sure you want to do this?';
$string['certificatedetails'] = 'Certificate details';
$string['certificatedetailshelp'] = '<h1>SAML2 auto generated public certificate contents</h1>
<p>The path for the cert is here:</p>
';
$string['countryname'] = 'Country';
$string['stateorprovincename'] = 'State or Province';
$string['localityname'] = 'Locality';
$string['organizationname'] = 'Organisation';
$string['organizationalunitname'] = 'Organisational Unit';
$string['commonname'] = 'Common Name';
$string['expirydays'] = 'Expiry in Days';
$string['required'] = 'This field is required';
$string['requireint'] = 'This field is required and needs to be a positive integer';
$string['regenerate_submit'] = 'Regenerate';
$string['test_passive'] = '<a href="{$a}">Test using isPassive</a>';
$string['test_auth'] = '<a href="{$a}">Test isAuthenticated and login</a>';

