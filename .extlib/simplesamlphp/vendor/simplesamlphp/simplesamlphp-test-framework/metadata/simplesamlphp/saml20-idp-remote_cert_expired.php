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
    'certData' => 'MIICqTCCAhICAQMwDQYJKoZIhvcNAQELBQAwgZgxCzAJBgNVBAYTAlVTMQ8wDQYDVQQIDAZIYXdhaWkxETAPBgNVBAcMCEhvbm9sdWx1MRYwFAYDVQQKDA1TaW1wbGVTQU1McGhwMRMwEQYDVQQLDApEZXZlbG9wZXJzMRQwEgYDVQQDDAtleGFtcGxlLm9yZzEiMCAGCSqGSIb3DQEJARYTbm9yZXBseUBleGFtcGxlLm9yZzAeFw0xOTA3MjAwODAwNDVaFw0xOTA3MjEwODAwNDVaMIGgMQswCQYDVQQGEwJVUzEPMA0GA1UECAwGSGF3YWlpMREwDwYDVQQHDAhIb25vbHVsdTEWMBQGA1UECgwNU2ltcGxlU0FNTHBocDETMBEGA1UECwwKRGV2ZWxvcGVyczEcMBoGA1UEAwwTZXhwaXJlZC5leGFtcGxlLm9yZzEiMCAGCSqGSIb3DQEJARYTbm9yZXBseUBleGFtcGxlLm9yZzCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEA2r6LqKka+9YwWzt32YWAIE/L/HKlVVYJAHBkWWVvHP16EL44LmTSCPZkNg27ha/vuWU0Qd8kW+hn1PiiNxI8dtR9uLaRaBhsHXEvgJtG9A2IrWtgbhMgRrMeSh9bDurgPEnGkXEpEghZpwEQ50ToFAukFJnx7EVK2UvbKtwfkv8CAwEAATANBgkqhkiG9w0BAQsFAAOBgQBfSFYHEiaKYIR2uOxOMpQQBigziuPKK7J4+IDrG1uCXV/YkEV8txgcUDZXTvchhVvLskOu5vrMFtaAXftySv6prS+TQZlCKo3OQQkiNFj/IJFaNq/bPascwJsd0VpB37daUqd65oK33Q1JgNY3b+gKWWUhZoHysURch5bO7jh8rA==',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'contacts' => [
        0 => [
            'emailAddress' => 'idp@example.org',
            'contactType' => 'technical',
            'givenName' => 'Administrator',
        ],
    ],
];
