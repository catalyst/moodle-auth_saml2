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
 * External functions for auth saml2.
 *
 * @copyright University of Graz 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'auth_saml2_manage_idp' => [
        'classname'    => auth_saml2\external\manage_idp::class,
        'methodname'   => 'execute',
        'classpath'    => 'auth/saml2/classes/external/manage_idps.php',
        'description'  => 'Update a single or multiple of the IdP (displayname, active, defaultidp).',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'moodle/site:config',
    ],
];

$services = [];
