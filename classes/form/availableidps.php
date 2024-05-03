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
use core\output\notification;

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
        global $OUTPUT;
        $mform = $this->_form;

        $metadataentities = $this->_customdata['metadataentities'];

        foreach ($metadataentities as $metadataurl => $idpentities) {

            foreach ($idpentities as $idpentityid => $idpentity) {
                $fieldkey = 'metadataentities['.$metadataurl.']['.$idpentityid.']';

                // Add the start of the row, entiyid, name, etc.
                $mform->addElement('header',  $idpentityid.'header', $idpentity['name']);
                $mform->addElement('hidden', $fieldkey.'[id]');
                $mform->setType($fieldkey.'[id]', PARAM_INT);

                // List the source.
                $mform->addElement('html', \html_writer::div(get_string('source', 'auth_saml2', $idpentity['entityid']),
                    'alert p-2 bg-gray bg-gray020'));

                // Add the displayname textbox.
                $mform->addElement('text', $fieldkey.'[displayname]',
                    get_string('multiidp:label:displayname', 'auth_saml2'), ['placeholder' => $idpentity['defaultname']]);
                $mform->setType($fieldkey.'[displayname]', PARAM_TEXT);

                // Add the alias textbox.
                $mform->addElement('text', $fieldkey.'[alias]', get_string('multiidp:label:alias', 'auth_saml2'));
                $mform->setType($fieldkey.'[alias]', PARAM_TEXT);

                // Update IdP configuration settings.
                $editmappings = new \moodle_url('edit.php', ['id' => $idpentity['id']]);
                $mform->addElement('static', $fieldkey.'[mapping]',
                    get_string('mappings', 'auth_saml2'), get_string('edit', 'auth_saml2', $editmappings));

                // Add the activeidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[activeidp]',
                    get_string('status', 'auth_saml2'), get_string('multiidp:label:active', 'auth_saml2'), [], [false, true]);

                // Add the defaultidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[defaultidp]',
                    get_string('multiidp:label:defaultidp', 'auth_saml2'), '', [], [false, true]);

                // Add the adminidp checkbox.
                $mform->addElement('advcheckbox', $fieldkey.'[adminidp]',
                    get_string('multiidp:label:admin', 'auth_saml2'), '', [], [false, true]);
                $mform->addHelpButton($fieldkey.'[adminidp]', 'multiidp:label:admin', 'auth_saml2');

                // Add whitelisted IP for redirection to this IdP.
                $mform->addElement('textarea', $fieldkey.'[whitelist]', get_string('multiidp:label:whitelist', 'auth_saml2'));
                $mform->addHelpButton($fieldkey.'[whitelist]', 'multiidp:label:whitelist', 'auth_saml2');
                $mform->setType($fieldkey.'[whitelist]', PARAM_TEXT);

                // Moodle Workplace - Tenant availability edit button.
                if (class_exists('\tool_tenant\local\auth\saml2\manager')) {
                    $links = component_class_callback('\tool_tenant\local\auth\saml2\manager',
                        'issuer_tenant_availability_button', [['id' => $idpentityid, 'name' => $idpentity['name']]], '');
                    $mform->addElement('static', 'tenantbutton', '&nbsp;', $links);
                }
            }
        }

        $this->add_action_buttons();
    }

}

