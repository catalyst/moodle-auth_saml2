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
 * IdP metadata parser class.
 *
 * @package   auth_saml2
 * @author    Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_saml2;

defined('MOODLE_INTERNAL') || die();

class idp_parser {
    /**
     * @var array
     */
    private $idps = [];

    /**
     * Parse the idpmetadata field if names / URLs are detected.
     *
     * Example lines may be:
     * "IdP Name https://idpurl https://idpicon"
     * "IdP Name https://idpurl"
     * "https://idpurl https://idpicon"
     * "https://idpurl"
     *
     * @param $data
     * @return \auth_saml2\idpdata[]
     */
    public function parse($data) {
        // Check if the form data is a possible XML chunk.
        if (strpos($data, '<?xml') === 0) {
            // The URL for a single XML blob is used as an index to obtain the EntityID.
            $singleidp = new \auth_saml2\idpdata(null, 'xml', null);
            $singleidp->set_rawxml(trim($data));
            $this->idps[] = $singleidp;

        } else {
            // First split the contents based on newlines.
            $lines = preg_split('#\R#', $data);

            foreach ($lines as $line) {
                $idpdata = null;
                $scheme = 'http';

                // Separate the line base on the scheme http. The scheme added back to the urls.
                $parts = array_map('rtrim', explode($scheme, $line));

                if (count($parts) === 3) {
                    // With three elements I will assume that it was entered in the correct format.
                    $idpname = $parts[0];
                    $idpurl = $scheme .$parts[1];
                    $idpicon = $scheme. $parts[2];

                    $idpdata = new \auth_saml2\idpdata($idpname, $idpurl, $idpicon);

                } else if (count($parts) === 2) {
                    // Two elements could either be a IdPName + IdPURL, or IdPURL + IdPIcon.

                    // Detect if $parts[0] starts with a URL.
                    if (substr($parts[0], 0, 8) === 'https://' ||
                        substr($parts[0], 0, 7) === 'http://') {
                        $idpurl = $scheme .$parts[1];
                        $idpicon = $scheme. $parts[2];

                        $idpdata = new \auth_saml2\idpdata(null, $idpurl, $idpicon);
                    } else {
                        // We would then know that is a IdPName + IdPURL combo.
                        $idpname = $parts[0];
                        $idpurl = $scheme .$parts[1];

                        $idpdata = new \auth_saml2\idpdata($idpname, $idpurl, null);
                    }

                } else if (count($parts) === 1) {
                    // One element is the previous default.
                    $idpurl = $scheme . $parts[0];
                    $idpdata = new \auth_saml2\idpdata(null, $idpurl, null);
                }

                $this->idps[] = $idpdata;
            }
        }

        return $this->idps;
    }
}
