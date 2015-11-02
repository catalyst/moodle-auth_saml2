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
 * Authenticate using an embeded SimpleSamlPhp instance
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * @package   auth_saml2
 * @copyright Brendan Heywood <brendan@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $saml2auth;
$cfg = $saml2auth->config;
$metadata[$cfg->entityid] = array(
    'SingleSignOnService'   => $cfg->ssourl,
    'SingleLogoutService'   => $cfg->slourl,
    'certFingerprint'       => $cfg->certfingerprint,
    'saml2.relaxvalidation' => array('noattributestatement')
);

