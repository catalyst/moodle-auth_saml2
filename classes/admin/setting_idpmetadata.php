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

namespace auth_saml2\admin;

use admin_setting_configtextarea;
use auth_saml2\idp_data;
use auth_saml2\idp_parser;
use DOMDocument;
use DOMNodeList;
use DOMXPath;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("{$CFG->libdir}/adminlib.php");

/**
 * Class admin_setting_configtext_idpmetadata
 *
 * @package     auth_saml2
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_idpmetadata extends admin_setting_configtextarea {
    public function __construct() {
        // All parameters are hardcoded because there can be only one instance:
        // When it validates, it saves extra configs, preventing this component from being reused as is.
        parent::__construct(
            'auth_saml2/idpmetadata',
            get_string('idpmetadata', 'auth_saml2'),
            get_string('idpmetadata_help', 'auth_saml2'),
            '',
            PARAM_RAW,
            80,
            5);
    }

    /**
     * Validate data before storage
     *
     * @param string $value
     * @return true|string Error message in case of error, true otherwise.
     * @throws \coding_exception
     */
    public function validate($value) {
        $value = trim($value);
        if (empty($value)) {
            return true;
        }

        try {
            $idps = $this->get_idps_data($value);
            $this->process_all_idps_metadata($idps);
        } catch (setting_idpmetadata_exception $exception) {
            return $exception->getMessage();
        }

        return true;
    }

    /**
     * @param idp_data[] $idps
     */
    private function process_all_idps_metadata($idps) {
        $entityids = [];
        $mduinames = [];
        $mduilogos = [];

        foreach ($idps as $idp) {
            $this->process_idp_metadata($idp, $entityids, $mduinames, $mduilogos);
        }

        // If multiple IdPs are configured, force 'duallogin' to display the IdP links.
        if (count($idps) > 1) {
            set_config('duallogin', '1', 'auth_saml2');
        }

        // Encode arrays to be saved the config.
        set_config('idpentityids', json_encode($entityids), 'auth_saml2');
        set_config('idpmduinames', json_encode($mduinames), 'auth_saml2');
        set_config('idpmduilogos', json_encode($mduilogos), 'auth_saml2');
    }

    private function process_idp_metadata(idp_data $idp, &$entityids, &$mduinames, &$mduilogos) {
        $xpath = $this->get_idp_xml_path($idp);
        $idpelements = $this->find_all_idp_sso_descriptors($xpath);

        if ($idpelements->length == 1) {
            $this->process_idp_xml_with_single_idp($idp, $idpelements, $xpath, $entityids, $mduinames);
        } else if ($idpelements->length > 1) {
            $this->process_idp_xml_with_multiple_idps($idp, $idpelements, $xpath, $entityids, $mduinames, $mduilogos);
        }

        if (empty($entityids)) {
            throw new setting_idpmetadata_exception(get_string('idpmetadata_noentityid', 'auth_saml2'));
        }

        $this->save_idp_metadata_xml($entityids[$idp->idpurl], $idp->get_rawxml());
    }

    private function process_idp_xml_with_single_idp(idp_data $idp, DOMNodeList $idpelements,
                                                        DOMXPath $xpath, &$entityids, &$mduinames) {
        $entityids[$idp->idpurl] = $idpelements->item(0)->getAttribute('entityID');

        // Locate a displayname element provided by the IdP XML metadata.
        $names = $xpath->query('.//mdui:DisplayName', $idpelements->item(0));
        if ($names && $names->length > 0) {
            $mduinames[$idp->idpurl] = $names->item(0)->textContent;
        } else {
            $mduinames[$idp->idpurl] = get_string('idpnamedefault', 'auth_saml2');
        }
    }

    private function process_idp_xml_with_multiple_idps(idp_data $idp, DOMNodeList $idpelements,
                                                     DOMXPath $xpath, &$entityids, &$mduinames, &$mduilogos) {
        $oldentityids = json_decode(get_config('auth_saml2', 'idpentityids'), true);

        $entityids[$idp->idpurl] = [];
        $mduinames[$idp->idpurl] = [];
        $mduilogos[$idp->idpurl] = [];

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
            } else {
                $mduinames[$idp->idpurl][$entityid] = get_string('idpnamedefault', 'auth_saml2');
            }

            // Locate a displayname element provided by the IdP XML metadata.
            $logos = $xpath->query('.//mdui:Logo', $idpelement);
            if ($logos && $logos->length > 0) {
                $mduilogos[$idp->idpurl][$entityid] = $logos->item(0)->textContent;
            }
        }
    }

    /**
     * @param $value
     * @return idp_data[]
     */
    public function get_idps_data($value) {
        $parser = new idp_parser();
        $idps = $parser->parse($value);

        // Download the XML if it was not parsed from the ipdmetadata field.
        foreach ($idps as $idp) {
            if (!is_null($idp->get_rawxml())) {
                continue;
            }

            $rawxml = @file_get_contents($idp->idpurl);
            if ($rawxml === false) {
                throw new setting_idpmetadata_exception(
                    get_string('idpmetadata_badurl', 'auth_saml2', $idp->idpurl)
                );
            }
            $idp->set_rawxml($rawxml);
        }

        return $idps;
    }

    /**
     * @param idp_data $idp
     * @return
     */
    private function get_idp_xml_path(idp_data $idp) {
        $xml = new DOMDocument();
        if (!$xml->loadXML($idp->rawxml)) {
            throw new setting_idpmetadata_exception(get_string('idpmetadata_invalid', 'auth_saml2'));
        }

        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $xpath->registerNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        return $xpath;
    }

    /**
     * @param DOMXPath $xpath
     * @return DOMNodeList
     */
    private function find_all_idp_sso_descriptors(DOMXPath $xpath) {
        $idpelements = $xpath->query('//md:EntityDescriptor[//md:IDPSSODescriptor]');
        return $idpelements;
    }

    private function save_idp_metadata_xml($url, $xml) {
        global $CFG, $saml2auth;
        require_once("{$CFG->dirroot}/auth/saml2/setup.php");

        $file = $saml2auth->get_file_idp_metadata_file($url);
        file_put_contents($file, $xml);
    }
}
