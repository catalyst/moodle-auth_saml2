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
 * Testcase class for metadata_fetcher class.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use auth_saml2\metadata_fetcher;

defined('MOODLE_INTERNAL') || die();

/**
 * Testcase class for metadata_fetcher class.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_metadata_fetcher_testcase extends advanced_testcase {

    public function test_fetch_metadata_404() {
        $url = $this->getExternalTestFileUrl('/test404.xml');
        $fetcher = new metadata_fetcher();

        try {
            $fetcher->fetch($url);
            // Fail if the exception is not thrown.
            $this->fail();
        } catch (\moodle_exception $e) {
            $this->assertEquals(404, (int) $fetcher->get_curlinfo()['http_code']);
        }
    }

    public function test_fetch_metadata_success() {
        $url = $this->getExternalTestFileUrl('/test.html');
        $fetcher = new metadata_fetcher();

        $result = $fetcher->fetch($url);
        $this->assertNotEmpty($result);
        $this->assertEquals(0, (int) $fetcher->get_curlerrorno());
        $this->assertEquals(200, (int) $fetcher->get_curlinfo()['http_code']);
    }

    public function test_fetch_metadata_curlerrorno() {
        $url = 'http://fakeurl.localhost';
        $curl = $this->prophesize('curl');

        $fetcher = new metadata_fetcher();
        $curl->get($url, Prophecy\Argument::type('array'))->willReturn('some bad stuff');
        $curl->get_errno()->willReturn(CURLE_READ_ERROR);
        $curl->get_info()->willReturn(['http_status' => 503]);

        try {
            $fetcher->fetch($url, $curl->reveal());
            // Fail if the exception is not thrown.
            $this->fail();
        } catch (\moodle_exception $e) {
            $this->assertEquals(CURLE_READ_ERROR, (int) $fetcher->get_curlerrorno());
            $this->assertContains('Metadata fetch failed: some bad stuff', $e->getMessage());
            $this->assertEquals('some bad stuff', $fetcher->get_curlerror());
        }
    }

    public function test_fetch_metadata_nohttpstatus() {
        $url = 'http://fakeurl.localhost';
        $curl = $this->prophesize('curl');

        $fetcher = new metadata_fetcher();
        $curl->get($url, Prophecy\Argument::type('array'))->willReturn('');
        $curl->get_info()->willReturn([]);
        $curl->get_errno()->willReturn(0);

        try {
            $fetcher->fetch($url, $curl->reveal());
            // Fail if the exception is not thrown.
            $this->fail();
        } catch (\moodle_exception $e) {
            $this->assertContains('Metadata fetch failed: Unknown cURL error', $e->getMessage());
        }
    }
}