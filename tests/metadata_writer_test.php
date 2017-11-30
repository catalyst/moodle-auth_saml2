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
 * Testcase class for metadata_writer class.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use auth_saml2\metadata_writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Testcase class for metadata_writer class.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_metadata_writer_testcase extends basic_testcase {

    public function test_write_default_path() {
        global $CFG;

        $filename = 'idp.xml';
        $content = 'Test data';

        $writer = new metadata_writer();
        $writer->write($filename, $content);

        $this->assertEquals($content, file_get_contents("$CFG->dataroot/saml2/idp.xml"));
    }

    /**
     * @expectedException coding_exception
     */
    public function test_write_empty_filename() {
        $filename = '';
        $content = 'Test data';

        $writer = new metadata_writer();
        $writer->write($filename, $content);
    }

    public function test_write_non_dataroot_path() {
        global $CFG;

        $filename = 'idp.xml';
        $content = 'Test data';

        $nondatarootpath = '/temp/yada/blah/';

        $writer = new metadata_writer($nondatarootpath);
        $writer->write($filename, $content);

        $this->assertFileNotExists("/temp/yada/blah/idp.xml");
        $this->assertEquals($content, file_get_contents("$CFG->dataroot/saml2/idp.xml"));
    }

    public function test_write_trailing_slash() {
        global $CFG;

        $filename = 'idp.xml';
        $filename2 = 'idp2.xml';
        $content = 'Test data';
        $pathtrailingslash = "$CFG->dataroot/saml2/";
        $pathnotrailingslash = "$CFG->dataroot/saml2";

        $writer = new metadata_writer($pathtrailingslash);
        $writer->write($filename, $content);

        $writer2 = new metadata_writer($pathnotrailingslash);
        $writer2->write($filename2, $content);

        $this->assertFileExists($pathtrailingslash . $filename);
        $this->assertFileExists($pathnotrailingslash . '/' . $filename2);
    }
}