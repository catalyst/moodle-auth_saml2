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

        $idpentityids = $this->_customdata['idpentityids'];
        $idpmduinames = $this->_customdata['idpmduinames'];

        $selectvalues = [];

        foreach ($idpentityids as $key => $idpentity) {
            if (is_array($idpentity)) {
                $mform->addElement('header', $key.'header', $key);

                $starttable = <<< EOM
<table>
    <thead>
        <tr>
            <th>IdP Entity</th>
            <th>Name</th>
            <th> </th>
        </tr>
    </thead>
    <tbody>
EOM;
                $mform->addElement('html', $starttable);
                foreach ($idpentity as $subidpentity => $value) {
                    $fieldkey = 'values['.$key.']['.$subidpentity.']';

                    $name = '';
                    if (isset($idpmduinames[$key][$subidpentity])) {
                        $name = $idpmduinames[$key][$subidpentity];
                    }

                    $startrow = '<tr><td>'.$subidpentity.'</td><td>'.$name.'</td><td>';
                    $mform->addElement('html', $startrow);
                    $mform->addElement('advcheckbox', $fieldkey , '', '', array(), array(false, true));
                    $mform->addElement('html', '</td></tr>');
                }

                $mform->addElement('html', '</tbody></table>');
            }
        }

        $this->add_action_buttons();
    }

}

