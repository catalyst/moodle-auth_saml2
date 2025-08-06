<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleSAML\Configuration;

/**
 * A test case that provides a certificate directory with public and private
 * keys.
 *
 * @package SimpleSAMLphp
 */
class SigningTestCase extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected $config;

    /** @var string $root_directory */
    protected $root_directory;

    /** @var string $cert_directory */
    protected $cert_directory = 'certificates/rsa-pem';

    /** @var string $ca_private_key_file */
    protected $ca_private_key_file = 'simplesamlphp.org-ca_nopasswd.key';

    /** @var string $ca_certificate_file */
    protected $ca_certificate_file = 'simplesamlphp.org-ca.crt';

    /** @var string $good_private_key_file */
    protected $good_private_key_file = 'signed.simplesamlphp.org_nopasswd.key';

    /** @var string $good_certificate_file */
    protected $good_certificate_file = 'signed.simplesamlphp.org.crt';

    // openssl genrsa -out example.org-ca.key 1024
    /** @var string $ca_private_key */
    protected $ca_private_key;

    // openssl req -key example.org-ca.key -new -x509 -days 36500 -out example.org-ca.crt
    /** @var string $ca_certificate */
    protected $ca_certificate;

    // openssl genrsa -out signed.example.org.key 1024
    /** @var string $good_private_key */
    protected $good_private_key;

    // openssl req -key signed.example.org.key -new -out signed.example.org.crt
    /** @var string $good_certificate */
    protected $good_certificate;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->root_directory = dirname(dirname(__FILE__));
        $base = $this->root_directory . DIRECTORY_SEPARATOR . $this->cert_directory;
        $this->ca_private_key = file_get_contents($base . DIRECTORY_SEPARATOR . $this->ca_private_key_file);
        $this->ca_certificate = file_get_contents($base . DIRECTORY_SEPARATOR . $this->ca_certificate_file);
        $this->good_private_key = file_get_contents($base . DIRECTORY_SEPARATOR . $this->good_private_key_file);
        $this->good_certificate = file_get_contents($base . DIRECTORY_SEPARATOR . $this->good_certificate_file);
    }


    /**
     * @return array
     */
    public function getCertDirContent(): array
    {
        return [
            $this->ca_private_key_file => $this->ca_private_key,
            $this->ca_certificate_file => $this->ca_certificate,
            $this->good_private_key_file => $this->good_private_key,
            $this->good_certificate_file => $this->good_certificate,
        ];
    }


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->config = Configuration::loadFromArray([
            'certdir' => $this->cert_directory,
        ], '[ARRAY]', 'simplesaml');
    }


    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->clearInstance($this->config, Configuration::class, []);
    }


    /**
     * @param \SimpleSAML\Configuration $service
     * @param class-string $className
     * @param mixed|null $value
     * @return void
     */
    protected function clearInstance(Configuration $service, string $className, $value = null): void
    {
        $reflectedClass = new ReflectionClass($className);
        $reflectedInstance = $reflectedClass->getProperty('instance');
        $reflectedInstance->setAccessible(true);
        $reflectedInstance->setValue($service, $value);
        $reflectedInstance->setAccessible(false);
    }
}
