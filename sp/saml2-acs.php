<?php

require_once('../setup.php');

// First setup the PATH_INFO
$_SERVER['PATH_INFO'] = '/' . $saml2auth->spname;;

require('../extlib/xmlseclibs/xmlseclibs.php');
require('../extlib/simplesamlphp/modules/saml/www/sp/saml2-acs.php');

