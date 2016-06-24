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

// @codingStandardsIgnoreStart
/**
 * This function is mostly a direct cut and paste from SimpleSAMLPHP with
 * only minor bootstrapping differences. As such it is formatted according
 * to the SSPHP rules not moodles so we ignore it here. This is to make it
 * easier when upgrading SSPHP.
 */
function auth_saml2_get_sp_metadata() {

    global $saml2auth, $CFG;

    $sourceId = $saml2auth->spname;

    $file = $saml2auth->certdir . $saml2auth->spname . '.xml';
    if (file_exists($file)) {
        $xml = file_get_contents($file);
        return $xml;
    }

    $auth = new SimpleSAML_Auth_Simple($sourceId);
    $config = SimpleSAML_Configuration::getInstance();
    $source = SimpleSAML_Auth_Source::getById($sourceId);
    if ($source === NULL) {
        throw new SimpleSAML_Error_NotFound('Could not find authentication source with id ' . $sourceId);
    }

    if (!($source instanceof sspmod_saml_Auth_Source_SP)) {
        throw new SimpleSAML_Error_NotFound('Source isn\'t a SAML SP: ' . var_export($sourceId, TRUE));
    }

    $entityId = $source->getEntityId();
    $spconfig = $source->getMetadata();
    $store = SimpleSAML_Store::getInstance();

    $metaArray20 = array();

    $slosvcdefault = array(
        SAML2_Const::BINDING_HTTP_REDIRECT,
        SAML2_Const::BINDING_SOAP,
    );

    $slob = $spconfig->getArray('SingleLogoutServiceBinding', $slosvcdefault);
    $slol = "$CFG->wwwroot/auth/saml2/sp/saml2-logout.php/{$sourceId}";

    foreach ($slob as $binding) {
        if ($binding == SAML2_Const::BINDING_SOAP && !($store instanceof SimpleSAML_Store_SQL)) {
            /* We cannot properly support SOAP logout. */
            continue;
        }
        $metaArray20['SingleLogoutService'][] = array(
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
        $assertionsconsumerservicesdefault[] = 	'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
    }

    $assertionsconsumerservices = $spconfig->getArray('acs.Bindings', $assertionsconsumerservicesdefault);

    $index = 0;
    $eps = array();
    foreach ($assertionsconsumerservices as $services) {

        $acsArray = array('index' => $index);
        switch ($services) {
        case 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST':
            $acsArray['Binding'] = SAML2_Const::BINDING_HTTP_POST;
            $acsArray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml2-acs.php/{$sourceId}";
            break;
        case 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post';
            $acsArray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml1-acs.php/{$sourceId}";
            break;
        case 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
            $acsArray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml2-acs.php/{$sourceId}";
            break;
        case 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01';
            $acsArray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml1-acs.php/{$sourceId}";
            break;
        case 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser':
            $acsArray['Binding'] = 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
            $acsArray['Location'] = "$CFG->wwwroot/auth/saml2/sp/saml2-acs.php/{$sourceId}";
            $acsArray['hoksso:ProtocolBinding'] = SAML2_Const::BINDING_HTTP_REDIRECT;
            break;
        }
        $eps[] = $acsArray;
        $index++;
    }

    $metaArray20['AssertionConsumerService'] = $eps;

    $keys = array();
    $certInfo = SimpleSAML\Utils\Crypto::loadPublicKey($spconfig, FALSE, 'new_');
    if ($certInfo !== NULL && array_key_exists('certData', $certInfo)) {
        $hasNewCert = TRUE;

        $certData = $certInfo['certData'];

        $keys[] = array(
            'type' => 'X509Certificate',
            'signing' => TRUE,
            'encryption' => TRUE,
            'X509Certificate' => $certInfo['certData'],
        );
    } else {
        $hasNewCert = FALSE;
    }

    $certInfo = SimpleSAML\Utils\Crypto::loadPublicKey($spconfig);
    if ($certInfo !== NULL && array_key_exists('certData', $certInfo)) {
        $certData = $certInfo['certData'];

        $keys[] = array(
            'type' => 'X509Certificate',
            'signing' => TRUE,
            'encryption' => ($hasNewCert ? FALSE : TRUE),
            'X509Certificate' => $certInfo['certData'],
        );
    } else {
        $certData = NULL;
    }

    $format = $spconfig->getString('NameIDPolicy', NULL);
    if ($format !== NULL) {
        $metaArray20['NameIDFormat'] = $format;
    }

    $name = $spconfig->getLocalizedString('name', NULL);
    $attributes = $spconfig->getArray('attributes', array());

    if ($name !== NULL && !empty($attributes)) {
        $metaArray20['name'] = $name;
        $metaArray20['attributes'] = $attributes;
        $metaArray20['attributes.required'] = $spconfig->getArray('attributes.required', array());

        if (empty($metaArray20['attributes.required'])) {
            unset($metaArray20['attributes.required']);
        }

        $description = $spconfig->getArray('description', NULL);
        if ($description !== NULL) {
            $metaArray20['description'] = $description;
        }

        $nameFormat = $spconfig->getString('attributes.NameFormat', NULL);
        if ($nameFormat !== NULL) {
            $metaArray20['attributes.NameFormat'] = $nameFormat;
        }
    }

    // add organization info
    $orgName = $spconfig->getLocalizedString('OrganizationName', NULL);
    if ($orgName !== NULL) {
        $metaArray20['OrganizationName'] = $orgName;

        $metaArray20['OrganizationDisplayName'] = $spconfig->getLocalizedString('OrganizationDisplayName', NULL);
        if ($metaArray20['OrganizationDisplayName'] === NULL) {
            $metaArray20['OrganizationDisplayName'] = $orgName;
        }

        $metaArray20['OrganizationURL'] = $spconfig->getLocalizedString('OrganizationURL', NULL);
        if ($metaArray20['OrganizationURL'] === NULL) {
            throw new SimpleSAML_Error_Exception('If OrganizationName is set, OrganizationURL must also be set.');
        }
    }

    if ($spconfig->hasValue('contacts')) {
        $contacts = $spconfig->getArray('contacts');
        foreach ($contacts as $contact) {
            $metaArray20['contacts'][] = \SimpleSAML\Utils\Config\Metadata::getContact($contact);
        }
    }

    // add technical contact
    $email = $config->getString('technicalcontact_email', 'na@example.org', FALSE);
    if ($email && $email !== 'na@example.org') {
        $techcontact['emailAddress'] = $email;
        $techcontact['name'] = $config->getString('technicalcontact_name', NULL);
        $techcontact['contactType'] = 'technical';
        $metaArray20['contacts'][] = \SimpleSAML\Utils\Config\Metadata::getContact($techcontact);
    }

    // add certificate
    if (count($keys) === 1) {
        $metaArray20['certData'] = $keys[0]['X509Certificate'];
    } elseif (count($keys) > 1) {
        $metaArray20['keys'] = $keys;
    }

    // add EntityAttributes extension
    if ($spconfig->hasValue('EntityAttributes')) {
        $metaArray20['EntityAttributes'] = $spconfig->getArray('EntityAttributes');
    }

    // add UIInfo extension
    if ($spconfig->hasValue('UIInfo')) {
        $metaArray20['UIInfo'] = $spconfig->getArray('UIInfo');
    }

    // add RegistrationInfo extension
    if ($spconfig->hasValue('RegistrationInfo')) {
        $metaArray20['RegistrationInfo'] = $spconfig->getArray('RegistrationInfo');
    }

    // add signature options
    if ($spconfig->hasValue('WantAssertionsSigned')) {
        $metaArray20['saml20.sign.assertion'] = $spconfig->getBoolean('WantAssertionsSigned');
    }
    if ($spconfig->hasValue('redirect.sign')) {
        $metaArray20['redirect.validate'] = $spconfig->getBoolean('redirect.sign');
    } elseif ($spconfig->hasValue('sign.authnrequest')) {
        $metaArray20['validate.authnrequest'] = $spconfig->getBoolean('sign.authnrequest');
    }

    $supported_protocols = array('urn:oasis:names:tc:SAML:1.1:protocol', SAML2_Const::NS_SAMLP);

    $metaArray20['metadata-set'] = 'saml20-sp-remote';
    $metaArray20['entityid'] = $entityId;

    $metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($entityId);
    $metaBuilder->addMetadataSP20($metaArray20, $supported_protocols);
    $metaBuilder->addOrganizationInfo($metaArray20);

    $xml = $metaBuilder->getEntityDescriptorText();

    unset($metaArray20['UIInfo']);
    unset($metaArray20['metadata-set']);
    unset($metaArray20['entityid']);

    // sanitize the attributes array to remove friendly names
    if (isset($metaArray20['attributes']) && is_array($metaArray20['attributes'])) {
        $metaArray20['attributes'] = array_values($metaArray20['attributes']);
    }

    /* Sign the metadata if enabled. */
    $xml = SimpleSAML_Metadata_Signer::sign($xml, $spconfig->toArray(), 'SAML 2 SP');

    // Store the file so it is exactly the same next time.
    file_put_contents($file, $xml);

    return $xml;
}

// @codingStandardsIgnoreEnd

