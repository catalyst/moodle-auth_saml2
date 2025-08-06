<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\Test\XML\ds;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\SignatureValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\XMLSecurity\Test\XML\ds\SignatureValueTest
 *
 * @covers \SimpleSAML\XMLSecurity\XML\ds\AbstractDsElement
 * @covers \SimpleSAML\XMLSecurity\XML\ds\SignatureValue
 *
 * @package simplesamlphp/xml-security
 */
final class SignatureValueTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->testedClass = SignatureValue::class;

        $this->schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/schemas/xmldsig1-schema.xsd';

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/tests/resources/xml/ds_SignatureValue.xml',
        );
    }


    /**
     * Test creating a SignatureValue from scratch.
     */
    public function testMarshalling(): void
    {
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval(new SignatureValue(
                'j14G9v6AnsOiEJYgkTg864DG3e/KLqoGpuybPGSGblVTn7ST6M/BsvP7YiVZjLqJEuEvWmf2mW4DPb+pbArzzDcsLWEtNveMrw+F' .
                'kWehDUQV9oe20iepo+W46wmj7zB/eWL+Z8MrGvlycoTndJU6CVwHTLsB+dq2FDa7JV4pAPjMY32JZTbiwKhzqw3nEi/eVrujJE4Y' .
                'RrlW28D+rXhITfoUAGGvsqPzcwGzp02lnMe2SmXADY1u9lbVjOhUrJpgvWfn9YuiCR+wjvaGMwIwzfJxChLJZOBV+1ad1CyNTiu6' .
                'qAblxZ4F8cWlMWJ7f0KkWvtw66HOf2VNR6Qan2Ra7Q==',
                'abc123',
            )),
        );
    }


    /**
     */
    public function testMarshallingNotBase64(): void
    {
        $this->expectException(AssertionFailedException::class);
        $digestValue = new SignatureValue(
            'j14G9v6AnsOiEJYgkTg864DG3e/KLqoGpuybPGSGblVTn7ST6M/BsvP7YiVZjLqJEuEvWmf2mW4DPb+pbArzzDcsLWEtNveMrw+F' .
            'kWehDUQV9oe20iepo+W46wmj7zB/eWL+Z8MrGvlycoTndJU6CVwHTLsB+dq2FDa7JV4pAPjMY32JZTbiwKhzqw3nEi/eVrujJE4Y' .
            'RrlW28D+rXhITfoUAGGvsqPzcwGzp02lnMe2SmXADY1u9lbVjOhUrJpgvWfn9YuiCR+wjvaGMwIwzfJxChLJZOBV+1ad1CyNTiu6' .
            'qblxZ4F8cWlMWJ7f0KkWvtw66HOf2VNR6Qan2Ra7Q==',
            'abc123',
        );
    }


    /**
     * Test creating a SignatureValue object from XML.
     */
    public function testUnmarshalling(): void
    {
        $signatureValue = SignatureValue::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            'j14G9v6AnsOiEJYgkTg864DG3e/KLqoGpuybPGSGblVTn7ST6M/BsvP7YiVZjLqJEuEvWmf2mW4DPb+pbArzzDcsLWEtNveMrw+F' .
            'kWehDUQV9oe20iepo+W46wmj7zB/eWL+Z8MrGvlycoTndJU6CVwHTLsB+dq2FDa7JV4pAPjMY32JZTbiwKhzqw3nEi/eVrujJE4Y' .
            'RrlW28D+rXhITfoUAGGvsqPzcwGzp02lnMe2SmXADY1u9lbVjOhUrJpgvWfn9YuiCR+wjvaGMwIwzfJxChLJZOBV+1ad1CyNTiu6' .
            'qAblxZ4F8cWlMWJ7f0KkWvtw66HOf2VNR6Qan2Ra7Q==',
            $signatureValue->getContent(),
        );
        $this->assertEquals('abc123', $signatureValue->getId());
    }
}
