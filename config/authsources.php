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

defined('MOODLE_INTERNAL') || die();

global $saml2auth, $CFG, $SITE;

// Check for https login.
$wwwroot = $CFG->wwwroot;
if (!empty($CFG->loginhttps)) {
    $wwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
}

$config = array(
    $saml2auth->spname => array(
        'saml:SP',
        'entityID' => "$wwwroot/auth/saml2/sp/metadata.php",
        'discoURL' => !empty($CFG->auth_saml2_disco_url) ? $CFG->auth_saml2_disco_url : null,
        'idp' => empty($CFG->auth_saml2_disco_url) ? $saml2auth->config->entityid : null,
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
    ),
);
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
