<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils;

use SimpleSAML\Configuration;
use SimpleSAML\Error\ConfigurationError;

/**
 * Test that ensures state doesn't spill over between tests
 * @package SimpleSAML\Test\Utils
 */
class ReduceSpillOverTest extends ClearStateTestCase
{
    /**
     * Set some global state
     * @return void
     */
    public function testSetState(): void
    {
        $_SERVER['QUERY_STRING'] = 'a=b';
        Configuration::loadFromArray(['a' => 'b'], '[ARRAY]', 'simplesaml');
        $this->assertEquals('b', Configuration::getInstance()->getString('a'));
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . __DIR__);
    }


    /**
     * Confirm global state removed prior to next test
     * @return void
     * @throws \SimpleSAML\Error\ConfigurationError
     */
    public function testStateRemoved(): void
    {
        $this->assertArrayNotHasKey('QUERY_STRING', $_SERVER);

        /** @var false $env */
        $env = getenv('SIMPLESAMLPHP_CONFIG_DIR');

        try {
            Configuration::getInstance();
            $this->fail('Expected config configured in other tests to no longer be valid');
        } catch (ConfigurationError $error) {
            // Expected error
        }
    }
}
