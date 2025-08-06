<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\XMLBase64ElementTrait;

/**
 * Empty shell class for testing XMLBase64Element.
 *
 * @package simplesaml/xml-common
 */
final class XMLBase64Element extends AbstractXMLElement
{
    use XMLBase64ElementTrait;

    /** @var string */
    public const NS = 'urn:foo:bar';

    /** @var string */
    public const NS_PREFIX = 'bar';


    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }
}
