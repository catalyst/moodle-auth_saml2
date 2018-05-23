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
 * IdP selection GUI.
 *
 * @package   auth_saml2
 * @author    Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require('setup.php');

$site = get_site();
$loginsite = get_string("loginsite");

$PAGE->set_title("$site->fullname: $loginsite");
$PAGE->set_heading("$site->fullname");
$PAGE->navbar->add($loginsite);
$PAGE->requires->css('/auth/saml2/styles.css');

$parentidp = optional_param('parentidp', '', PARAM_RAW);
$wants = optional_param('wants', '', PARAM_RAW);

$idpentityids = $saml2auth->idpentityids;
$idpmduinames = $saml2auth->idpmduinames;
$idpname = $saml2auth->config->idpname;
if (empty($idpname)) {
    $idpname = get_string('idpnamedefault', 'auth_saml2');
}

$activeidpentityids = [];

foreach ($idpentityids as $metadataentity => $subidps) {
    if ($parentidp == md5($metadataentity)) {
        $idpmduinames = (array)$idpmduinames[$metadataentity];

        foreach ((array)$subidps as $idpentity => $active) {
            if ((bool)$active) {
                $activeidpentityids[md5($idpentity)] = $idpmduinames[$idpentity];
            }
        }

        break;
    }
}

if (count($activeidpentityids) == 1) {
    reset($activeidpentityids);
    $idp = key($activeidpentityids);

    $params = [
        'wants' => $wants,
        'idp' => $idp,
    ];

    $idpurl = new moodle_url('/auth/saml2/login.php', $params);
    redirect($idpurl);
}

$data = [
    'idpentityids' => $activeidpentityids,
    'wants' => $wants,
    'idpname' => $idpname
];

$action = new moodle_url('/auth/saml2/selectidp.php');
$mform = new \auth_saml2\form\selectidp($action, $data);

if ($fromform = $mform->get_data()) {
    $idp = required_param('idp', PARAM_RAW);
    $wants = optional_param('wants', '', PARAM_RAW);
    $rememberidp = optional_param('rememberidp', '', PARAM_RAW);

    $params = [
        'wants' => $wants,
        'idp' => $idp,
        'rememberidp' => $rememberidp
    ];

    $loginurl = new moodle_url('/auth/saml2/login.php', $params);
    redirect($loginurl);
} else {
    $defaultidp = $saml2auth->get_idp_cookie();
    $rememberidp = $defaultidp !== '' ? 1 : 0;
    $mform->set_data(array(
        'idp' => $defaultidp,
        'rememberidp' => $rememberidp
    ));

    // Default is if rememberidp is on.
    $passive = (bool)optional_param('passive', $rememberidp, PARAM_BOOL);

    // If rememberidp is set and we are not returning from a passive attempt to login.
    if ($passive) {
        $errorurl = $PAGE->url;
        $errorurl->params(array('passive' => 0));

        $params = [
            'wants' => $wants,
            'idp' => $defaultidp,
            'passive' => 1,
            'errorurl' => $errorurl->out(false)
        ];
        $loginurl = new moodle_url('/auth/saml2/login.php', $params);
        redirect($loginurl);
    }
}

echo $OUTPUT->header();
echo "<div class=\"loginbox\">";
echo "<h2>Select a login service</h2>";
echo "<div class=\"subcontent\">";
$mform->display();
echo "</div>";
echo "</div>";
echo $OUTPUT->footer();
