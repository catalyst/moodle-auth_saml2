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
 * SSP auth sources which inherits from Moodle config
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $saml2auth, $CFG, $SITE, $SESSION;

// Check for https login.
$wwwroot = $CFG->wwwroot;
if (!empty($CFG->loginhttps)) {
    $wwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
}

$config = [];

$idp = $saml2auth->spname;

if (!empty($SESSION->saml2idp)) {
    foreach ($saml2auth->idpentityids as $idpentityid) {
        if ($SESSION->saml2idp === md5($idpentityid)) {
            $idp = $idpentityid;
            break;
        }
    }
}

$config[$saml2auth->spname] = [
    'saml:SP',
    'entityID' => "$wwwroot/auth/saml2/sp/metadata.php",
    'idp' => $idp,
    'NameIDPolicy' => null,
    'OrganizationName' => array(
        'en' => $SITE->shortname,
    ),
    'OrganizationDisplayName' => array(
        'en' => $SITE->fullname,
    ),
    'OrganizationURL' => array(
        'en' => $CFG->wwwroot,
    ),
    'privatekey' => $saml2auth->spname . '.pem',
    'privatekey_pass' => get_site_identifier(),
    'certificate' => $saml2auth->spname . '.crt',
    'sign.logout' => true,
    'redirect.sign' => true,
    'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
];
