<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * local lib
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @SuppressWarnings(PHPMD)
 */
function auth_saml2_get_sp_metadata() {

    global $saml2auth, $CFG;

    $auth = new SimpleSAML_Auth_Simple($saml2auth->spname);
    $config = SimpleSAML_Configuration::getInstance();
    $sourceid = $saml2auth->spname;
    $source = SimpleSAML_Auth_Source::getById($sourceid);
    if ($source === null) {
        throw new SimpleSAML_Error_NotFound('Could not find authentication source with id ' . $sourceid);
    }

    if (!($source instanceof sspmod_saml_Auth_Source_SP)) {
        throw new SimpleSAML_Error_NotFound('Source isn\'t a SAML SP: ' . var_export($sourceid, true));
    }

    $entityid = $source->getentityid();
    $spconfig = $source->getMetadata();
    $store = SimpleSAML_Store::getInstance();

    $metaarray20 = array();

    $slosvcdefault = array(
        SAML2_Const::BINDING_HTTP_REDIRECT,
        SAML2_Const::BINDING_SOAP,
    );

    $slob = $spconfig->getArray('SingleLogoutServiceBinding', $slosvcdefault);
    $slol = "$CFG->wwwroot/auth/saml2/sp/saml2-logout.php/{$sourceid}";

    foreach ($slob as $binding) {
        if ($binding == SAML2_Const::BINDING_SOAP && !($store instanceof SimpleSAML_Store_SQL)) {
            /* We cannot properly support SOAP logout. */
            continue;
        }
        $metaarray20['SingleLogoutService'][] = array(
            'Binding' => $binding,
            'Location' => $slol,
        );
    }

    $assertionsconsumerservicesdefault = array(
        'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
        'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
        'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
    );

    if ($spconfig->getString('ProtocolBinding', '') == 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser') {
        $assertionsconsumerservicesdefault[] = 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
    }

    $assertionsconsumerservices = $spconfig->getArray('acs.Bindings', $assertionsconsumerservicesdefault);

    $index = 0;
    $eps = array();
    foreach ($assertionsconsumerservices as $services) {

        $acsarray = array('index' => $index);
        switch ($services) {
            case 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST':
                $acsarray['Binding'] = SAML2_Const::BINDING_HTTP_POST;
                $acsarray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml2-acs.php/{$sourceid}";
                break;
            case 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post':
                $acsarray['Binding'] = 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post';
                $acsarray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml1-acs.php/{$sourceid}";
                break;
            case 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact':
                $acsarray['Binding'] = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
                $acsarray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml2-acs.php/{$sourceid}";
                break;
            case 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01':
                $acsarray['Binding'] = 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01';
                $acsarray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml1-acs.php/{$sourceid}";
                break;
            case 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser':
                $acsarray['Binding'] = 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
                $acsarray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml2-acs.php/{$sourceid}";
                $acsarray['hoksso:ProtocolBinding'] = SAML2_Const::BINDING_HTTP_REDIRECT;
                break;
        }
        $eps[] = $acsarray;
        $index++;
    }

    $metaarray20['AssertionConsumerService'] = $eps;

    $keys = array();
    $certinfo = SimpleSAML\Utils\Crypto::loadPublicKey($spconfig, false, 'new_');
    if ($certinfo !== null && array_key_exists('certdata', $certinfo)) {
        $hasnewcert = true;

        $certdata = $certinfo['certdata'];

        $keys[] = array(
            'type' => 'X509Certificate',
            'signing' => true,
            'encryption' => true,
            'X509Certificate' => $certinfo['certdata'],
        );
    } else {
        $hasnewcert = false;
    }

    $certinfo = SimpleSAML\Utils\Crypto::loadPublicKey($spconfig);
    if ($certinfo !== null && array_key_exists('certdata', $certinfo)) {
        $certdata = $certinfo['certdata'];

        $keys[] = array(
            'type' => 'X509Certificate',
            'signing' => true,
            'encryption' => ($hasnewcert ? false : true),
            'X509Certificate' => $certinfo['certdata'],
        );
    } else {
        $certdata = null;
    }

    $format = $spconfig->getString('NameIDPolicy', null);
    if ($format !== null) {
        $metaarray20['NameIDFormat'] = $format;
    }

    $name = $spconfig->getLocalizedString('name', null);
    $attributes = $spconfig->getArray('attributes', array());

    if ($name !== null && !empty($attributes)) {
        $metaarray20['name'] = $name;
        $metaarray20['attributes'] = $attributes;
        $metaarray20['attributes.required'] = $spconfig->getArray('attributes.required', array());

        if (empty($metaarray20['attributes.required'])) {
            unset($metaarray20['attributes.required']);
        }

        $description = $spconfig->getArray('description', null);
        if ($description !== null) {
            $metaarray20['description'] = $description;
        }

        $nameformat = $spconfig->getString('attributes.nameformat', null);
        if ($nameformat !== null) {
            $metaarray20['attributes.nameformat'] = $nameformat;
        }
    }

    // Add organization info.
    $orgname = $spconfig->getLocalizedString('OrganizationName', null);
    if ($orgname !== null) {
        $metaarray20['OrganizationName'] = $orgname;

        $metaarray20['OrganizationDisplayName'] = $spconfig->getLocalizedString('OrganizationDisplayName', null);
        if ($metaarray20['OrganizationDisplayName'] === null) {
            $metaarray20['OrganizationDisplayName'] = $orgname;
        }

        $metaarray20['OrganizationURL'] = $spconfig->getLocalizedString('OrganizationURL', null);
        if ($metaarray20['OrganizationURL'] === null) {
            throw new SimpleSAML_Error_Exception('If OrganizationName is set, OrganizationURL must also be set.');
        }
    }

    if ($spconfig->hasValue('contacts')) {
        $contacts = $spconfig->getArray('contacts');
        foreach ($contacts as $contact) {
            $metaarray20['contacts'][] = \SimpleSAML\Utils\Config\Metadata::getContact($contact);
        }
    }

    // Add technical contact.
    $email = $config->getString('technicalcontact_email', 'na@example.org', false);
    if ($email && $email !== 'na@example.org') {
        $techcontact['emailAddress'] = $email;
        $techcontact['name'] = $config->getString('technicalcontact_name', null);
        $techcontact['contactType'] = 'technical';
        $metaarray20['contacts'][] = \SimpleSAML\Utils\Config\Metadata::getContact($techcontact);
    }

    // Add certificate.
    if (count($keys) === 1) {
        $metaarray20['certdata'] = $keys[0]['X509Certificate'];
    } else if (count($keys) > 1) {
        $metaarray20['keys'] = $keys;
    }

    // Add EntityAttributes extension.
    if ($spconfig->hasValue('EntityAttributes')) {
        $metaarray20['EntityAttributes'] = $spconfig->getArray('EntityAttributes');
    }

    // Add UIInfo extension.
    if ($spconfig->hasValue('UIInfo')) {
        $metaarray20['UIInfo'] = $spconfig->getArray('UIInfo');
    }

    // Add RegistrationInfo extension.
    if ($spconfig->hasValue('RegistrationInfo')) {
        $metaarray20['RegistrationInfo'] = $spconfig->getArray('RegistrationInfo');
    }

    // Add signature options.
    if ($spconfig->hasValue('WantAssertionsSigned')) {
        $metaarray20['saml20.sign.assertion'] = $spconfig->getBoolean('WantAssertionsSigned');
    }
    if ($spconfig->hasValue('redirect.sign')) {
        $metaarray20['redirect.validate'] = $spconfig->getBoolean('redirect.sign');
    } else if ($spconfig->hasValue('sign.authnrequest')) {
        $metaarray20['validate.authnrequest'] = $spconfig->getBoolean('sign.authnrequest');
    }

    $supportedprotocols = array('urn:oasis:names:tc:SAML:1.1:protocol', SAML2_Const::NS_SAMLP);

    $metaarray20['metadata-set'] = 'saml20-sp-remote';
    $metaarray20['entityid'] = $entityid;

    $metabuilder = new SimpleSAML_Metadata_SAMLBuilder($entityid);
    $metabuilder->addMetadataSP20($metaarray20, $supportedprotocols);
    $metabuilder->addOrganizationInfo($metaarray20);

    $xml = $metabuilder->getEntityDescriptorText();

    unset($metaarray20['UIInfo']);
    unset($metaarray20['metadata-set']);
    unset($metaarray20['entityid']);

    // Sanitize the attributes array to remove friendly names.
    if (isset($metaarray20['attributes']) && is_array($metaarray20['attributes'])) {
        $metaarray20['attributes'] = array_values($metaarray20['attributes']);
    }

    /* Sign the metadata if enabled. */
    $xml = SimpleSAML_Metadata_Signer::sign($xml, $spconfig->toArray(), 'SAML 2 SP');

    return $xml;
}



