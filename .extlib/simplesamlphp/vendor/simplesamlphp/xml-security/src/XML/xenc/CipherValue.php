<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\xenc;

use SimpleSAML\XML\XMLBase64ElementTrait;

/**
 * Class representing a xenc:CipherValue element.
 *
 * @package simplesaml/xml-security
 */
final class CipherValue extends AbstractXencElement
{
    use XMLBase64ElementTrait;


    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }
}
