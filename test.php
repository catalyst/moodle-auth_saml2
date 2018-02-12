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

require('setup.php');

$idp = optional_param('idp', '', PARAM_RAW);
$logout = optional_param('logout', '', PARAM_RAW);
$idplogout = optional_param('idplogout', '', PARAM_RAW);

if (!empty($idp)) {
    $SESSION->saml2testidp = $idp;
}

if (!empty($logout)) {
    $SESSION->saml2testidp = $idplogout;
}

$passive = optional_param('passive', '', PARAM_RAW);
$passivefail = optional_param('passivefail', '', PARAM_RAW);
$trylogin = optional_param('login', '', PARAM_RAW);

if ($logout) {
    $urlparams = [
        'sesskey' => sesskey(),
        'auth' => $saml2auth->authtype,
    ];
    $url = new moodle_url('/auth/test_settings.php', $urlparams);
    $auth->logout(['ReturnTo' => $url->out(false)]);
}

$auth = new SimpleSAML\Auth\Simple($saml2auth->spname);

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
}

unset($SESSION->saml2testidp);
