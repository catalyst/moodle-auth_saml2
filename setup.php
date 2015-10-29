<?php

// This handles all common class loading and env setup.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('auth.php');
require_once('loader.php');

$saml2auth = new auth_plugin_saml2();

SimpleSAML_Configuration::setConfigDir($CFG->dirroot . '/auth/saml2/config');

// class MDL_SimpleSAML_Module extends SimpleSAML_Module {
//
//     /**
//      * Retrieve the base directory for a module.
//      *
//      * The returned path name will be an absolute path.
//      *
//      * @param string $module Name of the module
//      *
//      * @return string The base directory of a module.
//      */
//     public static function getModuleDir($module) {
//         return 'crap/'.$module;
//     }
//
// }

