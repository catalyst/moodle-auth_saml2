<?php

namespace SimpleSAML\XMLSecurity\Test\Utils;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Utils\Random;

/**
 * Tests for SimpleSAML\XMLSecurity\Utils\Random
 *
 * @package SimpleSAML\XMLSecurity\Utils
 */
final class RandomTest extends TestCase
{
    /**
     * Test generation of random GUIDs.
     */
    public function testGenerateGUID(): void
    {
        $mainRegEx = '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}';

        // test with default prefix
        $this->assertMatchesRegularExpression('/_' . $mainRegEx . '/', Random::generateGUID());

        // test with different prefix
        $this->assertMatchesRegularExpression('/pfx' . $mainRegEx . '/', Random::generateGUID('pfx'));
    }
}
