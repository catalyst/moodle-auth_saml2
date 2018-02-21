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
use auth_saml2\idp_parser;

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

    /**
     * @var idp_parser
     */
    private $idpparser;

    public function get_name() {
        return get_string('taskmetadatarefresh', 'auth_saml2');
    }

    public function execute($force = false) {
        $config = get_config('auth_saml2');
        if (!$force && empty($config->idpmetadatarefresh)) {
            $str = 'IdP metadata refresh is not configured. Enable it in the auth settings or disable this scheduled task';
            mtrace($str);
            return false;
        }

        if (!$this->idpparser instanceof idp_parser) {
            $this->idpparser = new idp_parser();
        }

        if ($this->idpparser->check_xml($config->idpmetadata) == true) {
            mtrace('IdP metadata config not a URL, nothing to refresh.');
            return false;
        }

        // Parse the URLs that are in the IdP metadata config.
        $idps = $this->idpparser->parse($config->idpmetadata);

        $entityids = [];
        $mduinames = [];

        foreach ($idps as $idp) {
            // Fetch the metadata.
            if (!$this->fetcher instanceof metadata_fetcher) {
                $this->fetcher = new metadata_fetcher();
            }
            $rawxml = $this->fetcher->fetch($idp->idpurl);

            // Parse the metadata.
            if (!$this->parser instanceof metadata_parser) {
                $this->parser = new metadata_parser();
            }
            $this->parser->parse($rawxml);

            $entityid = $this->parser->get_entityid();
            if (empty($entityid)) {
                mtrace(get_string('idpmetadata_noentityid', 'auth_saml2'));
                return false;
            }

            $idpdefaultname = $this->parser->get_idpdefaultname();
            if (empty($idpdefaultname)) {
                $idpdefaultname = get_string('idpnamedefault', 'auth_saml2');
            }

            // Write the metadata to the correct location.
            if (!$this->writer instanceof metadata_writer) {
                $this->writer = new metadata_writer();
            }

            $entityids[$idp->idpurl] = $entityid;
            $mduinames[$idp->idpurl] = $idpdefaultname;

            $filename = md5($entityids[$idp->idpurl]) . '.idp.xml';
            $this->writer->write($filename, $rawxml);
        }

        // Everything was successful. Update configs that may have changed.
        set_config('idpentityids', json_encode($entityids), 'auth_saml2');
        set_config('idpmduinames', json_encode($mduinames), 'auth_saml2');

        mtrace('IdP metadata refresh completed successfully.');
        return true;
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
