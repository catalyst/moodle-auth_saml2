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

use auth_saml2\admin\setting_idpmetadata;
use auth_saml2\idp_data;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../_autoload.php');

class setting_idpmetadata_test extends advanced_testcase {
    /** @var setting_idpmetadata */
    private $config;

    protected function setUp() {
        parent::setUp();
        $this->config = new setting_idpmetadata('name', 'visible', 'description');
    }

    public function test_it_allows_empty_values() {
        self::assertTrue($this->config->validate(''), 'Validate empty string.');
        self::assertTrue($this->config->validate('  '), ' Should trim spaces.');
        self::assertTrue($this->config->validate("\n \n"), 'Should trim newlines.');
    }

    public function test_it_gets_idp_data_for_xml() {
        $xml = file_get_contents(__DIR__ . '/../fixtures/metadata.xml');

        /** @var idp_data $data */
        $data = $this->config->get_idps_data($xml);

        self::assertCount(1, $data);
        $data = $data[0];
        self::assertInstanceOf(idp_data::class, $data);
        self::assertNotNull($data->rawxml);
    }
}
