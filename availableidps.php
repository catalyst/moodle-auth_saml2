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
require_once(__DIR__ . '/locallib.php');

global $DB;

require_login();
require_capability('moodle/site:config', context_system::instance());

$heading = get_string('manageidpsheading', 'auth_saml2');

$PAGE->set_url("$CFG->wwwroot/auth/saml2/avilableidps.php");
$PAGE->set_course($SITE);
$PAGE->set_title($SITE->shortname . ': ' . $heading);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('plugins', 'admin'));
$PAGE->navbar->add(get_string('authentication', 'admin'));
$PAGE->navbar->add(get_string('pluginname', 'auth_saml2'),
        new moodle_url('/admin/settings.php', array('section' => 'authsettingsaml2')));
$PAGE->navbar->add($heading);

$PAGE->requires->css('/auth/saml2/styles.css');

$metadataentities = auth_saml2_get_idps(false, true);

$data = [
    'metadataentities' => $metadataentities
];

$action = new moodle_url('/auth/saml2/availableidps.php');
$mform = new \auth_saml2\form\availableidps($action, $data);

if ($mform->is_cancelled()) {
    redirect("$CFG->wwwroot/admin/settings.php?section=authsettingsaml2");
}

if ($fromform = $mform->get_data()) {
    // Go through each idp and update its flags.
    foreach ($fromform->metadataentities as $idpentities) {
        foreach ($idpentities as $idpentity) {
            $DB->update_record('auth_saml2_idps', (object) $idpentity);
        }
    }
} else {
    $mform->set_data(array('metadataentities' => $metadataentities));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
echo get_string('multiidpinfo', 'auth_saml2');
$mform->display();
echo $OUTPUT->footer();
