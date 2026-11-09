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
 * Manage available IdPs.
 *
 * @package   auth_saml2
 * @copyright 2026 University of Graz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$context = \context_system::instance();
$tableid = 'auth_saml2_idps';

$PAGE->set_url(new moodle_url('/auth/saml2/manageidps.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('manageidps', 'auth_saml2'));
$PAGE->set_heading(get_string('manageidpsheading', 'auth_saml2'));

echo $OUTPUT->header();

// Render CoreFilter UI for the IdPs filters.
$filterrenderable = new \auth_saml2\output\idps_filter($context, $tableid);
$filtercontext = $filterrenderable->export_for_template($OUTPUT);

// Wrap the filter UI in a form, as the template doc requires.
echo html_writer::start_tag('form', [
    'id' => 'auth_saml2_idps_filterform',
    'method' => 'get',
    'class' => 'mb-3',
]);
echo $OUTPUT->render_from_template('core/datafilter/filter', $filtercontext);
echo html_writer::end_tag('form');

// Render the dynamic flexible sql table itself.
$table = new \auth_saml2\table\idps_table($tableid);
$table->out(25, true);

// JS initialisation.
$PAGE->requires->js_call_amd('auth_saml2/manage_idps', 'init', [$tableid]);

echo $OUTPUT->footer();
