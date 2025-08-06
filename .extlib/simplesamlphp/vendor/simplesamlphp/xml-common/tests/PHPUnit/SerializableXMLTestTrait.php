<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use DOMDocument;

use function class_exists;

/**
 * Test for SerializableXML classes to perform default serialization tests.
 *
 * @package simplesamlphp\xml-common
 */
trait SerializableXMLTestTrait
{
    /** @var class-string */
    protected string $testedClass;

    /** @var \DOMDocument */
    protected DOMDocument $xmlRepresentation;


    /**
     * Test serialization / unserialization.
     */
    public function testSerialization(): void
    {
        /** @psalm-var class-string|null */
        $testedClass = $this->testedClass;

        /** @psalm-var \DOMElement|null */
        $xmlRepresentation = $this->xmlRepresentation;

        if ($testedClass === null || !class_exists($testedClass)) {
            $this->markTestSkipped(
                'Unable to run ' . self::class . '::testSerialization(). Please set ' . self::class
                . ':$testedClass to a class-string representing the XML-class being tested',
            );
        } elseif ($xmlRepresentation === null) {
            $this->markTestSkipped(
                'Unable to run ' . self::class . '::testSerialization(). Please set ' . self::class
                . ':$xmlRepresentation to a DOMDocument representing the XML-class being tested',
            );
        } else {
            /** @psalm-var \DOMElement */
            $xmlRepresentationDocument = $this->xmlRepresentation->documentElement;

            $this->assertEquals(
                $this->xmlRepresentation->saveXML($xmlRepresentationDocument),
                strval(unserialize(serialize($testedClass::fromXML($xmlRepresentationDocument)))),
            );
        }
    }
}
