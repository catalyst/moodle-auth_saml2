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
 * A test GUI
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Require_login is not needed here.
// phpcs:disable moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

// Check we are in debug mode to use this tool.
$saml2auth = new \auth_saml2\auth();
if (!$saml2auth->is_debugging()) {
    redirect('/');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/auth/saml2/tester.php'));

if (!\auth_saml2\api::is_enabled()) {
    throw new \moodle_exception('plugindisabled', 'auth_saml2');
}

$idps = auth_saml2_get_idps(false, true);
$idpentityids = array();
foreach ($idps as $idpid => $idparray) {
    $idp = array_shift($idparray);
    $idpentityids[] = $idp['entityid'];
}

$action = new moodle_url('/auth/saml2/test.php');
$mform = new \auth_saml2\form\testidpselect($action, ['idpentityids' => $idpentityids]);
$mform->display();
