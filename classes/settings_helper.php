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


defined('MOODLE_INTERNAL') || die();

/**
 * Utility class for auth/saml2 settings.
 *
 * @package auth_saml2
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright   2019 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_helper {

    /**
     * @var string representation of PCRE Regular Expression for checking http/https scheme URLs
     * in accordance with RFC3986.
     */
    private static $httpsregex;

    /**
     * Setter for $http_https_regex.
     */
    protected static function set_https_regex() {
        // Build the regular expression for validating https/https URLs in accordance with RFC3986.
        self::$httpsregex = implode('', array(
            '/(^(https?\\:\\/\\/(www\\.)?',
            '[^\\.\\-\\s][\\-\\w\\d\\@\\:\\%\\.\\_\\+\\~\\#\\=\\(\\)]{0,256}',
            '\.[\\w\\-]{2,6}(?![\\.\\-\\s])',
            '([\\-\\w\\d\\@\\:\\%\\_\\+\\~\\#\\?\\&\\/\\=\\(\\)]*))$)',
            '|^(?![\\s\\S])/'));
    }

    /**
     * Getter for $http_https_regex.
     *
     * @return string representation of PCRE Regular Expression
     */
    public static function get_https_regex() {
        self::set_https_regex();
        return self::$httpsregex;
    }
}
