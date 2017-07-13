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
     */
    public function validate($value) {
        // Validate parent.
        $error = parent::validate($value);
        if ($error !== true) {
            return $error;
        }

        // If empty then that's ok
        if (trim($value) == '') {
            return true;
        }

        // If value looks like a url, then go scrape it first.
        if (substr($value, 0, 8) == 'https://' ||
            substr($value, 0, 7) == 'http://'
        ) {
            $value = @file_get_contents($value);
            if (!$value) {
                return get_string('idpmetadata_badurl', 'auth_saml2');
            }
        }

        try {
            return $this->validate_xml($value);
        } catch (Exception $e) {
            return get_string('idpmetadata_invalid', 'auth_saml2');
        }
    }

    private function validate_xml($rawxml) {
        global $saml2auth;

        $xml = new SimpleXMLElement($rawxml);
        $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $entityid = '';
        $idpdefaultname = $saml2auth->defaults['idpdefaultname'];

        // Find all IDPSSODescriptor elements and then work back up to the entityID.
        $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
        if ($idps && isset($idps[0])) {
            $entityid = (string)$idps[0]->attributes('', true)->entityID[0];

            $names = @$idps[0]->xpath('//mdui:DisplayName');
            if ($names && isset($names[0])) {
                $idpdefaultname = (string)$names[0];
            }
        }

        if (empty($entityid)) {
            return get_string('idpmetadata_noentityid', 'auth_saml2');
        }
        set_config('entityid', $entityid, 'auth_saml2');
        set_config('idpdefaultname', $idpdefaultname, 'auth_saml2');

        // Validated, create certificate.
        if (!file_exists($saml2auth->certdir)) {
            mkdir($saml2auth->certdir);
        }
        file_put_contents($saml2auth->certdir . 'idp.xml', $rawxml);
        return true;
    }
}
