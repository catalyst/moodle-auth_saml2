<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils;

use PHPUnit\Framework\TestCase;

/**
 * A base SSP test case that takes care of removing global state prior to test runs
 */
class ClearStateTestCase extends TestCase
{
    /**
     * Used for managing and clearing state
     * @var \SimpleSAML\TestUtils\StateClearer|null
     */
    protected static $stateClearer = null;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        if (!isset(self::$stateClearer)) {
            self::$stateClearer = new StateClearer();
            self::$stateClearer->backupGlobals();
        }
    }


    /**
     * @return void
     */
    protected function setUp(): void
    {
        self::clearState();
    }


    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::clearState();
    }


    /**
     * Clear any SSP global state to reduce spill over between tests.
     * @return void
     */
    public static function clearState(): void
    {
        if (isset(self::$stateClearer)) {
            self::$stateClearer->clearGlobals();
            self::$stateClearer->clearSSPState();
        }
    }
}
