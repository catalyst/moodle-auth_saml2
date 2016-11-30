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
 * Lock and unlock the Private Key and Certificate files
 *
 * @package    auth_saml2
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('setup.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$certfiles = array($saml2auth->certpem, $saml2auth->certcrt);

foreach ($certfiles as $certfile) {
    @chmod($certfile, 0440);
}

redirect(new moodle_url('/admin/auth_config.php?auth=saml2'));
