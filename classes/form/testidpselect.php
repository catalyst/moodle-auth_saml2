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

        $idpentityids  = $this->_customdata['idpentityids'];

        $mform->addElement('select', 'idp', 'IdP Entity', $idpentityids);

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'testtype', '', 'Test isAuthenticated and login', 'login');
        $radioarray[] = $mform->createElement('radio', 'testtype', '', 'Test using isPassive', 'passive');

        $mform->setDefault('testtype', 'login');
        $mform->addGroup($radioarray, 'radioar', '', ['<br/>'], false);

        $this->add_action_buttons(true, 'Start Test');
    }

}

