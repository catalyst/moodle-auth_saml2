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
 * SSP auth sources which inherits from Moodle config
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $saml2auth, $CFG, $SITE;

$config = array(
	$saml2auth->spname => array(
		'saml:SP',
        'entityID' => $CFG->wwwroot . '/auth/saml2/sp/metadata.php',
		'idp' => $saml2auth->config->entityid,
        'OrganizationName' => array(
            'en' => $SITE->fullname,
        ),
        'OrganizationURL' => array(
            'en' => $CFG->wwwroot,
        ),
	),
);

