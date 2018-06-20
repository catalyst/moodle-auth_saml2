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
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../_autoload.php');

class auth_saml2_sitedata_test extends advanced_testcase {
    public function test_it_creates_the_directory_if_it_does_not_exist() {
        global $CFG;

        $expected = "{$CFG->dataroot}/saml2";
        self::assertFalse(file_exists($expected));

        /** @var auth_plugin_saml2 $saml2 */
        $saml2 = get_auth_plugin('saml2');
        self::assertTrue(file_exists($expected));

        rmdir($expected);
        $actual = $saml2->get_saml2_directory();
        self::assertTrue(file_exists($expected));

        self::assertSame($expected, $actual);
    }
}
