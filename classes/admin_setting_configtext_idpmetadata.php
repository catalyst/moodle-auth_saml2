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

defined('MOODLE_INTERNAL') || die();

/**
 * Class admin_setting_configtext_idpmetadata
 *
 * @package     auth_saml2
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_configtext_idpmetadata extends admin_setting_configtextarea {
    public function __construct($name, $visiblename, $description, $defaultsetting = '') {
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_RAW, 80, 5);
    }

    /**
     * Validate data before storage
     *
     * @param string $value
     * @return true|string Error message in case of error, true otherwise.
     * @throws \coding_exception
     */
    public function validate($value) {
        // Allow empty field to be processed.
        if (empty($value)) {
            return true;
        }

        // Cleaning up potential newlines during a copy/paste.
        // The contents of the $form->idpmetadata textarea should be either,
        // 1. XML.
        // 2. A list of URLs.
        $idpmetadata = trim($value);

        $parser = new idp_parser();
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

        $oldentityids = json_decode(get_config('auth_saml2', 'idpentityids'), true);

        $entityids = [];
        $mduinames = [];

        // Process the rawxml and populate arrays of entityids and mduinames.
        foreach ($idps as $idp) {
            try {
                $xml = new \DOMDocument();
                $xml->loadXML($idp->rawxml);
                $xpath = new \DOMXPath($xml);
                $xpath->registerNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
                $xpath->registerNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

                // Find all IDPSSODescriptor elements and then work back up to the entityID.
                $idpelements = $xpath->query('//md:EntityDescriptor[//md:IDPSSODescriptor]');

                if ($idpelements && $idpelements->length == 1) {
                    $entityids[$idp->idpurl] = $idpelements->item(0)->getAttribute('entityID');

                    // Locate a displayname element provided by the IdP XML metadata.
                    $names = $xpath->query('.//mdui:DisplayName', $idpelements->item(0));
                    if ($names && $names->length > 0) {
                        $mduinames[$idp->idpurl] = $names->item(0)->textContent;
                    }
                } else if ($idpelements && $idpelements->length > 1) {
                    $entityids[$idp->idpurl] = [];
                    $mduinames[$idp->idpurl] = [];

                    foreach ($idpelements as $idpelement) {
                        $entityid = $idpelement->getAttribute('entityID');
                        $active = 0;
                        if (isset($oldentityids[$idp->idpurl][$entityid])) {
                            $active = $oldentityids[$idp->idpurl][$entityid];
                        }
                        $entityids[$idp->idpurl][$entityid] = $active;

                        // Locate a displayname element provided by the IdP XML metadata.
                        $names = $xpath->query('.//mdui:DisplayName', $idpelement);
                        if ($names && $names->length > 0) {
                            $mduinames[$idp->idpurl][$entityid] = $names->item(0)->textContent;
                        }
                    }
                }

                if (empty($entityids)) {
                    return get_string('idpmetadata_noentityid', 'auth_saml2');
                } else {
                    global $saml2auth;
                    require_once(__DIR__ . '/../setup.php');

                    if (!file_exists($saml2auth->certdir)) {
                        mkdir($saml2auth->certdir);
                    }

                    file_put_contents($saml2auth->certdir . md5($entityids[$idp->idpurl]) . '.idp.xml', $idp->get_rawxml());
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
