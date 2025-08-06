<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML\ec;

use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XMLSecurity\Constants as C;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/xml-security
 */
abstract class AbstractEcElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = C::C14N_EXCLUSIVE_WITHOUT_COMMENTS;

    /** @var string */
    public const NS_PREFIX = 'ec';
}
