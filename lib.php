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
 * local lib
 *
 * @package    auth_saml2
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * A chance to validate form data, and last chance to
 * do stuff before it is inserted in config_plugin
 *
 * @param object $form with submitted configuration settings (without system magic quotes)
 * @param array $err array of error messages
 *
 * @return array of any errors
 */
function auth_saml2_update_idp_metadata() {
    global $CFG, $saml2auth;
    require_once('setup.php');
    error_log('foobar');
   // error_log(print_r($saml2auth['config'], true));
    
    return;

    // The IdP entityID needs to be parsed out of the XML.
    // It will use the first IdP entityID it finds.
    $form->entityid = '';
    $form->idpdefaultname = $this->defaults['idpdefaultname'];
    try {
        $rawxml = $form->idpmetadata;

        // If rawxml looks like a url, then go scrape it first.
        if (substr($rawxml, 0, 8) == 'https://' ||
                substr($rawxml, 0, 7) == 'http://') {
                    $rawxml = @file_get_contents($rawxml);

                    if (!$rawxml) {
                        $err['idpmetadata'] = get_string('idpmetadata_badurl', 'auth_saml2');
                        return;
                    }
                }

                $xml = new SimpleXMLElement($rawxml);
                $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
                $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

                // Find all IDPSSODescriptor elements and then work back up to the entityID.
                $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
                if ($idps && isset($idps[0])) {
                    $form->entityid = (string)$idps[0]->attributes('', true)->entityID[0];

                    $names = @$idps[0]->xpath('//mdui:DisplayName');
                    if ($names && isset($names[0])) {
                        $form->idpdefaultname = (string)$names[0];
                    }
                }

                if (empty($form->entityid)) {
                    $err['idpmetadata'] = get_string('idpmetadata_noentityid', 'auth_saml2');
                } else {
                    if (!file_exists($saml2auth->certdir)) {
                        mkdir($saml2auth->certdir);
                    }
                    file_put_contents($saml2auth->certdir . 'idp.xml' , $rawxml);
                }
    } catch (Exception $e) {
        $err['idpmetadata'] = get_string('idpmetadata_invalid', 'auth_saml2');
    }
}