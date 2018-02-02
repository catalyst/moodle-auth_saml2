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
 * Version information
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The current plugin version (Date: YYYYMMDDXX).
// New versions should be date code was changed. This is to keep the code ahead
// of the branch: MOODLE_UPTO32.
$plugin->version   = 2018020200;

$plugin->release   = 2018020200;    // Match release exactly to version.
$plugin->requires  = 2017051500;    // Requires this Moodle version.
$plugin->component = 'auth_saml2';  // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_STABLE;

