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

global $saml2auth, $CFG, $SITE;

// Check for https login.
$wwwroot = $CFG->wwwroot;
if (!empty($CFG->loginhttps)) {
    $wwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
}

$config = [];

$idplist = $saml2auth->idplist;

// Adding the $saml2auth->spname to the list sources to create the SP metadata XML.
$idplist[] = new \auth_saml2\idpdata(null, $saml2auth->spname, null);

foreach ($idplist as $id => $idp) {

    // SP metadata check. We don't want to be using the md5($host).
    if ($idp->idpurl == $saml2auth->spname) {
        $source = $saml2auth->spname;
        $idp = $idplist[0];
    } else {
        // With multiple IdPs we will use the md5 hash to use the correct XML.
        $entitiyid = $saml2auth->idpentityids[$idp->idpurl];
        $source = md5($entitiyid);
    }

    $config[$source] = [
        'saml:SP',
        'entityID' => "$wwwroot/auth/saml2/sp/metadata.php",
        'idp' => $entitiyid,
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
}


