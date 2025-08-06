<?php

declare(strict_types=1);

namespace SimpleSAML\XML;

/**
 * Various XML constants.
 *
 * @package simplesamlphp/xml-common
 */
class Constants
{
    /**
     * The namespace fox XML.
     */
    public const NS_XML = 'http://www.w3.org/XML/1998/namespace';

    /**
     * The namespace fox XML schema.
     */
    public const NS_XS = 'http://www.w3.org/2001/XMLSchema';

    /**
     * The namespace for XML schema instance.
     */
    public const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * The namespace-attribute values for xs:any elements
     */
    public const XS_ANY_NS_ANY = '##any';
    public const XS_ANY_NS_LOCAL = '##local';
    public const XS_ANY_NS_OTHER = '##other';
    public const XS_ANY_NS_TARGET = '##targetNamespace';

    public const XS_ANY_NS = [
        self::XS_ANY_NS_ANY,
        self::XS_ANY_NS_LOCAL,
        self::XS_ANY_NS_OTHER,
        self::XS_ANY_NS_TARGET,
    ];

    /**
     * The processContents-attribute values for xs:any elements
     */
    public const XS_ANY_PROCESS_LAX = 'lax';
    public const XS_ANY_PROCESS_SKIP = 'skip';
    public const XS_ANY_PROCESS_STRICT = 'strict';

    public const XS_ANY_PROCESS = [
        self::XS_ANY_PROCESS_LAX,
        self::XS_ANY_PROCESS_SKIP,
        self::XS_ANY_PROCESS_STRICT,
    ];
}
