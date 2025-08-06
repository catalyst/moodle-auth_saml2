<?php

$metadata['__DYNAMIC:1__'] = [
    'host' => '__DEFAULT__',
    'privatekey' => 'server.pem',
    'certificate' => 'server.crt',
    // Some WS-Fed relying parties applications set the session lifetime to the assertion lifetime
    // 'assertion.lifetime' => 3600,

    'auth' => 'example-userpass',
    'authproc' => [
        // Convert LDAP names to WS-Fed Claims.
        100 => ['class' => 'core:AttributeMap', 'name2claim'],
    ],
];
