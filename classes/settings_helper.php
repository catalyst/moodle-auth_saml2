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
 * Admin settings helper.
 *
 * @package    auth_saml2
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2;

use auth_saml2\admin\saml2_settings;
/**
 * Utility class for auth/saml2 settings
 * @package auth_saml2
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_helper {

    private static $http_https_regex;

    /**
     * Direct initiation not allowed, utility class
     */
    protected function __construct() {
    }

    protected static function set_http_https_regex() {
        self::$http_https_regex = saml2_settings::SETTINGS_REGEXP_HTTP_HTTPS_URL;
    }

    public static function get_http_https_regex() {
        self::set_http_https_regex();
        return self::$http_https_regex;
    }
}