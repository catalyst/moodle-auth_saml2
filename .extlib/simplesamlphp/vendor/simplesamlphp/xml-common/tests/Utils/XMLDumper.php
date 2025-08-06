<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use DOMDocument;

use function preg_replace;

/**
 * Class \SimpleSAML\Test\XML\XMLDumper
 *
 * @package simplesamlphp/xml-common
 */
final class XMLDumper
{
    public static function dumpDOMDocumentXMLWithBase64Content(DOMDocument $document): string
    {
        $dump = $document->saveXML($document->documentElement);
        return preg_replace('/ *[\\r\\n] */', '', $dump);
    }
}
