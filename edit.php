<?php
// This file is part of SAML2 Authentication Plugin for Moodle
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
 * Edit IdP config settings and data mappings.
 *
 * @package     auth_saml2
 * @author      Jackson D'Souza <jackson.dsouza@catalyst-eu.net>
 * @copyright   2019 Catalyst IT Europe {@link http://www.catalyst-eu.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');
require_once('edit_form.php');

defined('MOODLE_INTERNAL') || die;

$id = optional_param('id', null, PARAM_INT);
$pagetitle = get_string('editidp', 'auth_saml2');

$pageparams = [];
if (isset($id)) {
    $pageparams['id'] = $id;
}

$idprecord = $DB->get_record('auth_saml2_idps', $pageparams, '*', MUST_EXIST);

$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/saml2/edit.php', $pageparams);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('plugins', 'admin'));
$PAGE->navbar->add(get_string('authentication', 'admin'));
$PAGE->navbar->add(get_string('pluginname', 'auth_saml2'),
        new moodle_url('/admin/settings.php', ['section' => 'authsettingsaml2']));
$PAGE->navbar->add(get_string('manageidpsheading', 'auth_saml2'), new moodle_url('/auth/saml2/availableidps.php'));
$PAGE->navbar->add($idprecord->displayname);
$PAGE->navbar->add($pagetitle);

require_login();
require_capability('moodle/site:config', context_system::instance());

$formparams['data'] = auth_saml2_get_idp_settings($id);
$formurl = new moodle_url($PAGE->url);
$mform = new auth_saml2_idp_edit_form($formurl, $formparams);

// Cancelled, return to main listing view.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/auth/saml2/availableidps.php'));
    exit;
} else if ($formdata = $mform->get_data()) {
    auth_saml2_save_idp($formdata, $id);
    redirect(new moodle_url('/auth/saml2/availableidps.php'));
    exit;
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
