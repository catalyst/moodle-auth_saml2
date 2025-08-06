<?php

declare(strict_types=1);

namespace SimpleSAML\Test\XML;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\Test\XML\ExtendableElement;
use SimpleSAML\Test\XML\SchemaViolationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\XMLElementInterface;

use function dirname;

/**
 * Class \SimpleSAML\XML\ExtendableElementTraitTest
 *
 * @covers \SimpleSAML\XML\ExtendableElementTrait
 *
 * @package simplesamlphp\xml-common
 */
final class ExtendableElementTraitTest extends TestCase
{
    use SerializableXMLTestTrait;
    use SchemaValidationTestTrait;

    /** @var \SimpleSAML\XML\XMLElementInterface */
    protected XMLElementInterface $empty;

    /** @var \SimpleSAML\XML\XMLElementInterface */
    protected XMLElementInterface $local;

    /** @var \SimpleSAML\XML\XMLElementInterface */
    protected XMLElementInterface $other;

    /** @var \SimpleSAML\XML\XMLElementInterface */
    protected XMLElementInterface $target;


    /**
     */
    public function setup(): void
    {
        $this->schema = dirname(dirname(__FILE__)) . '/resources/schemas/simplesamlphp.xsd';

        $this->testedClass = ExtendableElement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/ssp_ExtendableElement.xml',
        );

        $this->empty = new Chunk(DOMDocumentFactory::fromString(<<<XML
            <chunk/>
XML
        )->documentElement);

        $this->local = new Chunk(DOMDocumentFactory::fromString(<<<XML
            <chunk>some</chunk>
XML
        )->documentElement);

        $this->target = new Chunk(DOMDocumentFactory::fromString(<<<XML
            <ssp:chunk xmlns:ssp="urn:x-simplesamlphp:namespace">some</ssp:chunk>
XML
        )->documentElement);

        $this->other = new Chunk(DOMDocumentFactory::fromString(<<<XML
            <dummy:chunk xmlns:dummy="urn:custom:dummy">some</dummy:chunk>
XML
        )->documentElement);
    }


    /**
     */
    public function testInvalidNamespaceThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([]) extends ExtendableElement {
            public function getNamespace()
            {
                return 'wrong';
            }
        };
    }


    /**
     */
    public function testIllegalNamespaceComboThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([]) extends ExtendableElement {
            public function getNamespace()
            {
                return [C::XS_ANY_NS_OTHER, C::XS_ANY_NS_ANY];
            }
        };
    }


    /**
     */
    public function testEmptyNamespaceArrayThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([]) extends ExtendableElement {
            public function getNamespace()
            {
                return [];
            }
        };
    }


    /**
     */
    public function testOtherNamespacePassingOtherSucceeds(): void
    {
        new class ([$this->other]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_OTHER;
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testOtherNamespacePassingLocalThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([$this->local]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_OTHER;
            }
        };
    }


    /**
     */
    public function testTargetNamespacePassingTargetSucceeds(): void
    {
        new class ([$this->target]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_TARGET;
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testTargetNamespacePassingTargetArraySucceeds(): void
    {
        new class ([$this->target]) extends ExtendableElement {
            public function getNamespace()
            {
                return [C::XS_ANY_NS_TARGET];
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testTargetNamespacePassingTargetArraySucceedsWithLocal(): void
    {
        new class ([$this->target]) extends ExtendableElement {
            public function getNamespace()
            {
                return [C::XS_ANY_NS_TARGET, C::XS_ANY_NS_LOCAL];
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testTargetNamespacePassingOtherThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([$this->other]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_TARGET;
            }
        };
    }


    /**
     */
    public function testLocalNamespacePassingLocalSucceeds(): void
    {
        $o = new class ([$this->local]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_LOCAL;
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testLocalNamespacePassingLocalArraySucceeds(): void
    {
        new class ([$this->local]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_LOCAL;
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testLocalNamespacePassingTargetThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([$this->target]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_LOCAL;
            }
        };
    }


    /**
     */
    public function testLocalNamespacePassingOtherThrowsAnException(): void
    {
        $this->expectException(AssertionFailedException::class);
        new class ([$this->other]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_LOCAL;
            }
        };
    }


    /**
     */
    public function testAnyNamespacePassingTargetSucceeds(): void
    {
        $o = new class ([$this->target]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_ANY;
            }
        };

        $this->addToAssertionCount(1);
    }


    /**
     */
    public function testAnyNamespacePassingOtherSucceeds(): void
    {
        $o = new class ([$this->other]) extends ExtendableElement {
            public function getNamespace()
            {
                return C::XS_ANY_NS_ANY;
            }
        };

        $this->addToAssertionCount(1);
    }
}
