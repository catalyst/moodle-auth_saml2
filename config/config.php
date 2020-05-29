<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SSP config which inherits from Moodle config
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 use auth_saml2\ssl_algorithms;

defined('MOODLE_INTERNAL') || die();

global $CFG, $saml2auth;

// Check for https login.
$wwwroot = $CFG->wwwroot;
if (!empty($CFG->loginhttps)) {
    $wwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
}

$metadatasources = [];
foreach ($saml2auth->metadataentities as $metadataurl => $idpentities) {
    $metadatasources[] = [
        'type' => 'xml',
        'file' => "$CFG->dataroot/saml2/" . md5($metadataurl) . ".idp.xml"
    ];
}

$config = array(
    'baseurlpath'       => $wwwroot . '/auth/saml2/sp/',
    'certdir'           => $saml2auth->get_saml2_directory() . '/',
    'debug'             => $saml2auth->config->debug ? true : false,
    'logging.level'     => $saml2auth->config->debug ? SimpleSAML\Logger::DEBUG : SimpleSAML\Logger::ERR,
    'logging.handler'   => $saml2auth->config->logtofile ? 'file' : 'errorlog',
    'loggingdir'        => $saml2auth->config->logdir,
    'logging.logfile'   => 'simplesamlphp.log',
    'showerrors'        => $CFG->debugdisplay ? true : false,
    'errorreporting'    => false,
    'debug.validatexml' => false,
    'secretsalt'        => $saml2auth->config->privatekeypass,
    'technicalcontact_name'  => $CFG->supportname,
    'technicalcontact_email' => $CFG->supportemail ? $CFG->supportemail : $CFG->noreplyaddress,
    // TODO \core_user::get_support_user().
    'timezone' => class_exists('core_date') ? core_date::get_server_timezone() : null,

    'session.duration'          => 60 * 60 * 8, // 8 hours. TODO same as moodle.
    'session.datastore.timeout' => 60 * 60 * 4,
    'session.state.timeout'     => 60 * 60,

    'session.authtoken.cookiename'  => 'MDL_SSP_AuthToken',
    'session.cookie.name'     => 'MDL_SSP_SessID',
    'session.cookie.path'     => $CFG->sessioncookiepath,
    'session.cookie.domain'   => null,
    'session.cookie.secure'   => !empty($CFG->cookiesecure),
    'session.cookie.lifetime' => 0,

    'session.phpsession.cookiename' => null,
    'session.phpsession.savepath'   => null,
    'session.phpsession.httponly'   => true,

    'enable.http_post' => false,

    'signature.algorithm' => !empty($saml2auth->config->signaturealgorithm)
        ? $saml2auth->config->signaturealgorithm
        : ssl_algorithms::get_default_saml_signature_algorithm(),

    'metadata.sign.enable'          => $saml2auth->config->spmetadatasign ? true : false,
    'metadata.sign.certificate'     => $saml2auth->certcrt,
    'metadata.sign.privatekey'      => $saml2auth->certpem,
    'metadata.sign.privatekey_pass' => $saml2auth->config->privatekeypass,
    'metadata.sources'              => $metadatasources,

    'store.type' => !empty($CFG->auth_saml2_store) ? $CFG->auth_saml2_store : '\\auth_saml2\\store',

    'proxy' => null, // TODO inherit from moodle conf see http://moodle.local/admin/settings.php?section=http for more.

    'authproc.sp' => auth_plugin_saml2::saml2_authproc_filters_hook(),

    // TODO setting for signature.algorithm (ADFS 3 requires http://www.w3.org/2001/04/xmldsig-more#rsa-sha256)
    // TODO setting for redirect.sign
    // TODO More options for post-processing of the UID - essentially we need a safer version of SSPHP's authproc.
    // A basic plugin system would be ideal as requirements here can vary wildly.
);
