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
 * @package auth_saml2
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Catalyst IT
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Check if we have the saml=on param set. If so, disable guest access and force the user to log in with saml.
 *
 * @since  Moodle 3.8
 * @return void
 */
function auth_saml2_after_config() {
    global $CFG;
    try {
        $saml = optional_param('saml', null, PARAM_BOOL);
        if ($saml == 1) {
            if (isguestuser()) {
                // We want to force users to log in with a real account, so log guest users out.
                require_logout();
            }
            // We have the saml=on param set. Disable guest access (in memory -
            // not saved in database) to force the login with saml for this request.
            unset($CFG->autologinguests);
        }
    } catch (\Exception $exception) {
        debugging('auth_saml2_after_config error', DEBUG_DEVELOPER, $exception->getTrace());
    }
}

/**
 * Callback immediately after require_login succeeds.
 *
 * This callback requires Moodle 3.7+. On earlier versions this will not run. It also won't run
 * on pages which don't call require_login, so we use the _before_http_headers() callback too.
 *
 * @since Moodle 3.7
 */
function auth_saml2_after_require_login() {
    \auth_saml2\auto_login::process();
}

/**
 * Callback before HTTP headers are sent.
 *
 * This is called on every page.
 */
function auth_saml2_before_http_headers() {
    \auth_saml2\auto_login::process();
}
