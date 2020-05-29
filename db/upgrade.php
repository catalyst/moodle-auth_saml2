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
 * LDAP authentication plugin upgrade code
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_saml2\task\metadata_refresh;
use auth_saml2\ssl_algorithms;

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_saml2_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016031701) {

        // Define table auth_saml2_vkstore to be created.
        $table = new xmldb_table('auth_samltwo_kvstore');

        // Adding fields to table auth_saml2_vkstore.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('k', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('expire', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table auth_saml2_vkstore.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table auth_saml2_vkstore.
        $table->add_index('key_type', XMLDB_INDEX_UNIQUE, array('k', 'type'));

        // Conditionally launch create table for auth_saml2_vkstore.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Remove legacy tables not created by moodle.
        $table = new xmldb_table('auth_saml_tableVersion');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $table = new xmldb_table('auth_saml_kvstore');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Saml2 savepoint reached.
        upgrade_plugin_savepoint(true, 2016031701, 'auth', 'saml2');
    }

    if ($oldversion < 2016080302) {
        // Update plugin configuration settings from auth_saml2 to auth/saml2.
        $currentconfig = get_config('auth_saml2');

        // Remove old config.
        $rs = $DB->get_recordset_select('config_plugins', 'plugin = ?', array('auth_saml2'));
        foreach ($rs as $record) {
            if ($record->name != 'version') {
                $DB->delete_records('config_plugins', array('id' => $record->id));
            }
        }
        $rs->close();

        // Set new config.
        foreach ($currentconfig as $key => $value) {
            set_config($key, $value, 'auth/saml2');
        }

        // Saml2 savepoint reached.
        upgrade_plugin_savepoint(true, 2016080302, 'auth', 'saml2');
    }

    if ($oldversion < 2017051800) {
        // Update plugin configuration settings from auth/saml2 to auth_saml2.
        $currentconfig = (array)get_config('auth_saml2');
        $oldconfig = $DB->get_records('config_plugins', ['plugin' => 'auth/saml2']);

        // Convert old config items to new.
        foreach ($oldconfig as $item) {
            $DB->delete_records('config_plugins', array('id' => $item->id));
            set_config($item->name, $item->value, 'auth_saml2');
        }

        // Overwrite with any config that was created in the new format.
        foreach ($currentconfig as $key => $value) {
            set_config($key, $value, 'auth_saml2');
        }

        // Saml2 savepoint reached.
        upgrade_plugin_savepoint(true, 2017051800, 'auth', 'saml2');
    }

    // Depending on the path from the previous version branch, we may need to run this again.
    if ($oldversion < 2018021900) {
        // Update plugin configuration settings from auth/saml2 to auth_saml2.
        $currentconfig = (array)get_config('auth_saml2');
        $oldconfig = $DB->get_records('config_plugins', ['plugin' => 'auth/saml2']);

        // Convert old config items to new.
        foreach ($oldconfig as $item) {
            $DB->delete_records('config_plugins', array('id' => $item->id));
            set_config($item->name, $item->value, 'auth_saml2');
        }

        // Overwrite with any config that was created in the new format.
        foreach ($currentconfig as $key => $value) {
            set_config($key, $value, 'auth_saml2');
        }

        // Saml2 savepoint reached.
        upgrade_plugin_savepoint(true, 2018021900, 'auth', 'saml2');
    }

    if ($oldversion < 2018021901) {
        /* Multiple IdP support
         * sitedata/saml2/idp.xml is now sitedata/saml2/md5($entityid).idp.xml
         */

        $xmlfile = $CFG->dataroot . "/saml2/idp.xml";
        $entityids = [];
        $mduinames = [];

        $parser = new \auth_saml2\idp_parser();
        $idpmetadata = get_config('auth_saml2', 'idpmetadata');
        $idps = $parser->parse($idpmetadata);

        // If the content is not xml, provide the idp name for the built array.
        if (isset($idps[0]) && empty($idps[0]->rawxml)) {
            $type = $idps[0]->idpurl;
        } else {
            $type = 'xml';
        }

        if (file_exists($xmlfile)) {
            $rawxml = file_get_contents($xmlfile);

            $xml = new SimpleXMLElement($rawxml);
            $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');

            // Find all IDPSSODescriptor elements and then work back up to the entityID.
            $idpelements = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
            if ($idpelements && isset($idpelements[0])) {
                $entityid = (string)$idpelements[0]->attributes('', true)->entityID[0];
                $entityids[$type] = $entityid;
                rename($xmlfile, $CFG->dataroot . "/saml2/" . md5($entityid) . ".idp.xml");

                // Locate a displayname element provided by the IdP XML metadata.
                $names = @$idpelements[0]->xpath('//mdui:DisplayName');
                if ($names && isset($names[0])) {
                    $mduinames[$type] = (string)$names[0];
                }
            }
        }

        set_config('idpentityids', json_encode($entityids), 'auth_saml2');
        set_config('idpmduinames', json_encode($mduinames), 'auth_saml2');

        // Saml2 savepoint reached.
        upgrade_plugin_savepoint(true, 2018021901, 'auth', 'saml2');
    }

    if ($oldversion < 2018022203) {
        try {
            $refreshtask = new metadata_refresh();
            $refreshtask->execute(true);
        } catch (moodle_exception $exception) {
            mtrace($exception->getMessage());
        }
        upgrade_plugin_savepoint(true, 2018022203, 'auth', 'saml2');
    }

    if ($oldversion < 2018030800) {
        $table = new xmldb_table('auth_samltwo_kvstore');
        $dbman->rename_table($table, 'auth_saml2_kvstore');
        upgrade_plugin_savepoint(true, 2018030800, 'auth', 'saml2');
    }

    if ($oldversion < 2018071100) {
        set_config('signaturealgorithm', ssl_algorithms::get_default_saml_signature_algorithm(), 'auth_saml2');
        upgrade_plugin_savepoint(true, 2018071100, 'auth', 'saml2');
    }

    if ($oldversion < 2019022100) {

        // Define table auth_saml2_idps to be created.
        $tablename = 'auth_saml2_idps';
        $table = new xmldb_table($tablename);

        // Adding fields to table auth_saml2_idps.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('metadataurl', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('entityid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activeidp', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('defaultidp', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('adminidp', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('defaultname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('displayname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('logo', XMLDB_TYPE_TEXT, 'big', null, null, null, null);
        $table->add_field('alias', XMLDB_TYPE_CHAR, '50', null, null, null, null);

        // Adding keys to table auth_saml2_idps.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for auth_saml2_idps.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

            $idpentityids = json_decode(get_config('auth_saml2', 'idpentityids'), true);
            $idpmduinames = json_decode(get_config('auth_saml2', 'idpmduinames'), true);
            $idpmduilogos = json_decode(get_config('auth_saml2', 'idpmduilogos'), true);

            foreach ($idpentityids as $metadatakey => $idpentityid) {
                if (is_array($idpentityid)) {
                    foreach ($idpentityid as $singleidpentityid => $active) {
                        $idpobject = new stdClass();

                        $idpobject->metadataurl = $metadatakey;
                        $idpobject->entityid = $singleidpentityid;
                        $idpobject->activeidp = $active;
                        $idpobject->defaultidp = 0;
                        $idpobject->adminidp = 0;

                        if (isset($idpmduinames[$metadatakey][$singleidpentityid])) {
                            $idpobject->name = $idpmduinames[$metadatakey][$singleidpentityid];
                        }
                        if (isset($idpmduilogos[$metadatakey][$singleidpentityid])) {
                            $idpobject->logo = $idpmduilogos[$metadatakey][$singleidpentityid];
                        }

                        $DB->insert_record($tablename, $idpobject);
                    }
                } else {
                    $idpobject = new stdClass();

                    $idpobject->metadataurl = $metadatakey;
                    $idpobject->entityid = $metadatakey;
                    $idpobject->activeidp = 1;
                    $idpobject->defaultidp = 0;
                    $idpobject->adminidp = 0;

                    if (isset($idpmduinames[$metadatakey])) {
                        $idpobject->name = $idpmduinames[$metadatakey];
                    }
                    if (isset($idpmduilogos[$metadatakey])) {
                        $idpobject->logo = $idpmduilogos[$metadatakey];
                    }

                    $DB->insert_record($tablename, $idpobject);
                }
            }

            $data = get_config('auth_saml2', 'idpmetadata');
            if (!empty($data)) {
                $idpmetadata = new \auth_saml2\admin\setting_idpmetadata();
                $idpmetadata->write_setting($data);
            }
        }

        upgrade_plugin_savepoint(true, 2019022100, 'auth', 'saml2');
    }

    if ($oldversion < 2019062600) {
        // Move private key into new setting.
        set_config('privatekeypass', get_site_identifier(), 'auth_saml2');
        upgrade_plugin_savepoint(true, 2019062600, 'auth', 'saml2');
    }

    if ($oldversion < 2020031800) {
        $table = new xmldb_table('auth_saml2_idps');
        $fields = [];
        $fields[] = new xmldb_field('activeidp', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);
        $fields[] = new xmldb_field('defaultidp', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);
        $fields[] = new xmldb_field('adminidp', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);

        foreach ($fields as $field) {
            $dbman->change_field_default($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020031800, 'auth', 'saml2');
    }

    return true;
}
