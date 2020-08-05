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

            foreach ($idpentities as $idpentityid => $idpentity) {
                $fieldkey = 'metadataentities['.$metadataurl.']['.$idpentityid.']';

                // Add the start of the row, entiyid, name, etc.
                $mform->addElement('header',  $idpentityid.'header', $idpentity['entityid']);
                $mform->addElement('hidden', $fieldkey.'[id]');
                $mform->setType($fieldkey.'[id]', PARAM_INT);

                // Add the displayname textbox.
                $mform->addElement('text', $fieldkey.'[displayname]', get_string('multiidp:label:displayname', 'auth_saml2'), array('placeholder' => $idpentity['defaultname']));
                $mform->setType($fieldkey.'[displayname]', PARAM_TEXT);

                // Add the alias textbox.
                $mform->addElement('text', $fieldkey.'[alias]', get_string('multiidp:label:alias', 'auth_saml2'));
                $mform->setType($fieldkey.'[alias]', PARAM_TEXT);

                // Add the activeidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[activeidp]', get_string('multiidp:label:active', 'auth_saml2'), '', array(), array(false, true));

                // Add the defaultidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[defaultidp]', get_string('multiidp:label:defaultidp', 'auth_saml2'), '', array(), array(false, true));

                // Add the adminidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[adminidp]', get_string('multiidp:label:admin', 'auth_saml2'), '', array(), array(false, true));

                // Add whitelisted IP for redirection to this IdP.
                $mform->addElement('textarea', $fieldkey.'[whitelist]', get_string('multiidp:label:whitelist', 'auth_saml2'));
                $mform->addHelpButton($fieldkey.'[whitelist]', 'multiidp:label:whitelist', 'auth_saml2');
                $mform->setType($fieldkey.'[whitelist]', PARAM_TEXT);
            }
        }

        $this->add_action_buttons();
    }

}

