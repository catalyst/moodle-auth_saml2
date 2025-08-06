<?php

namespace SimpleSAML\XMLSecurity\Test\Utils;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XMLSecurity\Utils\Security;

/**
 * A class to test SimpleSAML\XMLSecurity\Utils\Security.
 *
 * @package SimpleSAML\XMLSecurity\Utils
 */
final class SecurityTest extends TestCase
{
    /**
     * Test the constant-time comparison function.
     */
    public function testCompareStrings(): void
    {
        // test that two equal strings compare successfully
        $this->assertTrue(Security::compareStrings('random string', 'random string'));

        // test that two different, equal-length strings fail to compare
        $this->assertFalse(Security::compareStrings('random string', 'string random'));

        // test that two different-length strings fail to compare
        $this->assertFalse(Security::compareStrings('one string', 'one string      '));
    }
}
