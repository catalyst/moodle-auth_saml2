<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 */

// require_once('../setup.php');

global $saml2auth;
$cfg = $saml2auth->config;
$metadata[$cfg->entityid] = array(
	'SingleSignOnService'   => $cfg->ssourl,
	'SingleLogoutService'   => $cfg->slourl,
	'certFingerprint'       => $cfg->certfingerprint,
	'saml2.relaxvalidation' => array('noattributestatement')
);

