<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\Test\XML\XMLBase64Element;
use SimpleSAML\Test\XML\XMLDumper;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\XMLBase64ElementTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XML\XMLBase64ElementTraitTest
 *
 * @covers \SimpleSAML\XML\XMLBase64ElementTrait
 * @covers \SimpleSAML\XML\XMLStringElementTrait
 * @covers \SimpleSAML\XML\AbstractXMLElement
 * @covers \SimpleSAML\XML\AbstractSerializableXML
 *
 * @package simplesamlphp\xml-common
 */
final class XMLBase64ElementTraitTest extends TestCase
{
    use SerializableXMLTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->testedClass = XMLBase64Element::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/bar_XMLBase64Element.xml',
        );
    }

    /**
     */
    public function testMarshalling(): void
    {
        $base64Element = new XMLBase64Element('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=');

        $this->assertEquals(
            XMLDumper::dumpDOMDocumentXMLWithBase64Content($this->xmlRepresentation),
            strval($base64Element),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $base64Element = XMLBase64Element::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=', $base64Element->getContent());
    }


    /**
     * @dataProvider provideBase64Cases
     */
    public function testBase64Cases(string $xml): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromString($xml);

        $xmlElement = XMLBase64Element::fromXML($xmlRepresentation->documentElement);

        $this->assertStringContainsString($xmlElement->getRawContent(), $xml);
    }

    public function provideBase64Cases(): array
    {
        return [
            'inline' => [
                <<<XML
<bar:XMLBase64Element xmlns:bar="urn:foo:bar">/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=</bar:XMLBase64Element>
XML
            ],
            'multiline' => [
                <<<XML
<bar:XMLBase64Element xmlns:bar="urn:foo:bar">
j14G9v6AnsOiEJYgkTg864DG3e/KLqoGpuybPGSGblVTn7ST6M/BsvP7YiVZjLqJEuEvWmf2mW4D
Pb+pbArzzDcsLWEtNveMrw+FkWehDUQV9oe20iepo+W46wmj7zB/eWL+Z8MrGvlycoTndJU6CVwH
TLsB+dq2FDa7JV4pAPjMY32JZTbiwKhzqw3nEi/eVrujJE4YRrlW28D+rXhITfoUAGGvsqPzcwGz
p02lnMe2SmXADY1u9lbVjOhUrJpgvWfn9YuiCR+wjvaGMwIwzfJxChLJZOBV+1ad1CyNTiu6qAbl
xZ4F8cWlMWJ7f0KkWvtw66HOf2VNR6Qan2Ra7Q==
</bar:XMLBase64Element>
XML
            ],
        ];
    }
}
