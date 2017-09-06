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
 * Test page for SAML
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../setup.php');

// First setup the PATH_INFO because that's how SSP rolls.
$_SERVER['PATH_INFO'] = '/' . $saml2auth->spname;

try {
    require('../extlib/simplesamlphp/modules/saml/www/sp/saml2-logout.php');
} catch (Exception $e) {
    // TODO SSPHP uses Exceptions for handling valid conditions, so a succesful
    // logout is an Exception. This is a workaround to just go back to the home
    // page but we should probably handle SimpleSAML_Error_Error similar to how
    // extlib/simplesamlphp/www/_include.php handles it.
    redirect(new moodle_url('/'));
}

