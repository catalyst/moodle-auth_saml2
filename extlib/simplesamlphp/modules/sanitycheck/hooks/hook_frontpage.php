<?php

use Webmozart\Assert\Assert;

/**
 * Hook to add the modinfo module to the frontpage.
 *
 * @param array &$links  The links on the frontpage, split into sections.
 * @return void
 */
function sanitycheck_hook_frontpage(&$links)
{
    Assert::isArray($links);
    Assert::keyExists($links, 'links', $links);

    $links['config']['sanitycheck'] = [
        'href' => SimpleSAML\Module::getModuleURL('sanitycheck/index.php'),
        'text' => '{sanitycheck:strings:link_sanitycheck}',
    ];
}

