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
 * Auth SAML2 metadata refresh scheduled task.
 *
 * @package    auth_saml2
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_saml2\task;

use auth_saml2\config;
use auth_saml2\metadata_fetcher;
use auth_saml2\metadata_parser;
use auth_saml2\metadata_writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Auth SAML2 metadata refresh scheduled task.
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadata_refresh extends \core\task\scheduled_task {

    /**
     * @var config
     */
    private $config;

    /**
     * @var metadata_fetcher
     */
    private $fetcher;

    /**
     * @var metadata_parser
     */
    private $parser;

    /**
     * @var metadata_writer
     */
    private $writer;

    public function get_name() {
        return get_string('taskmetadatarefresh', 'auth_saml2');
    }

    public function execute() {
        // Check config to see that the metadata is a URL, not XML. Return if not a URL.
        if (!$this->config instanceof config) {
            $this->config = new config();
        }
        if (substr($this->config->idpmetadata, 0, 8) != 'https://'
                && substr($this->config->idpmetadata, 0, 7) != 'http://') {
            // Not a link so nothing to refresh.
            mtrace('IDP metadata config not a URL, nothing to refresh.');
            return;
        }
        // Fetch the metadata.
        if (!$this->fetcher instanceof metadata_fetcher) {
            $this->fetcher = new metadata_fetcher();
        }

        try {
            $rawxml = $this->fetcher->fetch($this->config->idpmetadata);
        } catch (\coding_exception $e) {
            // Don't want the task to be rescheduled as a failure.
            mtrace('Metadata fetch failed.');
            return;
        }

        // Parse the metadata.
        if (!$this->parser instanceof metadata_parser) {
            $this->parser = new metadata_parser();
        }
        try {
            $this->parser->parse($rawxml);
        } catch (\coding_exception $e) {
            // Don't want the task to be rescheduled as a failure.
            mtrace('Metadata parsing failed.');
            return;
        }

        // Write the metadata to the correct location.
        if (!$this->writer instanceof metadata_writer) {
            $this->writer = new metadata_writer();
        }
        try {
            $this->writer->write('idp.xml', $rawxml);
        } catch (\coding_exception $e) {
            // Don't want the task to be rescheduled as a failure.
            mtrace('Metadata write failed.');
            return;
        }

        // Everything was successful. Update configs that may have changed.
        $cfgs = ['entityid' => $this->parser->get_entityid(), 'idpdefaultname' => $this->parser->get_idpdefaultname()];
        $this->config->update_configs($cfgs);
    }

    /**
     * @param config $config
     */
    public function set_config(config $config) {
        $this->config = $config;
    }

    /**
     * @param metadata_fetcher $fetcher
     */
    public function set_fetcher(metadata_fetcher $fetcher) {
        $this->fetcher = $fetcher;
    }

    /**
     * @param metadata_parser $parser
     */
    public function set_parser(metadata_parser $parser) {
        $this->parser = $parser;
    }

    /**
     * @param metadata_writer $writer
     */
    public function set_writer(metadata_writer $writer) {
        $this->writer = $writer;
    }
}