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
 * Page to select which IdPs to display if a metadata xml contains multiple.
 *
 * @package   auth_saml2
 * @author    Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());
$PAGE->set_url("$CFG->wwwroot/auth/saml2/avilableidps.php");
$PAGE->set_course($SITE);

$idpentityids = json_decode(get_config('auth_saml2', 'idpentityids'), true);
$idpmduinames = json_decode(get_config('auth_saml2', 'idpmduinames'), true);

$data = [
    'idpentityids' => $idpentityids,
    'idpmduinames' => $idpmduinames
];

$action = new moodle_url('/auth/saml2/availableidps.php');
$mform = new \auth_saml2\form\availableidps($action, $data);

if ($mform->is_cancelled()) {
    redirect("$CFG->wwwroot/admin/settings.php?section=authsettingsaml2");
}

if ($fromform = $mform->get_data()) {
    // Go through each metadata group and override the idpentities.
    // We don't overrride the whole lot because metadata entries with only 1 IdP entity won't be in the form.
    foreach ($fromform->values as $metadata => $idpentityvalues) {
        $idpentityids[$metadata] = $idpentityvalues;
    }

    set_config('idpentityids', json_encode($idpentityids), 'auth_saml2');
} else {
    $mform->set_data(array('values' => $idpentityids));
}

echo $OUTPUT->header();
echo "<h1>Select available IdPs</h1>";
$mform->display();
echo $OUTPUT->footer();
