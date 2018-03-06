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

use auth_saml2\test\mock_settings;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../_autoload.php');

/**
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_settings_test extends advanced_testcase {
    public function test_mapping_moodle_options() {
        global $ADMIN;

        $this->resetAfterTest();

        $settings = new mock_settings();
        $ADMIN = (object)['fulltree' => true];
        require(__DIR__ . '/../../settings.php');

        $expected = ['username', 'idnumber', 'email', 'alternatename'];
        $actual = array_keys($settings->fields['id_s_auth_saml2_mdlattr']->choices);
        self::assertSame($expected, $actual);
    }
}
