<?php
// This file is part of Moodle
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
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @codingStandardsIgnoreFile
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * @package     auth_saml2
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @SuppressWarnings(public) Allow as many methods as needed.
 */
class behat_auth_saml2 extends behat_base {
    /**
     * @Given /^the authentication plugin "([^"]*)" is (disabled|enabled) +\# auth_saml2$/
     */
    public function theAuthenticationPluginIsEnabledAuth_saml($plugin = 'saml2', $enabled = true) {
        if (($enabled == 'disabled') || ($enabled === false)) {
            $plugin = ''; // Disable all.
        }
        set_config('auth', $plugin);
        \core\session\manager::gc(); // Remove stale sessions.
        core_plugin_manager::reset_caches();
    }

    /**
     * @Given /^I go to the login page +\# auth_saml2$/
     */
    public function iGoToTheLoginPageAuth_saml() {
        $this->getSession()->visit($this->locate_path('login/index.php'));
    }

    /**
     * @Given /^I am an administrator +\# auth_saml2$/
     */
    public function iAmAnAdministratorAuth_saml() {
            $this->execute('behat_auth::i_log_in_as', ['admin']);
    }

    /**
     * @Given /^I am on the saml2 settings page +\# auth_saml2$/
     * @Then /^I go to the saml2 settings page (?:again) +\# auth_saml2$/
     */
    public function iGoToTheSamlsettingsPageAuth_saml() {
        $this->visitPath('/admin/auth_config.php?auth=saml2');
    }

    /**
     * @When /^I change the setting "([^"]*)" to "([^"]*)" +\# auth_saml2$/
     */
    public function iChangeTheSettingToAuth_saml($field, $value) {
        if ($field === 'Dual login') {
            $field = "duallogin";
        }
        $this->execute('behat_forms::i_set_the_field_to', [$field, $value]);
    }

    /**
     * @Given /^the setting "([^"]*)" should be "([^"]*)" +\# auth_saml2$/
     */
    public function theSettingShouldBeAuth_saml($field, $expectedvalue) {
        if ($field === 'Dual login') {
            $field = "duallogin";
        }
        $this->execute('behat_forms::the_field_matches_value', [$field, $expectedvalue]);
    }
}
