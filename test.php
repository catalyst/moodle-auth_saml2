<?php
 // Use Composers autoloading

require('../../config.php');

require('autoload.php');

// Implement the Container interface (out of scope for example)
// require 'container.php';
$container = new SAML2_Compat_Ssp_Container();
SAML2_Compat_ContainerSingleton::setContainer($container);

// Set up an AuthnRequest
$request = new SAML2_AuthnRequest();
$request->setId($container->generateId());
$request->setIssuer('https://sp.example.edu');
$request->setDestination('https://idp.example.edu');

// Send it off using the HTTP-Redirect binding
$binding = new SAML2_HTTPRedirect();
$binding->send($request);
