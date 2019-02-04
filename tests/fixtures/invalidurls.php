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
 * Fixture for testing settings_helper invalid urls.
 *
 * @package    auth_saml2
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$invalidurls = array(
    array("http://"),
    array("http://."),
    array("http://.."),
    array("http://../"),
    array("http://?"),
    array("http://??"),
    array("http://??/"),
    array("http://#"),
    array("http://##"),
    array("http://##/"),
    array("http://foo.bar?q=Spaces should be encoded"),
    array("//"),
    array("//a"),
    array("///a"),
    array("///"),
    array("http:///a"),
    array("foo.com"),
    array("rdar://1234"),
    array("h://test"),
    array("http:// shouldfail.com"),
    array(":// should fail"),
    array("http://foo.bar/foo(bar)baz quu"),
    array("ftps://foo.bar/"),
    array("http://-error-.invalid/"),
    array("http://-a.b.co"),
    array("http://a.b-."),
    array("http://0.0.0.0"),
    array("http://3628126748"),
    array("http://.www.foo.bar/"),
    array("http://www.foo.bar./"),
    array("http://.www.foo.bar."),
);
