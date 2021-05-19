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

namespace auth_saml2;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

/**
 * Static list of api methods for auth saml2 configuration.
 *
 * @package   auth_saml2
 * @author    Brendan Heywood <brendan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {
    /**
     * Called from SimpleSamlphp after a LogoutResponse from the IdP
     */
    public static function logout_from_idp_front_channel() {
        // The SP session will be cleaned up but we need to remove the
        // Moodle session here.
        \core\session\manager::terminate_current();
    }

    /**
     * Called from SimpleSamlphp after a LogoutRequest from the SP
     */
    public static function after_logout_from_sp($state) {
        global $saml2config;

        $cookiename = $saml2config['session.cookie.name'];
        $sessid = $_COOKIE[$cookiename];

        // In SSP should do this for us but remove stored SP session data.
        $storeclass = $saml2config['store.type'];
        $store = new $storeclass;
        $store->delete('session', $sessid);

        redirect(new moodle_url($state['ReturnTo']));
    }
}