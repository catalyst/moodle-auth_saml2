<?php

declare(strict_types=1);

namespace SimpleSAML\XML;

use DOMElement;
use RuntimeException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Constants as C;

use function array_diff;
use function array_map;
use function array_search;
use function defined;
use function implode;
use function is_array;
use function is_string;
use function rtrim;
use function sprintf;

/**
 * Trait grouping common functionality for elements implementing the xs:any element.
 *
 * @package simplesamlphp/xml-common
 */
trait ExtendableElementTrait
{
    /** @var \SimpleSAML\XML\XMLElementInterface[] */
    protected array $elements = [];


    /**
     * Set an array with all elements present.
     *
     * @param array \SimpleSAML\XML\AbstractXMLElement[]
     */
    protected function setElements(array $elements): void
    {
        Assert::allIsInstanceOf($elements, XMLElementInterface::class);
        $namespace = $this->getNamespace();

        // Validate namespace value
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        Assert::true(is_array($namespace) || is_string($namespace));
        if (!is_array($namespace)) {
            // Must be one of the predefined values
            Assert::oneOf($namespace, C::XS_ANY_NS);
        } else {
            // Array must be non-empty and cannot contain ##any or ##other
            Assert::notEmpty($namespace);
            Assert::allNotSame($namespace, C::XS_ANY_NS_ANY);
            Assert::allNotSame($namespace, C::XS_ANY_NS_OTHER);
        }

        // Get namespaces for all elements
        $actual_namespaces = array_map(
            /**
             * @param \SimpleSAML\XML\AbstractXMLElement|\SimpleSAML\XML\Chunk $elt
             * @return string|null
             */
            function ($elt) {
                return ($elt instanceof Chunk) ? $elt->getNamespaceURI() : $elt::getNamespaceURI();
            },
            $elements
        );

        if ($namespace === C::XS_ANY_NS_LOCAL) {
            // If ##local then all namespaces must be null
            Assert::allNull($actual_namespaces);
        } elseif (is_array($namespace)) {
            // Make a local copy of the property that we can edit
            $allowed_namespaces = $namespace;

            // Replace the ##targetedNamespace with the actual namespace
            if (($key = array_search(C::XS_ANY_NS_TARGET, $allowed_namespaces)) !== false) {
                $allowed_namespaces[$key] = static::NS;
            }

            // Replace the ##local with null
            if (($key = array_search(C::XS_ANY_NS_LOCAL, $allowed_namespaces)) !== false) {
                $allowed_namespaces[$key] = null;
            }

            $diff = array_diff($actual_namespaces, $allowed_namespaces);
            Assert::isEmpty(
                $diff,
                sprintf(
                    'Elements from namespaces [ %s ] are not allowed inside a %s element.',
                    rtrim(implode(', ', $diff)),
                    static::NS,
                ),
            );
        } else {
            // All elements must be namespaced, ergo non-null
            Assert::allNotNull($actual_namespaces);

            if ($namespace === C::XS_ANY_NS_OTHER) {
                // Must be any namespace other than the parent element
                Assert::allNotSame($actual_namespaces, static::NS);
            } elseif ($namespace === C::XS_ANY_NS_TARGET) {
                // Must be the same namespace as the one of the parent element
                Assert::allSame($actual_namespaces, static::NS);
            }
        }

        $this->elements = $elements;
    }


    /**
     * Get an array with all elements present.
     *
     * @return \SimpleSAML\XML\XMLElementInterface[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }


    /**
     * @return array|string
     */
    public function getNamespace()
    {
        Assert::true(
            defined('static::NAMESPACE'),
            self::getClassName(static::class)
            . '::NAMESPACE constant must be defined and set to the namespace for the xs:any element.',
            RuntimeException::class,
        );

        return static::NAMESPACE;
    }
}
