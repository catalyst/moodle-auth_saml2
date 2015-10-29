<?php

// This handles all common class loading and env setup.

require_once('../../config.php');
require_once('auth.php');
require_once('loader.php');

$auth = new auth_plugin_saml2();

SimpleSAML_Configuration::setConfigDir($CFG->dirroot . '/auth/saml2/config/');

