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

use auth_saml2\admin\saml2_settings;
use auth_saml2\task\metadata_refresh;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Gherkin\Node\TableNode;

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
     * Hopefully it will help when dealing with the IDP...
     *
     * @AfterStep
     */
    public function take_screenshot_after_failure($scope) {
        if (!($scope instanceof AfterStepScope)) {
            // Older version of behat.
            return;
        }
        $resultcode = $scope->getTestResult()->getResultCode();
        if ($resultcode === 99) {
            $filename = '/tmp/behat_screenshots.base64';
            if (!file_exists($filename)) {
                file_put_contents($filename, "Just paste it into your browser :-). You're welcome!\n\n");
            }

            try {
                $screenshot = $this->getSession()->getDriver()->getScreenshot();
                $screenshot = base64_encode($screenshot);

                $url = "data:image/png;base64,{$screenshot}\n"; //
                file_put_contents($filename, $url, FILE_APPEND);
            } catch (UnsupportedDriverActionException $e) {
                file_put_contents($filename, $e->getMessage(), FILE_APPEND);
            }
        }
    }

    /**
     * @Given /^the authentication plugin saml2 is (disabled|enabled) +\# auth_saml2$/
     */
    public function theAuthenticationPluginIsEnabledAuth_saml($enabled = true) {
        // If using SAML2 functionality, ensure all sessions are reset.
        $this->reset_moodle_session();

        if (($enabled == 'disabled') || ($enabled === false)) {
            set_config('auth', '');
        } else {
            set_config('auth', 'saml2');
            $this->initialise_saml2();
            /** @var auth_plugin_saml2 $auth */
            $auth = get_auth_plugin('saml2');
            if (!$auth->is_configured()) {
                throw new moodle_exception('Saml2 not configured.');
            }
        }

        \core\session\manager::gc(); // Remove stale sessions.
        core_plugin_manager::reset_caches();
    }

    /**
     * @Given /^I go to the (login|self-test) page +\# auth_saml2$/
     */
    public function iGoToTheLoginPageAuth_saml($page) {
        switch ($page){
            case 'login':
                $page = '/login/index.php';
                break;
            case 'self-test':
                $page='/auth/saml2/test.php';
                break;
        }


        $this->getSession()->visit($this->locate_path($page));
    }

    /**
     * @When /^I go to the login page with "([^"]*)" +\# auth_saml2$/
     */
    public function iGoToTheLoginPageWithAuth_saml($parameters) {
        $this->getSession()->visit($this->locate_path("login/index.php?{$parameters}"));
    }

    /**
     * @Given /^I am an administrator +\# auth_saml2$/
     */
    public function iAmAnAdministratorAuth_saml() {
        return $this->execute('behat_auth::i_log_in_as', ['admin']);
    }

    /**
     * @Given /^I am on the saml2 settings page +\# auth_saml2$/
     * @Then /^I go to the saml2 settings page (?:again) +\# auth_saml2$/
     */
    public function iGoToTheSamlsettingsPageAuth_saml() {
        $this->getSession()->visit($this->locate_path('/admin/settings.php?section=authsettingsaml2'));
    }

    /**
     * @When /^I change the setting "([^"]*)" to "([^"]*)" +\# auth_saml2$/
     */
    public function iChangeTheSettingToAuth_saml($field, $value) {
        $this->execute('behat_forms::i_set_the_field_to', [$field, $value]);
    }

    /**
     * @Given /^the setting "([^"]*)" should be "([^"]*)" +\# auth_saml2$/
     */
    public function theSettingShouldBeAuth_saml($field, $expectedvalue) {
        $this->execute('behat_forms::the_field_matches_value', [$field, $expectedvalue]);
    }

    private function apply_defaults() {
        global $CFG;

        require_once($CFG->dirroot . '/auth/saml2/auth.php');

        // All integration test are over HTTP.
        set_config('cookiesecure', false);

        /** @var auth_plugin_saml2 $auth */
        $auth = get_auth_plugin('saml2');

        $defaults = array_merge($auth->defaults, [
            'autocreate'          => 1,
            'field_map_idnumber'  => 'uid',
            'field_map_email'     => 'email',
            'field_map_firstname' => 'firstname',
            'field_map_lastname'  => 'surname',
            'field_map_lang'      => 'lang',
        ]);

        foreach (['email', 'firstname', 'lastname', 'lang'] as $field) {
            $defaults["field_lock_{$field}"] = 'unlocked';
            $defaults["field_updatelocal_{$field}"] = 'oncreate';
        }

        foreach ($defaults as $key => $value) {
            set_config($key, $value, 'auth_saml2');
        }
    }

    private function initialise_saml2() {
        $this->apply_defaults();
        require(__DIR__ . '/../../setup.php');
    }

    /**
     * @Given /^the saml2 setting "([^"]*)" is set to "([^"]*)" +\# auth_saml2$/
     */
    public function theSamlsettingIsSetToAuth_saml($setting, $value) {
        $map = [];

        if ($setting == 'Dual Login') {
            $setting = 'duallogin';
            $map = [
                'no'      => saml2_settings::OPTION_DUAL_LOGIN_NO,
                'yes'     => saml2_settings::OPTION_DUAL_LOGIN_YES,
                'passive' => saml2_settings::OPTION_DUAL_LOGIN_PASSIVE,
            ];
        }

        if ($setting == 'Group rules') {
            $setting = 'grouprules';
        }

        if ($setting == 'Account blocking response type') {
            $setting = 'flagresponsetype';
            $map = [
                'display custom message'   => saml2_settings::OPTION_FLAGGED_LOGIN_MESSAGE,
                'redirect to external url' => saml2_settings::OPTION_FLAGGED_LOGIN_REDIRECT,
            ];
        }

        if ($setting == 'Redirect URL') {
            $setting = 'flagredirecturl';
        }

        if ($setting == 'Response message') {
            $setting = 'flagmessage';
        }

        $lowervalue = strtolower($value);
        $value = array_key_exists($lowervalue, $map) ? $map[$lowervalue] : $value;
        set_config($setting, $value, 'auth_saml2');
    }

    /**
     * Configures auth_saml2 to use the mock SAML IdP in tests/fixtures/mockidp.
     *
     * Also initialises certificates (if not done yet) and turns off secure cookies, in case you
     * are running Behat over http.
     *
     * @Given /^the mock SAML IdP is configured +\# auth_saml2$/
     */
    public function the_mock_saml_idp_is_configured() {
        global $CFG;
        $cert = file_get_contents(__DIR__ . '/../fixtures/mockidp/mock.crt');
        $cert = preg_replace('~(-----(BEGIN|END) CERTIFICATE-----)|\n~', '', $cert);
        $baseurl = $CFG->wwwroot . '/auth/saml2/tests/fixtures/mockidp';

        $metadata = <<<EOF
<md:EntityDescriptor entityID="{$baseurl}/idpmetadata.php" xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata">
    <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" WantAuthnRequestsSigned="false">
        <md:KeyDescriptor>
            <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                <X509Data><X509Certificate>{$cert}</X509Certificate></X509Data>
            </KeyInfo>
        </md:KeyDescriptor>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
            Location="{$baseurl}/slo.php" />
        <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:persistent</md:NameIDFormat>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
            Location="{$baseurl}/sso.php" />
    </md:IDPSSODescriptor>
</md:EntityDescriptor>
EOF;

        // Update the config setting using the same method used in the UI.
        $idpmetadata = new \auth_saml2\admin\setting_idpmetadata();
        $idpmetadata->set_updatedcallback('auth_saml2_update_idp_metadata');
        $idpmetadata->write_setting($metadata);

        // Allow insecure cookies for Behat testing.
        set_config('cookiesecure', '0');

        $auth = get_auth_plugin('saml2');
        if (!$auth->is_configured()) {
            require_once(__DIR__ . '/../../setuplib.php');
            create_certificates($auth);
        }
    }

    /**
     * Confirms a user's login from the IdP, and returns information back to Moodle.
     *
     * This step must be used while at the mock IdP 'login' screen.
     *
     * @param TableNode $data Table of attributes
     * @When /^the mock SAML IdP allows ((?:passive )?)login with the following attributes: +\# auth_saml2$/
     */
    public function the_mock_saml_idp_allows_login_with_the_following_attributes($passive, TableNode $data) {
        // Check the correct page is current.
        $this->find('xpath', '//h1[normalize-space(.)="Mock IdP login"]',
                new ExpectationException('Not on the IdP login page.', $this->getSession()));

        // Find out if it's in passive mode.
        $pagepassive = $this->getSession()->getDriver()->find('//h2[normalize-space(.)="Passive mode"]');
        if ($passive && !$pagepassive) {
            throw new ExpectationException('Expected passive mode, but not passive.', $this->getSession());
        } else if (!$passive && $pagepassive) {
            throw new ExpectationException('Expected not passive mode, but passive.', $this->getSession());
        }

        // Work out the JSON data.
        $out = new \stdClass();
        foreach ($data->getRowsHash() as $key => $value) {
            $out->{$key} = $value;
        }
        $json = json_encode($out);

        // Set the field and press the submit button.
        $this->getSession()->getDriver()->setValue('//textarea', $json);
        $this->getSession()->getDriver()->click('//button[@id="login"]');
    }

    /**
     * After a passive login attempt, when the IdP confirms that the user is not logged in.
     *
     * @Given /^the mock SAML IdP does not allow passive login +\# auth_saml2$/
     */
    public function the_mock_saml_idp_does_not_allow_passive_login() {
        // Check the correct page is current.
        $this->find('xpath', '//h1[normalize-space(.)="Mock IdP login"]',
                new ExpectationException('Not on the IdP login page.', $this->getSession()));

        $this->find('xpath', '//h2[normalize-space(.)="Passive mode"]',
                new ExpectationException('Expected passive mode, but not passive.', $this->getSession()));

        // Press the no-login button.
        $this->getSession()->getDriver()->click('//button[@id="nologin"]');
    }

    /**
     * Confirms logout from the IdP.
     *
     * This step must be used while at the mock IdP 'logout' screen.
     *
     * @When /^the mock SAML IdP confirms logout +\# auth_saml2$/
     */
    public function the_mock_saml_idp_confirms_logout() {
        // Check the correct page is current.
        $this->find('xpath', '//h1[normalize-space(.)="Mock IdP logout"]',
                new ExpectationException('Not on the IdP logout page.', $this->getSession()));

        // Press the submit button.
        $this->getSession()->getDriver()->click('//button');
    }

    private function reset_moodle_session() {
        $this->iGoToTheLoginPageWithAuth_saml('saml=off');
        $this->getSession()->reset();
    }

    protected function execute($contextapi, $params = []) {
        global $CFG;

        // We allow usage of depricated behat steps for now.
        $CFG->behat_usedeprecated = true;

        // If newer Moodle, use the correct version.
        if ($CFG->branch >= 29) {
            return parent::execute($contextapi, $params);
        }

        // Backported for Moodle 27 and 28.
        list($class, $method) = explode("::", $contextapi);
        $object = behat_context_helper::get($class);
        $object->setMinkParameter('base_url', $CFG->wwwroot);
        return call_user_func_array([$object, $method], $params);
    }
}
