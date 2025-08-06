<?php

declare(strict_types=1);

namespace SimpleSAML\Module\adfs\SAML2\XML\fed;

use DOMElement;
use SAML2\XML\md\RoleDescriptor;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SecurityTokenServiceType RoleDescriptor.
 *
 * @package SimpleSAMLphp
 */

class SecurityTokenServiceType extends RoleDescriptor
{
    /**
     * List of supported protocols.
     *
     * @var string[] $protocolSupportEnumeration
     */
    public array $protocolSupportEnumeration = [Constants::NS_FED];

    /**
     * The Location of Services.
     *
     * @var string $Location
     */
    public string $Location = '';


    /**
     * Initialize a SecurityTokenServiceType element.
     *
     * @param \DOMElement|null $xml  The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('RoleDescriptor', $xml);
        parent::setProtocolSupportEnumeration($this->protocolSupportEnumeration);

        if ($xml === null) {
            return;
        }
    }

    /**
     * Convert this SecurityTokenServiceType RoleDescriptor to XML.
     *
     * @param \DOMElement $parent  The element we should add this contact to.
     * @return \DOMElement  The new ContactPerson-element.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->Location, 'Location not set');

        $e = parent::toXML($parent);
        $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:fed', Constants::NS_FED);
        $e->setAttributeNS(Constants::NS_XSI, 'xsi:type', 'fed:SecurityTokenServiceType');
        TokenTypesOffered::appendXML($e);
        Endpoint::appendXML($e, 'SecurityTokenServiceEndpoint', $this->Location);
        Endpoint::appendXML($e, 'fed:PassiveRequestorEndpoint', $this->Location);

        return $e;
    }


    /**
     * Get the location of this service.
     *
     * @return string The full URL where this service can be reached.
     */
    public function getLocation(): string
    {
        Assert::notEmpty($this->Location, 'Location not set');

        return $this->Location;
    }


    /**
     * Set the location of this service.
     *
     * @param string $location The full URL where this service can be reached.
     */
    public function setLocation(string $location): void
    {
        $this->Location = $location;
    }
}
