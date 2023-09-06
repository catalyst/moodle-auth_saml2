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
namespace auth_saml2;

/**
 * IdP metadata parser class.
 *
 * @package   auth_saml2
 * @author    Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * @param string $data
     * @return \auth_saml2\idp_data[]
     */
    public function parse($data) {

        if ($this->check_xml($data)) {
            $this->parse_xml($data);
        } else {
            $this->parse_urls($data);

        }

        return $this->idps;
    }

    /**
     * Does the field *look* like xml, mostly?
     *
     * @param string $xml
     */
    public function check_xml($xml) {

        $declaration = '<?xml';

        $xml = trim($xml);
        if (substr($xml, 0, strlen($declaration)) === $declaration) {
            return true;
        }

        libxml_use_internal_errors(true);
        if (simplexml_load_string($xml)) {
            return true;
        }

        return false;
    }

    /**
     * Parse the xml
     *
     * @param string $xml
     */
    public function parse_xml($xml) {
        $singleidp = new \auth_saml2\idp_data(null, 'xml', null);
        $singleidp->set_rawxml(trim($xml));
        $this->idps[] = $singleidp;
    }

    /**
     * Parse the urls
     *
     * @param string $urls
     */
    public function parse_urls($urls) {
        // First split the contents based on newlines.
        $lines = preg_split('#\R#', $urls);

        foreach ($lines as $line) {
            $idpdata = null;
            $scheme = 'http';

            $line = trim($line);
            if (!$line) {
                continue;
            }

            // @COREHACK fix-parsing-url-with-url-in-parameter.
            // Separate out query strings from the URL prefix and build a line without query strings.
            $start = strpos($line, "http");
            if ($start === false) {
                $terms = array();
            } else {
                $terms = explode(" ", substr($line, $start));
                $line = substr($line, 0, $start);
            }

            $parameters = array();
            $clean = "";
            foreach ($terms as $term) {
                $p = strpos($term, "?");
                if ($p >= 1) {
                    $parameters[] = substr($term, $p);
                    $left = substr($term, 0, $p);
                } else {
                    $parameters[] = "";
                    $left = $term;
                }
                $clean .= $left . " ";
            }
            $line .= trim($clean);
            // END COREHACK fix-parsing-url-with-url-in-parameter.

            // Separate the line base on the scheme http. The scheme added back to the urls.
            $parts = array_map('rtrim', explode($scheme, $line));

            if (count($parts) === 3) {
                // With three elements I will assume that it was entered in the correct format.
                $idpname = $parts[0];
                $idpurl = $scheme . $parts[1];
                $idpicon = $scheme . $parts[2];

                // @COREHACK fix-parsing-url-with-url-in-parameter.
                if ($parameters[0]) {
                    $idpurl .= $parameters[0];
                }
                if ($parameters[1]) {
                    $idpicon .= $parameters[1];
                }
                // END COREHACK fix-parsing-url-with-url-in-parameter.

                $idpdata = new \auth_saml2\idp_data($idpname, $idpurl, $idpicon);

            } else if (count($parts) === 2) {
                // Two elements could either be a IdPName + IdPURL, or IdPURL + IdPIcon.

                // Detect if $parts[0] starts with a URL.
                if (substr($parts[0], 0, 8) === 'https://' ||
                    substr($parts[0], 0, 7) === 'http://') {
                    $idpurl = $scheme . $parts[1];
                    $idpicon = $scheme . $parts[2];

                    // @COREHACK fix-parsing-url-with-url-in-parameter.
                    if ($parameters[0]) {
                        $idpurl .= $parameters[0];
                    }
                    if ($parameters[1]) {
                        $idpicon .= $parameters[1];
                    }
                    // END COREHACK fix-parsing-url-with-url-in-parameter.

                    $idpdata = new \auth_saml2\idp_data(null, $idpurl, $idpicon);
                } else {
                    // We would then know that is a IdPName + IdPURL combo.
                    $idpname = $parts[0];
                    $idpurl = $scheme . $parts[1];

                    // @COREHACK fix-parsing-url-with-url-in-parameter.
                    if ($parameters[0]) {
                        $idpurl .= $parameters[0];
                    }
                    // END COREHACK fix-parsing-url-with-url-in-parameter.

                    $idpdata = new \auth_saml2\idp_data($idpname, $idpurl, null);
                }

            } else if (count($parts) === 1) {
                // One element is the previous default.
                $idpurl = $scheme . $parts[0];

                // @COREHACK fix-parsing-url-with-url-in-parameter.
                if ($parameters[0]) {
                    $idpurl .= $parameters[0];
                }
                // END COREHACK fix-parsing-url-with-url-in-parameter.

                $idpdata = new \auth_saml2\idp_data(null, $idpurl, null);
            }

            $this->idps[] = $idpdata;
        }
    }
}
