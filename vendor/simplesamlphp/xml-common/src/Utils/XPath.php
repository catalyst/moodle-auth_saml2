<?php

declare(strict_types=1);

namespace SimpleSAML\XML\Utils;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants as C;

/**
 * XPath helper functions for the XML library.
 *
 * @package simplesamlphp/xml-common
 */
class XPath
{
    /**
     * Get an instance of DOMXPath associated with a DOMNode
     *
     * @param \DOMNode $node The associated node
     * @return \DOMXPath
     */
    public static function getXPath(DOMNode $node): DOMXPath
    {
        static $xpCache = null;

        if ($node instanceof DOMDocument) {
            $doc = $node;
        } else {
            $doc = $node->ownerDocument;
            Assert::notNull($doc);
        }

        if ($xpCache === null || !$xpCache->document->isSameNode($doc)) {
            $xpCache = new DOMXPath($doc);
        }

        $xpCache->registerNamespace('xml', C::NS_XML);
        $xpCache->registerNamespace('xs', C::NS_XS);

        // Enrich with ancestor-declared prefixes for this document context.
        $prefixToUri = self::registerAncestorNamespaces($xpCache, $node);

        // Single, bounded subtree scan to pick up descendant-only declarations.
        self::registerSubtreePrefixes($xpCache, $node, $prefixToUri);

        return $xpCache;
    }


    /**
     * Walk from the given node up to the document element, registering all prefixed xmlns declarations.
     *
     * Safety:
     * - Only attributes in the XMLNS namespace (http://www.w3.org/2000/xmlns/).
     * - Skip default xmlns (localName === 'xmlns') because XPath requires prefixes.
     * - Skip empty URIs.
     * - Do not override core 'xml' and 'xs' prefixes (already bound).
     * - Nearest binding wins during this pass (prefixes are added once).
     *
     * @param \DOMXPath $xp
     * @param \DOMNode  $node
     * @return array<string,string> Map of prefix => namespace URI that are bound after this pass
     */
    private static function registerAncestorNamespaces(DOMXPath $xp, DOMNode $node): array
    {
        // Track prefix => uri to feed into subtree scan. Seed with core bindings.
        $prefixToUri = [
            'xml' => C::NS_XML,
            'xs'  => C::NS_XS,
        ];

        // Start from the nearest element (or documentElement if a DOMDocument is passed).
        $current = $node instanceof DOMDocument
            ? $node->documentElement
            : ($node instanceof DOMElement ? $node : $node->parentNode);

        $steps = 0;

        while ($current instanceof DOMElement) {
            if (++$steps > C::UNBOUNDED_LIMIT) {
                throw new RuntimeException(__METHOD__ . ': exceeded ancestor traversal limit');
            }

            if ($current->hasAttributes()) {
                foreach ($current->attributes as $attr) {
                    if ($attr->namespaceURI !== 'http://www.w3.org/2000/xmlns/') {
                        continue;
                    }
                    $prefix = $attr->localName;
                    $uri = (string) $attr->nodeValue;

                    if (
                        $prefix === null || $prefix === '' ||
                        $prefix === 'xmlns' || $uri === '' ||
                        isset($prefixToUri[$prefix])
                    ) {
                        continue;
                    }

                    $xp->registerNamespace($prefix, $uri);
                    $prefixToUri[$prefix] = $uri;
                }
            }

            $current = $current->parentNode;
        }

        return $prefixToUri;
    }


    /**
     * Single-pass subtree scan from the context element to bind prefixes used only on descendants.
     * - Never rebind an already-registered prefix (collision-safe).
     * - Skips 'xmlns' and empty URIs.
     * - Bounded by UNBOUNDED_LIMIT.
     *
     * @param \DOMXPath $xp
     * @param \DOMNode  $node
     * @param array<string,string> $prefixToUri
     */
    private static function registerSubtreePrefixes(DOMXPath $xp, DOMNode $node, array $prefixToUri): void
    {
        $root = $node instanceof DOMDocument
            ? $node->documentElement
            : ($node instanceof DOMElement ? $node : $node->parentNode);

        if (!$root instanceof DOMElement) {
            return;
        }

        $visited = 0;

        /** @var array<\DOMElement> $queue */
        $queue = [$root];

        while ($queue) {
            /** @var \DOMElement $el */
            $el = array_shift($queue);

            if (++$visited > C::UNBOUNDED_LIMIT) {
                throw new RuntimeException(__METHOD__ . ': exceeded subtree traversal limit');
            }

            // Element prefix
            if ($el->prefix && !isset($prefixToUri[$el->prefix])) {
                $uri = $el->namespaceURI;
                if (is_string($uri) && $uri !== '') {
                    $xp->registerNamespace($el->prefix, $uri);
                    $prefixToUri[$el->prefix] = $uri;
                }
            }

            // Attribute prefixes (excluding xmlns)
            if ($el->hasAttributes()) {
                foreach ($el->attributes as $attr) {
                    if (
                        $attr->prefix &&
                        $attr->prefix !== 'xmlns' &&
                        !isset($prefixToUri[$attr->prefix])
                    ) {
                        $uri = $attr->namespaceURI;
                        if (is_string($uri) && $uri !== '') {
                            $xp->registerNamespace($attr->prefix, $uri);
                            $prefixToUri[$attr->prefix] = $uri;
                        }
                    } else {
                        // Optional: collision detection (same prefix, different URI)
                        // if ($prefixToUri[$pfx] !== $attr->namespaceURI) {
                        //     // Default: skip rebind; could log a debug message here.
                        // }
                    }
                }
            }

            // Enqueue children (only DOMElement to keep types precise)
            foreach ($el->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $queue[] = $child;
                }
            }
        }
    }


    /**
     * Do an XPath query on an XML node.
     *
     * @param \DOMNode $node  The XML node.
     * @param string $query The query.
     * @param \DOMXPath $xpCache The DOMXPath object
     * @return array<int<0, max>, \DOMNameSpaceNode|\DOMNode|null> Array with matching DOM nodes.
     */
    public static function xpQuery(DOMNode $node, string $query, DOMXPath $xpCache): array
    {
        $ret = [];

        $results = $xpCache->query($query, $node);
        Assert::notFalse($results, 'Malformed XPath query or invalid contextNode provided.');

        for ($i = 0; $i < $results->length; $i++) {
            $ret[$i] = $results->item($i);
        }

        return $ret;
    }
}
