<?php
/**
 * @package     auth_saml2
 * @author      Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright   2019 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2\tests;

use \advanced_testcase;
use auth_saml2\settings_helper;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../_autoload.php');

/**
 * Class settings_helper_testcase
 *
 * @package auth_saml2\tests
 */
class settings_helper_testcase extends advanced_testcase {

    /**
     * Test that get_http_https_regex method correctly validates valid urls
     *
     * @dataProvider provide_valid_urls
     */
    public function test_valid_urls($url) {

        $actual = preg_match(settings_helper::get_http_https_regex(), $url);
        $this->assertEquals(true, (bool) $actual);
    }

    /**
     * Test that get_http_https_regex method does not validate invalid urls
     *
     * @dataProvider provide_invalid_urls
     */
    public function test_invalid_urls($url) {

        $actual = preg_match(settings_helper::get_http_https_regex(), $url);
        $this->assertEquals(false, (bool) $actual);
    }

    /**
     * Provider for valid urls from fixture
     *
     * @return mixed
     */
    public function provide_valid_urls() {
        require_once(__DIR__ . '/../fixtures/validurls.php');
        return $validurls;
    }

    /**
     * Provider for invalid urls from fixture
     *
     * @return mixed
     */
    public function provide_invalid_urls() {
        require_once(__DIR__ . '/../fixtures/invalidurls.php');
        return $invalidurls;
    }
}