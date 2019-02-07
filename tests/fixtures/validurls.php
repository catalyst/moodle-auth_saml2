<?php
// This file is part of SAML2 Authentication Plugin
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
 * Fixture for testing settings_helper valid urls.
 *
 * @package    auth_saml2
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$validurls =array(
    array("http://foo.com/blah_blah"),
    array("http://foo.com/blah_blah/"),
    array("http://foo.com/blah_blah_(wikipedia)"),
    array("http://foo.com/blah_blah_(wikipedia)_(again)"),
    array("http://www.example.com/wpstyle/?p=364"),
    array("https://www.example.com/foo/?bar=baz&#038;inga=42&#038;quux"),
    array("http://userid:password@example.com:8080"),
    array("http://userid:password@example.com:8080/"),
    array("http://userid@example.com"),
    array("http://userid@example.com/"),
    array("http://userid@example.com:8080"),
    array("http://userid@example.com:8080/"),
    array("http://userid:password@example.com"),
    array("http://userid:password@example.com/"),
    array("http://foo.com/blah_(wikipedia)#cite-1"),
    array("http://foo.com/blah_(wikipedia)_blah#cite-1"),
    array("http://foo.com/(something)?after=parens"),
    array("http://code.google.com/events/#&#038;product=browser"),
    array("http://j.mp"),
    array("https://foo.bar/baz"),
    array("http://foo.bar/?q=Test%20URL-encoded%20stuff"),
    array("http://1337.net"),
    array("http://a.b-c.de"),
    array("http://223.255.255.254"),
    array("http://xn--nw2a.xn--j6w193g/"),
    array("http://foo.com/blah_blah"),
    array(""),
);