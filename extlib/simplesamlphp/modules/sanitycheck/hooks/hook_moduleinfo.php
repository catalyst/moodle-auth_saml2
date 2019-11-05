<?php

use Webmozart\Assert\Assert;

/**
 * This hook lets the module describe itself.
 *
 * @param array &$moduleinfo  The links on the frontpage, split into sections.
 * @return void
 */
function sanitycheck_hook_moduleinfo(&$moduleinfo)
{
    Assert::isArray($moduleinfo);
    Assert::keyExists($moduleinfo, 'info');

    $moduleinfo['info']['sanitycheck'] = [
        'name' => ['en' => 'Sanity check'],
        'description' => ['en' => 'This module adds functionality for other modules to provide sanity checks.'],

        'dependencies' => ['core'],
        'uses' => ['cron'],
    ];
}
