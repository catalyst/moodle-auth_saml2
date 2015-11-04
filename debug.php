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
 * Service provider metadata
 *
 * Unfortunately this file inside SSP couldn't be customized in any clean
 * way so it has been copied here and forked. The main differences are
 * the config lookup, but also using the proxy SP module urls.
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('setup.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$config = SimpleSAML_Configuration::getInstance();

$PAGE->set_url("$CFG->httpswwwroot/auth/saml2/debug.php");
$PAGE->set_course($SITE);
echo $OUTPUT->header();
echo '<pre>' . print_r($config,1) . '</pre>';
echo $OUTPUT->footer();

