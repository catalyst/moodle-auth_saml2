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
 * @package     auth_saml2
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2;

use admin_setting_configtextarea;
use Exception;
use SimpleXMLElement;

defined('MOODLE_INTERNAL') || die();

/**
 * Class admin_setting_configtext_idpmetadata
 *
 * @package     auth_saml2
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_configtext_idpmetadata extends admin_setting_configtextarea {
    /**
     * Validate data before storage
     *
     * @param string $value
     * @return true|string Error message in case of error, true otherwise.
     * @throws \coding_exception
     */
    public function validate($value) {
        // Validate parent.
        $error = parent::validate($value);
        if ($error !== true) {
            return $error;
        }

        // Allow empty field to be processed.
        if (empty($value)) {
            return true;
        }

        global $saml2auth;
        require_once(__DIR__ . '/../setup.php');

        // Cleaning up potential newlines during a copy/paste.
        // The contents of the $form->idpmetadata textarea should be either,
        // 1. XML.
        // 2. A list of URLs.
        $idpmetadata = trim($value);

        $parser = new \auth_saml2\idp_parser();
        $idps = $parser->parse($idpmetadata);

        foreach ($idps as $idp) {
            // Download the XML if it was not parsed from the ipdmetadata field.
            if (empty($idp->get_rawxml())) {
                $rawxml = @file_get_contents($idp->idpurl);

                if (!$rawxml) {
                    return get_string('idpmetadata_badurl', 'auth_saml2');
                }
                $idp->set_rawxml($rawxml);
            }
        }

        $entityids = [];
        $mduinames = [];

        // Process the rawxml and populate arrays of entityids and mduinames.
        foreach ($idps as $idp) {
            try {
                $xml = new SimpleXMLElement($idp->rawxml);
                $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
                $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

                // Find all IDPSSODescriptor elements and then work back up to the entityID.
                $idpelements = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
                if ($idpelements && isset($idpelements[0])) {
                    $entityids[$idp->idpurl] = (string)$idpelements[0]->attributes('', true)->entityID[0];

                    // Locate a displayname element provided by the IdP XML metadata.
                    $names = @$idpelements[0]->xpath('//mdui:DisplayName');
                    if ($names && isset($names[0])) {
                        $mduinames[$idp->idpurl] = (string)$names[0];
                    }
                }

                if (empty($entityids)) {
                    return get_string('idpmetadata_noentityid', 'auth_saml2');
                } else {
                    if (!file_exists($saml2auth->certdir)) {
                        mkdir($saml2auth->certdir);
                    }

                    file_put_contents($saml2auth->certdir . md5($entityids[$idp->idpurl]) . '.idp.xml' , $idp->get_rawxml());
                }
            } catch (Exception $e) {
                return get_string('idpmetadata_invalid', 'auth_saml2');
            }
        }

        // If multiple IdPs are configured, force 'duallogin' to display the IdP links.
        if (count($idps) > 1) {
            set_config('duallogin', '1', 'auth_saml2');
        }

        // Encode arrays to be saved the config.
        set_config('idpentityids', json_encode($entityids), 'auth_saml2');
        set_config('idpmduinames', json_encode($mduinames), 'auth_saml2');

        return true;
    }
}
