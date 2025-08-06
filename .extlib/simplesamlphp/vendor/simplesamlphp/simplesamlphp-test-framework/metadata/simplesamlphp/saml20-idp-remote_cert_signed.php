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
    'certData' => 'MIICqDCCAhECAQIwDQYJKoZIhvcNAQELBQAwgZgxCzAJBgNVBAYTAlVTMQ8wDQYDVQQIDAZIYXdhaWkxETAPBgNVBAcMCEhvbm9sdWx1MRYwFAYDVQQKDA1TaW1wbGVTQU1McGhwMRMwEQYDVQQLDApEZXZlbG9wZXJzMRQwEgYDVQQDDAtleGFtcGxlLm9yZzEiMCAGCSqGSIb3DQEJARYTbm9yZXBseUBleGFtcGxlLm9yZzAeFw0xOTA3MjAwNzU4MDFaFw0zOTA3MTUwNzU4MDFaMIGfMQswCQYDVQQGEwJVUzEPMA0GA1UECAwGSGF3YWlpMREwDwYDVQQHDAhIb25vbHVsdTEWMBQGA1UECgwNU2ltcGxlU0FNTHBocDETMBEGA1UECwwKRGV2ZWxvcGVyczEbMBkGA1UEAwwSc2lnbmVkLmV4YW1wbGUub3JnMSIwIAYJKoZIhvcNAQkBFhNub3JlcGx5QGV4YW1wbGUub3JnMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCcYmwkO1lHel3sFpQtVnCQInGac8MYVWiXKrxWKsWAqrcsRnjGvIMQU5oz2KNhensx7C2Baa3yOmhyfoGEIoMnntQO6gqYAVskuAKGJhUzpPP1qP4ZV/FjZZ224u9+25gOkZO3Hr5PVCNBPloc+K8ppjRoUbwxFos8i9xou5v6xQIDAQABMA0GCSqGSIb3DQEBCwUAA4GBAD5jvsrGp0rv33XwbfWwhTNSBzwa61qr1fs1OjTfN2DJf/3i46uywHMZOfkDGstxmoqS7DcpNhMblv4eQDjYBADI1a6O5atAbtdu3D9qN67Ucc20xwQdZ0fBO9prH6pl6dm4zeF0pboZRi1s1GbxgixCPT5UQWvDnL7YM/pW8ttA',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'contacts' => [
        0 => [
            'emailAddress' => 'idp@example.org',
            'contactType' => 'technical',
            'givenName' => 'Administrator',
        ],
    ],
];
