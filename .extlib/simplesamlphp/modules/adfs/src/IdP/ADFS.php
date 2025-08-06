<?php

declare(strict_types=1);

namespace SimpleSAML\Module\adfs\IdP;

use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\HTTP\RunnableResponse;
use SimpleSAML\IdP;
use SimpleSAML\Logger;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module;
use SimpleSAML\Utils;
use SimpleSAML\XHTML\Template;
use Symfony\Component\HttpFoundation\Request;

class ADFS
{
    /**
     * @param \SimpleSAML\IdP $idp
     * @throws \SimpleSAML\Error\MetadataNotFound
     */
    public static function receiveAuthnRequest(Request $request, IdP $idp): RunnableResponse
    {
        parse_str($request->server->get('QUERY_STRING'), $query);

        $requestid = $query['wctx'] ?? null;
        $issuer = $query['wtrealm'];

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spMetadata = $metadata->getMetaDataConfig($issuer, 'adfs-sp-remote');

        Logger::info('ADFS - IdP.prp: Incoming Authentication request: ' . $issuer . ' id ' . $requestid);

        $state = [
            'Responder' => [ADFS::class, 'sendResponse'],
            'SPMetadata' => $spMetadata->toArray(),
            'ForceAuthn' => false,
            'isPassive' => false,
            'adfs:wctx' => $requestid,
            'adfs:wreply' => false
        ];

        if (isset($query['wreply']) && !empty($query['wreply'])) {
            $httpUtils = new Utils\HTTP();
            $state['adfs:wreply'] = $httpUtils->checkURLAllowed($query['wreply']);
        }

        return new RunnableResponse([$idp, 'handleAuthenticationRequest'], [&$state]);
    }


    /**
     * @param string $issuer
     * @param string $target
     * @param string $nameid
     * @param array $attributes
     * @param int $assertionLifetime
     * @return string
     */
    private static function generateResponse(
        string $issuer,
        string $target,
        string $nameid,
        array $attributes,
        int $assertionLifetime
    ): string {
        $httpUtils = new Utils\HTTP();
        $randomUtils = new Utils\Random();
        $timeUtils = new Utils\Time();

        $issueInstant = $timeUtils->generateTimestamp();
        $notBefore = $timeUtils->generateTimestamp(time() - 30);
        $assertionExpire = $timeUtils->generateTimestamp(time() + $assertionLifetime);
        $assertionID = $randomUtils->generateID();
        $nameidFormat = 'http://schemas.xmlsoap.org/claims/UPN';
        $nameid = htmlspecialchars($nameid);

        if ($httpUtils->isHTTPS()) {
            $method = Constants::AC_PASSWORD_PROTECTED_TRANSPORT;
        } else {
            $method = Constants::AC_PASSWORD;
        }

        $result = <<<MSG
<wst:RequestSecurityTokenResponse xmlns:wst="http://schemas.xmlsoap.org/ws/2005/02/trust">
    <wst:RequestedSecurityToken>
        <saml:Assertion Issuer="$issuer" IssueInstant="$issueInstant" AssertionID="$assertionID" MinorVersion="1" MajorVersion="1" xmlns:saml="urn:oasis:names:tc:SAML:1.0:assertion">
            <saml:Conditions NotOnOrAfter="$assertionExpire" NotBefore="$notBefore">
                <saml:AudienceRestrictionCondition>
                    <saml:Audience>$target</saml:Audience>
                </saml:AudienceRestrictionCondition>
            </saml:Conditions>
            <saml:AuthenticationStatement AuthenticationMethod="$method" AuthenticationInstant="$issueInstant">
                <saml:Subject>
                    <saml:NameIdentifier Format="$nameidFormat">$nameid</saml:NameIdentifier>
                </saml:Subject>
            </saml:AuthenticationStatement>
            <saml:AttributeStatement>
                <saml:Subject>
                    <saml:NameIdentifier Format="$nameidFormat">$nameid</saml:NameIdentifier>
                </saml:Subject>
MSG;

        $attrUtils = new Utils\Attributes();
        foreach ($attributes as $name => $values) {
            if ((!is_array($values)) || (count($values) == 0)) {
                continue;
            }

            list($namespace, $name) = $attrUtils->getAttributeNamespace(
                $name,
                'http://schemas.xmlsoap.org/claims'
            );
            $namespace = htmlspecialchars($namespace);
            $name = htmlspecialchars($name);
            foreach ($values as $value) {
                if ((!isset($value)) || ($value === '')) {
                    continue;
                }
                $value = htmlspecialchars($value);

                $result .= <<<MSG
                <saml:Attribute AttributeNamespace="$namespace" AttributeName="$name">
                    <saml:AttributeValue>$value</saml:AttributeValue>
                </saml:Attribute>
MSG;
            }
        }

        $result .= <<<MSG
            </saml:AttributeStatement>
        </saml:Assertion>
   </wst:RequestedSecurityToken>
   <wsp:AppliesTo xmlns:wsp="http://schemas.xmlsoap.org/ws/2004/09/policy">
       <wsa:EndpointReference xmlns:wsa="http://schemas.xmlsoap.org/ws/2004/08/addressing">
           <wsa:Address>$target</wsa:Address>
       </wsa:EndpointReference>
   </wsp:AppliesTo>
</wst:RequestSecurityTokenResponse>
MSG;

        return $result;
    }


