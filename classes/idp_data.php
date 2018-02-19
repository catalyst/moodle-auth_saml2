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

defined('MOODLE_INTERNAL') || die();

/**
 * IdP data class.
 *
 * @package    auth_saml2
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class idp_data {
    public $idpname;

    public $idpurl;

    public $idpicon;

    public $rawxml;

    /**
     * idp_data constructor.
     *
     * @param $idpname
     * @param $idpicon
     * @param $idpurl
     */
    public function __construct($idpname, $idpurl, $idpicon) {
        $this->idpname = $idpname;
        $this->idpurl = $idpurl;
        $this->idpicon = $idpicon;
        $this->rawxml = null;
    }

    public function get_rawxml() {
        return $this->rawxml;
    }

    public function set_rawxml($rawxml) {
        $this->rawxml = $rawxml;
    }
}
