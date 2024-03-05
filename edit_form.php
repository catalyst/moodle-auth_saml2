<?php
// This file is part of SAML2 Authentication Plugin for Moodle
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
 * Edit IdP data mappings form.
 *
 * @package     auth_saml2
 * @author      Jackson D'Souza <jackson.dsouza@catalyst-eu.net>
 * @copyright   2019 Catalyst IT Europe {@link http://www.catalyst-eu.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_saml2\admin\saml2_settings;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Edit IdP data mappings form.
 *
 * @package     auth_saml2
 * @author      Jackson D'souza <jackson.dsouza@catalyst-eu.net>
 * @copyright   2019 Catalyst IT Europe {@link http://www.catalyst-eu.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml2_idp_edit_form extends moodleform {

    /**
     * Define the form for editing data mappings.
     */
    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        $id = isset($this->_customdata['id']) ? $this->_customdata['id'] : false;

        $mform->addElement('text', 'idpattr', get_string('idpattr', 'auth_saml2'), ['size' => 40, 'maxlength' => 50]);
        $mform->setType('idpattr', PARAM_TEXT);
        $mform->addElement('static', 'idpattr_help',
                            null,
                            get_string('idpattr_help', 'auth_saml2'));

        // Moodle Field.
        $fields = [
            'username'      => get_string('username'),
            'email'         => get_string('email'),
        ];
        $mform->addElement('select', 'mdlattr', get_string('mdlattr', 'auth_saml2'), $fields);
        $mform->addElement('static', 'mdlattr_help', null,
                            get_string('mdlattr_help', 'auth_saml2'));
        $mform->setDefault('mdlattr', 'username');

        $mform->addElement('selectyesno', 'tolower', get_string('tolower', 'auth_saml2'));
        $mform->addElement('static', 'tolower_help', null,
                            get_string('tolower_help', 'auth_saml2'));

        $mform->addElement('textarea', 'requestedattributes', get_string('requestedattributes', 'auth_saml2'),
                            'wrap="virtual" rows="8" cols="50"');
        $mform->setType('requestedattributes', PARAM_TEXT);
        $mform->addElement('static', 'requestedattributes_help', null,
                            get_string('requestedattributes_help', 'auth_saml2',
                                ['example' => "<pre>
urn:mace:dir:attribute-def:eduPersonPrincipalName
urn:mace:dir:attribute-def:mail *</pre>"]));
        $mform->setDefault('requestedattributes', '');

        $mform->addElement('selectyesno', 'autocreate', get_string('autocreate', 'auth_saml2'));
        $mform->addElement('static', 'autocreate_help',
                            null,
                            get_string('autocreate_help', 'auth_saml2'));

        $mform->addElement('textarea', 'grouprules', get_string('grouprules', 'auth_saml2'),
                            'wrap="virtual" rows="8" cols="50"');
        $mform->setType('grouprules', PARAM_TEXT);
        $mform->addElement('static', 'grouprules_help', null,
                            get_string('grouprules_help', 'auth_saml2'));
        $mform->setDefault('grouprules', '');

        $mform->addElement('text', 'alterlogout', get_string('alterlogout', 'auth_saml2'), ['size' => 40, 'maxlength' => 50]);
        $mform->setType('alterlogout', PARAM_URL);
        $mform->addElement('static', 'alterlogout_help',
                            null,
                            get_string('alterlogout_help', 'auth_saml2'));

        $mform->addElement('selectyesno', 'attemptsignout', get_string('attemptsignout', 'auth_saml2'));
        $mform->addElement('static', 'attemptsignout_help',
                            null,
                            get_string('attemptsignout_help', 'auth_saml2'));

        $authplugin = get_auth_plugin('saml2');
        $mform->addElement('static', 'sspversion',
                            get_string('sspversion', 'auth_saml2'),
                            $authplugin->get_ssp_version());

        $mform->addElement('html', html_writer::tag('h3', get_string('blockredirectheading', 'auth_saml2')));

        // Flagged login response options.
        $flaggedloginresponseoptions = [
            saml2_settings::OPTION_FLAGGED_LOGIN_MESSAGE => get_string('flaggedresponsetypemessage', 'auth_saml2'),
            saml2_settings::OPTION_FLAGGED_LOGIN_REDIRECT => get_string('flaggedresponsetyperedirect', 'auth_saml2'),
        ];

        $mform->addElement('select', 'flagresponsetype', get_string('flagresponsetype', 'auth_saml2'),
            $flaggedloginresponseoptions);
        $mform->getElement('flagresponsetype')->setSelected(saml2_settings::OPTION_FLAGGED_LOGIN_REDIRECT);
        $mform->addElement('static', 'flagresponsetype_help', null,
                            get_string('flagresponsetype_help', 'auth_saml2'));

        $mform->addElement('text', 'flagredirecturl', get_string('flagredirecturl', 'auth_saml2'),
            ['size' => 40, 'maxlength' => 50]);
        $mform->setType('flagredirecturl', PARAM_URL);
        $mform->addElement('static', 'flagredirecturl_help', null,
                            get_string('flagredirecturl_help', 'auth_saml2'));

        $mform->addElement('textarea', 'flagmessage', get_string('flagmessage', 'auth_saml2'),
                            'wrap="virtual" rows="3" cols="50"');
        $mform->setType('flagmessage', PARAM_TEXT);
        $mform->addElement('static', 'flagmessage_help', null,
                            get_string('flagmessage_help', 'auth_saml2'));
        $mform->setDefault('flagmessage', '');

        // Display locking / mapping of profile fields.
        $help = get_string('auth_updatelocal_expl', 'auth');
        $help .= get_string('auth_fieldlock_expl', 'auth');
        $help .= get_string('auth_updateremote_expl', 'auth');
        auth_saml2_display_auth_lock_options(
            $mform,
            $authplugin->authtype,
            $authplugin->userfields,
            $help,
            true,
            true,
            $authplugin->get_custom_user_profile_fields(),
            'form'
        );

        if ($id !== false) {
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);
        }

        $this->add_action_buttons();

    }

    /**
     * Called from form method definition_after_data
     * Can be overridden if more functionality is needed.
     */
    public function definition_after_data() {
        $mform =& $this->_form;
        foreach ($this->_customdata['data'] as $key => $value) {
            $mform->setDefault($key, $value);
        }
    }
}
