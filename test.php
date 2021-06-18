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

// Require_login is not needed here.
// phpcs:disable moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');
require('setup.php');

// Check we are in debug mode to use this tool.
if (!$saml2auth->is_debugging()) {
    redirect('/');
}

if (!\auth_saml2\api::is_enabled()) {
    throw new \moodle_exception('plugindisabled', 'auth_saml2');
}

$idp = optional_param('idp', '', PARAM_TEXT);
$logout = optional_param('logout', '', PARAM_BOOL);
$idplogout = optional_param('idplogout', '', PARAM_RAW);

if (!empty($idp)) {
    $SESSION->saml2idp = $idp;
    echo "<p>Setting IdP via param</p>";
}

if (empty($SESSION->saml2idp)) {
    $arr = array_reverse($saml2auth->metadataentities);
    $metadataentities = array_pop($arr);
    $idpentity = array_pop($metadataentities);
    $idp = md5($idpentity->entityid);

    // Specify the default IdP to use.
    $SESSION->saml2idp = $idp;
    echo '<p>Setting IdP to default</p>';
}

if (!empty($logout)) {
    $SESSION->saml2idp = $idplogout;
}

$passive = optional_param('passive', '', PARAM_BOOL);
$passivefail = optional_param('passivefail', '', PARAM_BOOL);
$trylogin = optional_param('login', '', PARAM_BOOL);

echo '<p>SP name: ' . $saml2auth->spname;
echo '<p>Which IdP will be used? ' . s($SESSION->saml2idp);

$auth = new SimpleSAML\Auth\Simple($saml2auth->spname);

$idps = $saml2auth->metadataentities;

foreach ($idps as $entityid => $info) {

    $md5 = key($info);

    echo '<hr>';
    echo "<h4>IDP: $entityid</h4>";
    echo "<p>md5: $md5</p>";
    echo "<p>check: " . md5($entityid) . "</p>";

}

if ($logout) {
    $urlparams = [
        'sesskey' => sesskey(),
        'auth' => $saml2auth->authtype,
    ];
    $url = new moodle_url('/auth/saml2/test.php', $urlparams);
    $auth->logout(['ReturnTo' => $url->out(false)]);
}

if ($passive) {
    /* Prevent it from calling the missing post redirection. /auth/saml2/sp/module.php/core/postredirect.php */
    $auth->requireAuth(array(
        'KeepPost' => false,
        'isPassive' => true,
        'ErrorURL' => $CFG->wwwroot . '/auth/saml2/test.php?passivefail=1'
    ));
    echo "<p>Passive auth check:</p>";
    if (!$auth->isAuthenticated() ) {
        $attributes = $auth->getAttributes();
    } else {
        echo "You are not logged in";
    }

} else if (!$auth->isAuthenticated() && $trylogin) {

    $auth->requireAuth(array(
        'KeepPost' => false
    ));
    echo "Hello, authenticated user!";
    $attributes = $as->getAttributes();
    var_dump($attributes);
    echo 'IdP: ' . $auth->getAuthData('saml:sp:IdP');

} else if (!$auth->isAuthenticated()) {
    echo '<p>You are not logged in: <a href="?login=true">Login</a> | <a href="?passive=true">isPassive test</a></p>';
    if ($passivefail) {
        echo "Passive test worked, but not logged in";
    }
} else {
    echo 'Authed!';
    $attributes = $auth->getAttributes();
    echo '<pre>';
    var_dump($attributes);
    echo 'IdP: ' . $auth->getAuthData('saml:sp:IdP');
    echo '</pre>';
    echo '<p>You are logged in: <a href="?logout=true&idplogout=' . md5($auth->getAuthData('saml:sp:IdP')) . '">Logout</a></p>';
}

