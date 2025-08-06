<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use DOMDocument;

use function class_exists;

/**
 * Test for ArrayizableXML classes to perform default serialization tests.
 *
 * @package simplesamlphp\xml-common
 */
trait ArrayizableXMLTestTrait
{
    /** @var class-string */
    protected string $testedClass;

    /** @var array */
    protected array $arrayRepresentation;


    /**
     * Test arrayization / de-arrayization
     */
    public function testArrayization(): void
    {
        /** @psalm-var class-string|null */
        $testedClass = $this->testedClass;

        /** @psalm-var array|null */
        $arrayRepresentation = $this->arrayRepresentation;


        if (!class_exists($this->testedClass)) {
            $this->markTestSkipped(
                'Unable to run ' . self::class . '::testArrayization(). Please set ' . self::class
                . ':$element to a class-string representing the XML-class being tested',
            );
        } elseif ($this->arrayRepresentation === null) {
            $this->markTestSkipped(
                'Unable to run ' . self::class . '::testArrayization(). Please set ' . self::class
                . ':$arrayRepresentation to an array representing the XML-class being tested',
            );
        } else {
            $this->assertEquals(
                $this->arrayRepresentation,
                $this->testedClass::fromArray($this->arrayRepresentation)->toArray(),
            );
        }
    }
}
