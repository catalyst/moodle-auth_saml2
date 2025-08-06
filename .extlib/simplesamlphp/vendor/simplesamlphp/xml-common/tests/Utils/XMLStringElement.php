<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\XMLStringElementTrait;

/**
 * Empty shell class for testing XMLStringElement.
 *
 * @package simplesaml/xml-common
 */
final class XMLStringElement extends AbstractXMLElement
{
    use XMLStringElementTrait;

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
