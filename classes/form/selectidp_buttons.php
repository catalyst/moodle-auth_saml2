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
 * IdP selection form.
 *
 * @package   auth_saml2
 * @author    Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2\form;

defined('MOODLE_INTERNAL') || die();

use moodleform;

require_once("$CFG->libdir/formslib.php");

/**
 * IdP selection form.
 *
 * @package    auth_saml2
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class selectidp_buttons extends moodleform {

    /**
     * Definition
     */
    public function definition() {
        $mform = $this->_form;

        $metadataentities = $this->_customdata['metadataentities'];
        $defaultidp = $this->_customdata['defaultidp'];
        $wants = $this->_customdata['wants'];

        $mform->addElement('hidden', 'wants', $wants);
        $mform->addElement('checkbox', 'rememberidp' , '', get_string('rememberidp', 'auth_saml2'));

        foreach ($metadataentities as $idpentities) {
            if (isset($idpentities[$defaultidp])) {
                $defaultidp = $idpentities[$defaultidp];
                $mform->addElement('html', $this->get_idpbutton($defaultidp, $defaultidp->name, $defaultidp->logo, true));
                $mform->addElement('html', '<hr>');
                unset($idpentities[$defaultidp]);
            }

            foreach ($idpentities as $idpentityid => $idp) {
                $mform->addElement('html', $this->get_idpbutton($idpentityid, $idp->name, $idp->logo));
            }
        }
    }

    private function get_idpbutton($idpentityid, $idpname, $logourl, $rememberedidp = false) {
        $logo = !is_null($logourl) ? "<img src=\"{$logourl}\"> " : "";
        $extraclasses = $rememberedidp ? "rememberedidp" : "";
        return <<<EOD
<div class="fitem fitem_actionbuttons fitem_fsubmit ">
    <button value="{$idpentityid}" class="btn idpbtn {$extraclasses}" type="submit" name="idp">
        {$logo}{$idpname}
    </button>
</div>
EOD;
    }

}

