<?php

require_once('../setup.php');

// First setup the PATH_INFO
$_SERVER['PATH_INFO'] = '/' . $saml2auth->spname;;

require_once(dirname(dirname(__FILE__)) . "/xmlseclibs/xmlseclibs.php");

require('../simplesamlphp/modules/saml/www/sp/saml2-acs.php');

