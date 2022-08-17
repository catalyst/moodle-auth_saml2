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
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2\admin;

/**
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class saml2_settings {
    const OPTION_DUAL_LOGIN_NO = 0;

    const OPTION_DUAL_LOGIN_YES = 1;

    const OPTION_DUAL_LOGIN_PASSIVE = 2;

    const OPTION_MULTI_IDP_DISPLAY_DROPDOWN = 0;

    const OPTION_MULTI_IDP_DISPLAY_BUTTONS = 1;

    const OPTION_FLAGGED_LOGIN_MESSAGE = 1;

    const OPTION_FLAGGED_LOGIN_REDIRECT = 2;

    const OPTION_AUTO_LOGIN_NO = 0;

    const OPTION_AUTO_LOGIN_SESSION = 1;

    const OPTION_AUTO_LOGIN_COOKIE = 2;

    const OPTION_TOLOWER_EXACT = 0;

    const OPTION_TOLOWER_LOWER_CASE = 1;

    const OPTION_TOLOWER_CASE_INSENSITIVE = 2;

    const OPTION_TOLOWER_CASE_AND_ACCENT_INSENSITIVE = 3;
}
