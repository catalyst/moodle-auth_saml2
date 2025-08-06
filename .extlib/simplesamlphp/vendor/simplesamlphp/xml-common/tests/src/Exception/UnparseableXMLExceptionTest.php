<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use LibXMLError;
use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\Exception\UnparseableXMLException;

/**
 * Empty shell class for testing UnparseableXMLException.
 *
 * @package simplesaml/xml-security
 *
 * @covers \SimpleSAML\XML\Exception\UnparseableXMLException
 */
final class UnparseableXMLExceptionTest extends TestCase
{
    /** @var \LibXMLError $libxmlerror */
    protected LibXMLError $libxmlerror;


    /**
     */
    public function setup(): void
    {
        $this->libxmlerror = new LibXMLError();

        // Set error variables
        $this->libxmlerror->level = \LIBXML_ERR_ERROR;
        $this->libxmlerror->code = 2;
        $this->libxmlerror->column = 3;
        $this->libxmlerror->message = 'message';
        $this->libxmlerror->file = 'file';
        $this->libxmlerror->line = 99;
    }


    public function testUnparseableXMLException(): void
    {
        $this->expectException(UnparseableXMLException::class);
        $this->expectExceptionMessage('Unable to parse XML - "ERROR[2]": "message" in "file" at line 99 on column 3"');

        // Throw exception
        throw new UnparseableXMLException($this->libxmlerror);
    }
}
