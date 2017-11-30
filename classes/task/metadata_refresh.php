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
        $config = get_config('auth/saml2');
        if (empty($config->idpmetadatarefresh)) {
            $str = 'IdP metadata refresh is not configured. Enable it in the auth settings or disable this scheduled task';
            mtrace($str);
            return;
        }
        if (substr($config->idpmetadata, 0, 8) != 'https://'
                && substr($config->idpmetadata, 0, 7) != 'http://') {
            // Not a link so nothing to refresh.
            mtrace('IdP metadata config not a URL, nothing to refresh.');
            return;
        }
        // Fetch the metadata.
        if (!$this->fetcher instanceof metadata_fetcher) {
            $this->fetcher = new metadata_fetcher();
        }
        $rawxml = $this->fetcher->fetch($config->idpmetadata);

        // Parse the metadata.
        if (!$this->parser instanceof metadata_parser) {
            $this->parser = new metadata_parser();
        }
        $this->parser->parse($rawxml);

        $entityid = $this->parser->get_entityid();
        if (empty($entityid)) {
            mtrace(get_string('idpmetadata_noentityid', 'auth_saml2'));
            return;
        }

        $idpdefaultname = $this->parser->get_idpdefaultname();
        if (empty($idpdefaultname)) {
            $idpdefaultname = get_string('idpnamedefault', 'auth_saml2');
        }

        // Write the metadata to the correct location.
        if (!$this->writer instanceof metadata_writer) {
            $this->writer = new metadata_writer();
        }
        $this->writer->write('idp.xml', $rawxml);

        // Everything was successful. Update configs that may have changed.
        set_config('entityid', $entityid, 'auth/saml2');
        set_config('idpdefaultname', $idpdefaultname, 'auth/saml2');

        mtrace('IdP metadata refresh completed successfully.');
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