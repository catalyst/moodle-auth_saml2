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

use auth_saml2\ssl_signing_algorithm;

defined('MOODLE_INTERNAL') || die();

global $saml2auth, $CFG, $SITE, $SESSION;

$config = [];

if (!empty($SESSION->saml2idp) && array_key_exists($SESSION->saml2idp, $saml2auth->metadataentities)) {
    $idpentityid = $saml2auth->metadataentities[$SESSION->saml2idp]->entityid;
} else {
    // Case for specifying no $SESSION IdP, select the first configured IdP as the default.
    $idpentityid = reset($saml2auth->metadataentities)->entityid;
}

$config[$saml2auth->spname] = [
    'saml:SP',
    'entityID' => !empty($saml2auth->config->entityid) ? $saml2auth->config->entityid : "$CFG->wwwroot/auth/saml2/sp/metadata.php",
    'discoURL' => !empty($CFG->auth_saml2_disco_url) ? $CFG->auth_saml2_disco_url : null,
    'idp' => empty($CFG->auth_saml2_disco_url) ? $idpentityid : null,
    'NameIDPolicy' => $saml2auth->config->nameidpolicy,
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
    'privatekey_pass' => get_config('auth_saml2', 'privatekeypass'),
    'certificate' => $saml2auth->spname . '.crt',
    'sign.logout' => true,
    'redirect.sign' => true,
    'signature.algorithm' => $saml2auth->config->signaturealgorithm,
    'WantAssertionsSigned' => $saml2auth->config->wantassertionssigned == 1,
    'acs.Bindings' => explode(',', $saml2auth->config->assertionsconsumerservices),
];

/*
 * If we're configured to expose the nameid as an attribute, set this authproc filter up
 * the nameid value appears under the attribute "nameid"
 */
if ($saml2auth->config->nameidasattrib) {
    $config[$saml2auth->spname]['authproc'] = array(
        20 => array(
            'class' => 'saml:NameIDAttribute',
            'format' => '%V',
        ),
    );
}
