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
 * Multiple IdP selection form.
 *
 * @package    auth_saml2
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2\form;

defined('MOODLE_INTERNAL') || die();

use moodleform;

require_once("$CFG->libdir/formslib.php");

/**
 * Multiple IdP selection form.
 *
 * @package    auth_saml2
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availableidps extends moodleform {

    /**
     * Definition
     */
    public function definition() {
        $mform = $this->_form;

        $metadataentities = $this->_customdata['metadataentities'];

        foreach ($metadataentities as $metadataurl => $idpentities) {
            $mform->addElement('header', $metadataurl.'header', $metadataurl);

            // Start the table.
            $starttable = <<< EOM
<table>
    <thead>
        <tr>
            <th>IdP Entity</th>
            <th>Display name</th>
            <th>Alias</th>
            <th>Active</th>
            <th>Default</th>
            <th>Admin</th>
        </tr>
    </thead>
<tbody>
EOM;
            $mform->addElement('html', $starttable);
            foreach ($idpentities as $idpentityid => $idpentity) {
                $fieldkey = 'metadataentities['.$metadataurl.']['.$idpentityid.']';

                // Add the start of the row, entiyid, name, etc.
                $mform->addElement('html', '<tr><td>');
                $mform->addElement('hidden', $fieldkey.'[id]');
                $mform->addElement('html', $idpentity['entityid'].'</td><td>');

                // Add the displayname textbox
                $mform->addElement('text', $fieldkey.'[displayname]', '', array('placeholder' => $idpentity['defaultname']));
                $mform->addElement('html', '</td><td>');

                // Add the alias textbox.
                $mform->addElement('text', $fieldkey.'[alias]', '');
                $mform->addElement('html', '</td><td>');

                // Add the activeidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[activeidp]', '', '', array(), array(false, true));
                $mform->addElement('html', '</td><td>');

                // Add the defaultidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[defaultidp]', '', '', array(), array(false, true));
                $mform->addElement('html', '</td><td>');

                // Add the adminidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[adminidp]', '', '', array(), array(false, true));
                $mform->addElement('html', '</td></tr>');
            }

            // Close off the table.
            $mform->addElement('html', '</tbody></table>');
        }

        $this->add_action_buttons();
    }

}