    /**
     * @param string $response
     * @param string $key
     * @param string $cert
     * @param string $algo
     * @param string|null $passphrase
     * @return string
     */
    private static function signResponse(
        string $response,
        string $key,
        string $cert,
        string $algo,
        string $passphrase = null
    ): string {
        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->idKeys = ['AssertionID'];
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $responsedom = DOMDocumentFactory::fromString(str_replace("\r", "", $response));
        $firstassertionroot = $responsedom->getElementsByTagName('Assertion')->item(0);

        if (is_null($firstassertionroot)) {
            throw new Exception("No assertion found in response.");
        }

        $objXMLSecDSig->addReferenceList(
            [$firstassertionroot],
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N],
            ['id_name' => 'AssertionID']
        );

        $objKey = new XMLSecurityKey($algo, ['type' => 'private']);
        if (is_string($passphrase)) {
            $objKey->passphrase = $passphrase;
        }
        $objKey->loadKey($key, true);
        $objXMLSecDSig->sign($objKey);
        if ($cert) {
            $public_cert = file_get_contents($cert);
            $objXMLSecDSig->add509Cert($public_cert, true);
        }

        /** @var \DOMElement $objXMLSecDSig->sigNode */
        $newSig = $responsedom->importNode($objXMLSecDSig->sigNode, true);
        $firstassertionroot->appendChild($newSig);
        return $responsedom->saveXML();
    }


    /**
     * @param string $wreply
     * @param string $wresult
     * @param ?string $wctx
     */
    private static function postResponse(string $wreply, string $wresult, ?string $wctx): void
    {
        $config = Configuration::getInstance();
        $t = new Template($config, 'adfs:postResponse.twig');
        $t->data['wreply'] = $wreply;
        $t->data['wresult'] = $wresult;
        $t->data['wctx'] = $wctx;
        $t->send();
        // Idp->postAuthProc expects this function to exit
        exit();
    }


    /**
     * Get the metadata of a given hosted ADFS IdP.
     *
     * @param string $entityid The entity ID of the hosted ADFS IdP whose metadata we want to fetch.
     *
     * @return array
     * @throws \SimpleSAML\Error\Exception
     * @throws \SimpleSAML\Error\MetadataNotFound
     */
    public static function getHostedMetadata(string $entityid): array
    {
        $handler = MetaDataStorageHandler::getMetadataHandler();
        $cryptoUtils = new Utils\Crypto();
        $config = $handler->getMetaDataConfig($entityid, 'adfs-idp-hosted');

        $endpoint = Module::getModuleURL('adfs/idp/prp.php');
        $metadata = [
            'metadata-set' => 'adfs-idp-hosted',
            'entityid' => $entityid,
            'SingleSignOnService' => [
                [
                    'Binding' => Constants::BINDING_HTTP_REDIRECT,
                    'Location' => $endpoint,
                ]
            ],
            'SingleLogoutService' => [
                'Binding' => Constants::BINDING_HTTP_REDIRECT,
                'Location' => $endpoint,
            ],
            'NameIDFormat' => $config->getOptionalString('NameIDFormat', Constants::NAMEID_TRANSIENT),
            'contacts' => [],
        ];

        // add certificates
        $keys = [];
        $certInfo = $cryptoUtils->loadPublicKey($config, false, 'new_');
        $hasNewCert = false;
        if ($certInfo !== null) {
            $keys[] = [
                'type' => 'X509Certificate',
                'signing' => true,
                'encryption' => true,
                'X509Certificate' => $certInfo['certData'],
                'prefix' => 'new_',
            ];
            $hasNewCert = true;
        }

        /** @var array $certInfo */
        $certInfo = $cryptoUtils->loadPublicKey($config, true);
        $keys[] = [
            'type' => 'X509Certificate',
            'signing' => true,
            'encryption' => $hasNewCert === false,
            'X509Certificate' => $certInfo['certData'],
            'prefix' => '',
        ];

        if ($config->hasValue('https.certificate')) {
            /** @var array $httpsCert */
            $httpsCert = $cryptoUtils->loadPublicKey($config, true, 'https.');
            $keys[] = [
                'type' => 'X509Certificate',
                'signing' => true,
                'encryption' => false,
                'X509Certificate' => $httpsCert['certData'],
                'prefix' => 'https.'
            ];
        }
        $metadata['keys'] = $keys;

        // add organization information
        if ($config->hasValue('OrganizationName')) {
            $metadata['OrganizationName'] = $config->getLocalizedString('OrganizationName');
            $metadata['OrganizationDisplayName'] = $config->getOptionalLocalizedString(
                'OrganizationDisplayName',
                $metadata['OrganizationName']
            );

            if (!$config->hasValue('OrganizationURL')) {
                throw new Error\Exception('If OrganizationName is set, OrganizationURL must also be set.');
            }
            $metadata['OrganizationURL'] = $config->getLocalizedString('OrganizationURL');
        }

        // add scope
        if ($config->hasValue('scope')) {
            $metadata['scope'] = $config->getArray('scope');
        }

        // add extensions
        if ($config->hasValue('EntityAttributes')) {
            $metadata['EntityAttributes'] = $config->getArray('EntityAttributes');

            // check for entity categories
            if (Utils\Config\Metadata::isHiddenFromDiscovery($metadata)) {
                $metadata['hide.from.discovery'] = true;
            }
        }

        if ($config->hasValue('UIInfo')) {
            $metadata['UIInfo'] = $config->getArray('UIInfo');
        }

        if ($config->hasValue('DiscoHints')) {
            $metadata['DiscoHints'] = $config->getArray('DiscoHints');
        }

        if ($config->hasValue('RegistrationInfo')) {
            $metadata['RegistrationInfo'] = $config->getArray('RegistrationInfo');
        }

        // add contact information
        $globalConfig = Configuration::getInstance();
        $email = $globalConfig->getOptionalString('technicalcontact_email', null);
        if ($email !== null && $email !== 'na@example.org') {
            $contact = [
                'emailAddress' => $email,
                'givenName' => $globalConfig->getOptionalString('technicalcontact_name', null),
                'contactType' => 'technical',
            ];
            $metadata['contacts'][] = Utils\Config\Metadata::getContact($contact);
        }

        return $metadata;
    }


    /**
     * @param array $state
     * @throws \Exception
     */
    public static function sendResponse(array $state): void
    {
        $spMetadata = $state["SPMetadata"];
        $spEntityId = $spMetadata['entityid'];
        $spMetadata = Configuration::loadFromArray(
            $spMetadata,
            '$metadata[' . var_export($spEntityId, true) . ']'
        );

        $attributes = $state['Attributes'];

        $nameidattribute = $spMetadata->getValue('simplesaml.nameidattribute');
        if (!empty($nameidattribute)) {
            if (!array_key_exists($nameidattribute, $attributes)) {
                throw new Exception('simplesaml.nameidattribute does not exist in resulting attribute set');
            }
            $nameid = $attributes[$nameidattribute][0];
        } else {
            $randomUtils = new Utils\Random();
            $nameid = $randomUtils->generateID();
        }

        $idp = IdP::getByState($state);
        $idpMetadata = $idp->getConfig();
        $idpEntityId = $idpMetadata->getString('entityid');

        $idp->addAssociation([
            'id' => 'adfs:' . $spEntityId,
            'Handler' => ADFS::class,
            'adfs:entityID' => $spEntityId,
        ]);

        $assertionLifetime = $spMetadata->getOptionalInteger('assertion.lifetime', null);
        if ($assertionLifetime === null) {
            $assertionLifetime = $idpMetadata->getOptionalInteger('assertion.lifetime', 300);
        }

        $response = ADFS::generateResponse($idpEntityId, $spEntityId, $nameid, $attributes, $assertionLifetime);

        $configUtils = new Utils\Config();
        $privateKeyFile = $configUtils->getCertPath($idpMetadata->getString('privatekey'));
        $certificateFile = $configUtils->getCertPath($idpMetadata->getString('certificate'));
        $passphrase = $idpMetadata->getOptionalString('privatekey_pass', null);

        $algo = $spMetadata->getOptionalString('signature.algorithm', null);
        if ($algo === null) {
            $algo = $idpMetadata->getOptionalString('signature.algorithm', XMLSecurityKey::RSA_SHA256);
        }
        $wresult = ADFS::signResponse($response, $privateKeyFile, $certificateFile, $algo, $passphrase);

        $wctx = $state['adfs:wctx'];
        $wreply = $state['adfs:wreply'] ? : $spMetadata->getValue('prp');
        ADFS::postResponse($wreply, $wresult, $wctx);
    }


    /**
     * @param \SimpleSAML\IdP $idp
     * @param array $state
     */
    public static function sendLogoutResponse(IdP $idp, array $state): void
    {
        // NB:: we don't know from which SP the logout request came from
        $idpMetadata = $idp->getConfig();
        $httpUtils = new Utils\HTTP();
        $httpUtils->redirectTrustedURL(
            $idpMetadata->getOptionalString('redirect-after-logout', $httpUtils->getBaseURL())
        );
    }


    /**
     * @param \SimpleSAML\IdP $idp
     * @throws \Exception
     */
    public static function receiveLogoutMessage(IdP $idp): void
    {
        // if a redirect is to occur based on wreply, we will redirect to url as
        // this implies an override to normal sp notification
        if (isset($_GET['wreply']) && !empty($_GET['wreply'])) {
            $httpUtils = new Utils\HTTP();
            $idp->doLogoutRedirect($httpUtils->checkURLAllowed($_GET['wreply']));
            throw new Exception("Code should never be reached");
        }

        $state = [
            'Responder' => [ADFS::class, 'sendLogoutResponse'],
        ];
        $assocId = null;
        // TODO: verify that this is really no problem for:
        //       a) SSP, because there's no caller SP.
        //       b) ADFS SP because caller will be called back..
        $idp->handleLogoutRequest($state, $assocId);
    }


    /**
     * accepts an association array, and returns a URL that can be accessed to terminate the association
     *
     * @param \SimpleSAML\IdP $idp
     * @param array $association
     * @param string $relayState
     * @return string
     */
    public static function getLogoutURL(IdP $idp, array $association, string $relayState): string
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spMetadata = $metadata->getMetaDataConfig($association['adfs:entityID'], 'adfs-sp-remote');
        $returnTo = Module::getModuleURL(
            'adfs/idp/prp.php?assocId=' . urlencode($association["id"]) . '&relayState=' . urlencode($relayState)
        );
        return $spMetadata->getValue('prp') . '?wa=wsignoutcleanup1.0&wreply=' . urlencode($returnTo);
    }
}
