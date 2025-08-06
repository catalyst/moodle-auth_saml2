<?php

declare(strict_types=1);

$metadata['https://idp.example.org/saml2/idp/metadata.php'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'https://idp.example.org/saml2/idp/metadata.php',
    'SingleSignOnService' => [
        0 => [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://idp.example.org/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        0 => [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://idp.example.org/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDEjCCAnugAwIBAgIJANIdJROXilWcMA0GCSqGSIb3DQEBCwUAMIGgMQswCQYDVQQGEwJVUzELMAkGA1UECAwCSEkxETAPBgNVBAcMCEhvbm9sdWx1MRYwFAYDVQQKDA1TaW1wbGVTQU1McGhwMRQwEgYDVQQLDAtEZXZlbG9wbWVudDEfMB0GA1UEAwwWc2VsZnNpZ25lZC5leGFtcGxlLm9yZzEiMCAGCSqGSIb3DQEJARYTbm9yZXBseUBleGFtcGxlLm9yZzAgFw0xOTA3MTgxNjMwMTZaGA8yMTE5MDYyNDE2MzAxNlowgaAxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJISTERMA8GA1UEBwwISG9ub2x1bHUxFjAUBgNVBAoMDVNpbXBsZVNBTUxwaHAxFDASBgNVBAsMC0RldmVsb3BtZW50MR8wHQYDVQQDDBZzZWxmc2lnbmVkLmV4YW1wbGUub3JnMSIwIAYJKoZIhvcNAQkBFhNub3JlcGx5QGV4YW1wbGUub3JnMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCwjX17mpEFt9pdka/jnseuSNosBqTRH6Pw9sQkuTH19xrRMdlGigfgdOQqvwUN1LcOF+zvFNQnZ3yTqpRxjGGcTtExKPeRI5Avef6aFO6AiDCKt831b95pnZuRsC0XweojS1xkEyiplzFZ0UjGTEG06QYvPYXJwDrTqSZuTOZGAQIDAQABo1AwTjAdBgNVHQ4EFgQUdWqf6TRDUhnJNM7vZB60oZ6bEgIwHwYDVR0jBBgwFoAUdWqf6TRDUhnJNM7vZB60oZ6bEgIwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOBgQBS+7YLLJJiDJ7OIg4PQb1bK2JpSNAimT1ZuhcgeeApM81OTh9AAS5OchRcjYmf4u1nJmfXk5RnJUHpFGGzjXoTtCrdwTUFV+u0WEkM+bB1nfuQHaHqr1UC6H956keHpedQ0N/9+0/hMoqwERQiaQLfoH9tIHv83Lq3iTc8uuJ/XA==',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'contacts' => [
        0 => [
            'emailAddress' => 'idp@example.org',
            'contactType' => 'technical',
            'givenName' => 'Administrator',
        ],
    ],
];
