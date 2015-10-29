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
$string['entityid'] = 'IdP Entity ID';
$string['entityid_help'] = 'eg https://idp.example.com/';
$string['ssourl'] = 'Signin Service URL';
$string['ssourl_help'] = 'eg https://idp.example.com/SsoRedirect';
$string['slourl'] = 'Logout Service URL';
$string['slourl_help'] = 'eg https://idp.example.com/SloRedirect';
$string['certfingerprint'] = 'Certificate finger print';
$string['certfingerprint_help'] = 'eg a bunch of hexidecimal chars';
$string['debug'] = 'Debugging';
$string['debug_help'] = 'This adds extra debugging to the normal moodle log';
$string['spmetadata'] = 'SP Metdata';
$string['spmetadata_link'] = 'View Service Provider Metadata (xml)';
$string['spmetadata_help'] = 'You may need to give this to the IdP admin to whitelist you.';

