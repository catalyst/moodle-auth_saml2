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
 * Testcase class for metadata_refresh task class.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use auth_saml2\task\metadata_refresh;

defined('MOODLE_INTERNAL') || die();

/**
 * Testcase class for metadata_refresh task class.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_metadata_refresh_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
    }

    public function test_metadata_refresh_disabled() {
        set_config('idpmetadatarefresh', 0, 'auth_saml2');

        $refreshtask = new metadata_refresh();

        $this->expectOutputString('IdP metadata refresh is not configured. Enable it in the auth settings or disable' .
                ' this scheduled task' . "\n");
        $refreshtask->execute();
    }

    public function test_metadata_refresh_idpmetadata_non_url() {
        $randomxml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<somexml>yada</somexml>
XML;
        set_config('idpmetadatarefresh', 1, 'auth_saml2');
        set_config('idpmetadata', $randomxml, 'auth_saml2');

        $refreshtask = new metadata_refresh();

        $this->expectOutputString('IdP metadata config not a URL, nothing to refresh.' . "\n");
        $refreshtask->execute();
    }

    /**
     * @expectedException \moodle_exception
     */
    public function test_metadata_refresh_fetch_fails() {
        if (!method_exists($this, 'prophesize')) {
            $this->markTestSkipped('Skipping due to Prophecy library not available');
        }

        set_config('idpmetadatarefresh', 1, 'auth_saml2');
        set_config('idpmetadata', 'http://somefakeidpurl.local', 'auth_saml2');
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_fetcher($fetcher->reveal());

        $fetcher->fetch('http://somefakeidpurl.local')->willThrow(new \moodle_exception('metadatafetchfailed', 'auth_saml2'));
        $refreshtask->execute();
    }

    /**
     * @expectedException \moodle_exception
     */
    public function test_metadata_refresh_parse_fails() {
        if (!method_exists($this, 'prophesize')) {
            $this->markTestSkipped('Skipping due to Prophecy library not available');
        }

        set_config('idpmetadatarefresh', 1, 'auth_saml2');
        set_config('idpmetadata', 'http://somefakeidpurl.local', 'auth_saml2');
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');
        $parser = $this->prophesize('auth_saml2\metadata_parser');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_parser($parser->reveal());

        $fetcher->fetch('http://somefakeidpurl.local')->willReturn('doesnotmatter');
        $parser->parse('doesnotmatter')->willThrow(new \moodle_exception('errorparsingxml', 'auth_saml2', '', 'error'));
        $refreshtask->execute();
    }

    public function test_metadata_refresh_parse_no_entityid() {
        if (!method_exists($this, 'prophesize')) {
            $this->markTestSkipped('Skipping due to Prophecy library not available');
        }

        set_config('idpmetadatarefresh', 1, 'auth_saml2');
        set_config('idpmetadata', 'http://somefakeidpurl.local', 'auth_saml2');
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');
        $parser = $this->prophesize('auth_saml2\metadata_parser');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_parser($parser->reveal());

        $fetcher->fetch('http://somefakeidpurl.local')->willReturn('doesnotmatter');
        $parser->parse('doesnotmatter')->willReturn(null);
        $parser->get_entityid()->willReturn('');
        $this->expectOutputString(get_string('idpmetadata_noentityid', 'auth_saml2') . "\n");
        $refreshtask->execute();
    }

    public function test_metadata_refresh_parse_no_idpname() {
        if (!method_exists($this, 'prophesize')) {
            $this->markTestSkipped('Skipping due to Prophecy library not available');
        }

        set_config('idpmetadatarefresh', 1, 'auth_saml2');
        set_config('idpmetadata', 'http://somefakeidpurl.local', 'auth_saml2');
        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');
        $parser = $this->prophesize('auth_saml2\metadata_parser');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_parser($parser->reveal());

        $fetcher->fetch('http://somefakeidpurl.local')->willReturn('doesnotmatter');
        $parser->parse('doesnotmatter')->willReturn(null);
        $parser->get_entityid()->willReturn('someentityid');
        $parser->get_idpdefaultname()->willReturn('');
        $refreshtask->execute();

        $idpmduinames = (array) json_decode(get_config('auth_saml2', 'idpmduinames'));
        $this->assertEquals(get_string('idpnamedefault', 'auth_saml2'), $idpmduinames['http://somefakeidpurl.local']);
    }

    /**
     * @expectedException \coding_exception
     */
    public function test_metadata_refresh_write_fails() {
        if (!method_exists($this, 'prophesize')) {
            $this->markTestSkipped('Skipping due to Prophecy library not available');
        }

        $this->setExpectedExceptionFromAnnotation();

        set_config('idpmetadatarefresh', 1, 'auth_saml2');
        set_config('idpmetadata', 'http://somefakeidpurl.local', 'auth_saml2');

        $fetcher = $this->prophesize('auth_saml2\metadata_fetcher');
        $parser = $this->prophesize('auth_saml2\metadata_parser');
        $writer = $this->prophesize('auth_saml2\metadata_writer');

        $refreshtask = new metadata_refresh();
        $refreshtask->set_fetcher($fetcher->reveal());
        $refreshtask->set_parser($parser->reveal());
        $refreshtask->set_writer($writer->reveal());

        $fetcher->fetch('http://somefakeidpurl.local')->willReturn('somexml');
        $parser->parse('somexml')->willReturn(null);
        $parser->get_entityid()->willReturn('Some id');
        $parser->get_idpdefaultname()->willReturn('Default name');
        $md5 = md5('Some id');
        $writer->write($md5 . '.idp.xml', 'somexml')->willThrow(new coding_exception('Metadata write failed: some error'));
        $refreshtask->execute();
    }
}
