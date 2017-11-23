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

$passive = optional_param('passive', '', PARAM_RAW);
$passivefail = optional_param('passivefail', '', PARAM_RAW);
$trylogin = optional_param('login', '', PARAM_RAW);

$auth = new SimpleSAML_Auth_Simple($saml2auth->spname);

if ($passive) {

    $auth->requireAuth(array(
        'isPassive' => true,
        'ErrorURL' => $CFG->wwwroot . '/auth/saml2/test.php?passivefail=1',
    ));
    echo "<p>Passive auth check:</p>";
    if (!$auth->isAuthenticated() ) {
        $attributes = $auth->getAttributes();
    } else {
        echo "You are not logged in";
    }

} else if (!$auth->isAuthenticated() && $trylogin) {

    $auth->requireAuth();
    echo "Hello, authenticated user!";
    $attributes = $as->getAttributes();
    var_dump($attributes);

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
    echo '</pre>';
}

