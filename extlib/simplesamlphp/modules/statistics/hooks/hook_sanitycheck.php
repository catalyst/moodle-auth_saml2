<?php

use Webmozart\Assert\Assert;

/**
 * Hook to do sanity checks
 *
 * @param array &$hookinfo  hookinfo
 * @return void
 */
function statistics_hook_sanitycheck(&$hookinfo)
{
    Assert::isArray($hookinfo);
    Assert::keyExists($hookinfo, 'errors');
    Assert::keyExists($hookinfo, 'info');

    try {
        $statconfig = \SimpleSAML\Configuration::getConfig('module_statistics.php');
    } catch (Exception $e) {
        $hookinfo['errors'][] = '[statistics] Could not get configuration: '.$e->getMessage();
        return;
    }

    $statdir = $statconfig->getValue('statdir');
    $inputfile = $statconfig->getValue('inputfile');

    if (file_exists($statdir)) {
        $hookinfo['info'][] = '[statistics] Statistics dir ['.$statdir.'] exists';
        if (is_writable($statdir)) {
            $hookinfo['info'][] = '[statistics] Statistics dir ['.$statdir.'] is writable';
        } else {
            $hookinfo['errors'][] = '[statistics] Statistics dir ['.$statdir.'] is not writable';
        }
    } else {
        $hookinfo['errors'][] = '[statistics] Statistics dir ['.$statdir.'] does not exist';
    }

    if (file_exists($inputfile)) {
        $hookinfo['info'][] = '[statistics] Input file ['.$inputfile.'] exists';
    } else {
        $hookinfo['errors'][] = '[statistics] Input file ['.$inputfile.'] does not exist';
    }
}
