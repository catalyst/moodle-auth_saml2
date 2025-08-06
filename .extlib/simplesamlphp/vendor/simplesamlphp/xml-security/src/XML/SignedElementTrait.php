<?php

declare(strict_types=1);

namespace SimpleSAML\XMLSecurity\XML;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmInterface;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;
use SimpleSAML\XMLSecurity\Exception\NoSignatureFoundException;
use SimpleSAML\XMLSecurity\Exception\RuntimeException;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\Key\AbstractKey;
use SimpleSAML\XMLSecurity\Key;
use SimpleSAML\XMLSecurity\Utils\Security;
use SimpleSAML\XMLSecurity\Utils\XML;
use SimpleSAML\XMLSecurity\Utils\XPath;
use SimpleSAML\XMLSecurity\XML\ds\Reference;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

use function array_pop;
use function base64_decode;
use function in_array;

/**
 * Helper trait for processing signed elements.
 *
 * @package simplesamlphp/xml-security
 */
trait SignedElementTrait
{
    use CanonicalizableElementTrait;

    /**
     * The signature of this element.
     *
     * @var \SimpleSAML\XMLSecurity\XML\ds\Signature|null $signature
     */
    protected ?Signature $signature = null;

    /**
     * The key that successfully verifies the signature in this object.
     *
     * @var \SimpleSAML\XMLSecurity\Key\AbstractKey|null
     */
    private ?AbstractKey $validatingKey = null;


    /**
     * Get the signature element of this object.
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\Signature
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }


    /**
     * Initialize a signed element from XML.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\Signature $signature The ds:Signature object
     */
    protected function setSignature(Signature $signature): void
    {
        $this->signature = $signature;
    }


    /**
     * Make sure the given Reference points to the original XML given.
     */
    private function validateReferenceUri(Reference $reference, DOMElement $xml): void
    {
        if (
            in_array(
                $this->signature->getSignedInfo()->getCanonicalizationMethod()->getAlgorithm(),
                [
                    C::C14N_INCLUSIVE_WITH_COMMENTS,
                    C::C14N_EXCLUSIVE_WITH_COMMENTS,
                ],
            )
            && !$reference->isXPointer()
        ) { // canonicalization with comments used, but reference wasn't an xpointer!
            throw new RuntimeException('Invalid reference for canonicalization algorithm.');
        }

        $id = $this->getId();
        $uri = $reference->getURI();

        if (empty($uri) || $uri === '#xpointer(/)') { // same-document reference
            Assert::true(
                $xml->isSameNode($xml->ownerDocument->documentElement),
                'Cannot use document reference when element is not the root of the document.',
                RuntimeException::class,
            );
        } else { // short-name or scheme-based xpointer
            Assert::notEmpty(
                $id,
                'Reference points to an element, but given element does not have an ID.',
                RuntimeException::class,
            );
            Assert::oneOf(
                $uri,
                [
                    '#' . $id,
                    '#xpointer(id(' . $id . '))',
                ],
                'Reference does not point to given element.',
                RuntimeException::class,
            );
        }
    }


    /**
     * @return \SimpleSAML\XMLSecurity\XML\SignedElementInterface
     */
    private function validateReference(): SignedElementInterface
    {
        /** @var \SimpleSAML\XMLSecurity\XML\ds\Signature $this->signature */
        $signedInfo = $this->signature->getSignedInfo();
        $references = $signedInfo->getReferences();
        Assert::count(
            $references,
            1,
            'Exactly one reference expected in signature.',
            TooManyElementsException::class,
        );
        $reference = array_pop($references);

        $xml = $this->getOriginalXML();
        $this->validateReferenceUri($reference, $xml);

        $xp = XPath::getXPath($xml->ownerDocument);
        $sigNode = XPath::xpQuery($xml, 'child::ds:Signature', $xp);
        Assert::minCount($sigNode, 1, NoSignatureFoundException::class);
        Assert::maxCount($sigNode, 1, 'More than one signature found in object.', TooManyElementsException::class);
        $xml->removeChild($sigNode[0]);

        $data = XML::processTransforms($reference->getTransforms(), $xml);
        $digest = Security::hash($reference->getDigestMethod()->getAlgorithm(), $data, false);

        if (Security::compareStrings($digest, base64_decode($reference->getDigestValue()->getRawContent())) !== true) {
            throw new SignatureVerificationFailedException('Failed to verify signature.');
        }

        $verifiedXml = DOMDocumentFactory::fromString($data);
        return static::fromXML($verifiedXml->documentElement);
    }


