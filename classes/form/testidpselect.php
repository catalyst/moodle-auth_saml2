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
 * Test IdP selection form.
 *
 * @package   auth_saml2
 * @author    Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2\form;

defined('MOODLE_INTERNAL') || die();

use moodleform;

require_once("$CFG->libdir/formslib.php");

/**
 * Test IdP selection form.
 *
 * @package    auth_saml2
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testidpselect extends moodleform {

    /**
     * Definition
     */
    public function definition() {
        $mform = $this->_form;

        $idpentityids = $this->_customdata['idpentityids'];

        $selectvalues = [];

        foreach ($idpentityids as $idpentity) {
            if (is_string($idpentity)) {
                $selectvalues[$idpentity] = $idpentity;
            } else {
                foreach ((array)$idpentity as $subidpentity => $active) {
                    if ($active) {
                        $selectvalues[$subidpentity] = $subidpentity;
                    }
                }
            }
        }

        $mform->addElement('select', 'idp', get_string('test_auth_button_login', 'auth_saml2'), $selectvalues);

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'testtype', '', get_string('test_auth_str', 'auth_saml2'), 'login');
        $radioarray[] = $mform->createElement('radio', 'testtype', '', get_string('test_passive_str', 'auth_saml2'), 'passive');

        $mform->setDefault('testtype', 'login');
        $mform->addGroup($radioarray, 'radioar', '', ['<br/>'], false);
        $mform->addElement('submit', 'login', get_string('test_auth_button_login', 'auth_saml2'));

        $mform->addElement('html', '<br /><br />');

        $mform->addElement('select', 'idplogout', get_string('test_auth_button_logout', 'auth_saml2'), $selectvalues);
        $mform->addElement('submit', 'logout', get_string('test_auth_button_logout', 'auth_saml2'));
    }

}