    /**
     * Verify this element against a public key.
     *
     * true is returned on success, false is returned if we don't have any
     * signature we can verify. An exception is thrown if the signature
     * validation fails.
     *
     * @param \SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmInterface|null $verifier The verifier to use to
     * verify the signature. If null, attempt to verify it with the KeyInfo information in the signature.
     *
     * @return \SimpleSAML\XMLSecurity\XML\SignedElementInterface The Signed element if it was verified.
     */
    private function verifyInternal(SignatureAlgorithmInterface $verifier): SignedElementInterface
    {
        /** @var \SimpleSAML\XMLSecurity\XML\ds\Signature $this->signature */
        $signedInfo = $this->signature->getSignedInfo();
        $c14nAlg = $signedInfo->getCanonicalizationMethod()->getAlgorithm();
        $c14nSignedInfo = $signedInfo->canonicalize($c14nAlg);
        $ref = $this->validateReference();

        if (
            $verifier->verify(
                $c14nSignedInfo, // the canonicalized ds:SignedInfo element (plaintext)
                base64_decode($this->signature->getSignatureValue()->getRawContent()), // the actual signature
            )
        ) {
            /*
             * validateReference() returns an object of the same class using this trait. This means the validatingKey
             * property is available, and we can set it on the newly created object because we are in the same class,
             * even thought the property itself is private.
             */
            /** @psalm-suppress NoInterfaceProperties */
            $ref->validatingKey = $verifier->getKey();
            return $ref;
        }
        throw new SignatureVerificationFailedException('Failed to verify signature.');
    }


    /**
     * Retrieve certificates that sign this element.
     *
     * @return \SimpleSAML\XMLSecurity\Key\AbstractKey|null The key that successfully verified this signature.
     */
    public function getVerifyingKey(): ?AbstractKey
    {
        return $this->validatingKey;
    }


    /**
     * Whether this object is signed or not.
     *
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->signature !== null;
    }


    /**
     * Verify the signature in this object.
     *
     * If no signature is present, false is returned. If a signature is present,
     * but cannot be verified, an exception will be thrown.
     *
     * @param \SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmInterface|null $verifier The verifier to use to
     * verify the signature. If null, attempt to verify it with the KeyInfo information in the signature.
     * @return \SimpleSAML\XMLSecurity\XML\SignedElementInterface The object processed again from its canonicalised
     * representation verified by the signature.
     * @throws \SimpleSAML\XMLSecurity\Exception\NoSignatureFoundException if the object is not signed.
     * @throws \SimpleSAML\XMLSecurity\Exception\InvalidArgumentException if no key is passed and there is no KeyInfo
     * in the signature.
     * @throws \SimpleSAML\XMLSecurity\Exception\RuntimeException if the signature fails to verify.
     */
    public function verify(SignatureAlgorithmInterface $verifier = null): SignedElementInterface
    {
        if (!$this->isSigned()) {
            throw new NoSignatureFoundException();
        }

        $keyInfo = $this->signature->getKeyInfo();
        $algId = $this->signature->getSignedInfo()->getSignatureMethod()->getAlgorithm();
        if ($verifier === null && $keyInfo === null) {
            throw new InvalidArgumentException('No key or KeyInfo available for signature verification.');
        }

        if ($verifier !== null) {
            // verify using given key
            // TODO: make this part of the condition, so that we support using this verifier to decrypt an encrypted key
            Assert::eq(
                $verifier->getAlgorithmId(),
                $algId,
                'Algorithm provided in key does not match algorithm used in signature.',
            );

            return $this->verifyInternal($verifier);
        }

        $factory = new SignatureAlgorithmFactory();
        foreach ($keyInfo->getInfo() as $info) {
            if (!$info instanceof X509Data) {
                continue;
            }

            foreach ($info->getData() as $data) {
                if (!$data instanceof X509Certificate) {
                    // not supported
                    continue;
                }

                // build a valid PEM for the certificate
                $cert = Key\X509Certificate::PEM_HEADER . "\n" .
                        $data->getRawContent() . "\n" .
                        Key\X509Certificate::PEM_FOOTER;

                $key = new Key\X509Certificate($cert);
                $verifier = $factory->getAlgorithm($algId, $key);

                try {
                    return $this->verifyInternal($verifier);
                } catch (RuntimeException $e) {
                    // failed to verify with this certificate, try with other, if any
                }
            }
        }
        throw new SignatureVerificationFailedException('Failed to verify signature.');
    }


    /**
     * @return string|null
     */
    abstract public function getId(): ?string;
}
