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

global $CFG, $saml2auth;

$config = array(

    'certdir' => $saml2auth->certdir,
    'loggingdir' => 'log/', // TODO
    'datadir' => 'data/', // TODO.
    'tempdir' => '/tmp/simplesaml',

    /*
     * If you enable this option, simpleSAMLphp will log all sent and received messages
     * to the log file.
     *
     * This option also enables logging of the messages that are encrypted and decrypted.
     *
     * Note: The messages are logged with the DEBUG log level, so you also need to set
     * the 'logging.level' option to LOG_DEBUG.
     */
    'debug' => false,

    /*
     * When showerrors is enabled, all error messages and stack traces will be output
     * to the browser.
     *
     * When errorreporting is enabled, a form will be presented for the user to report
     * the error to technicalcontact_email.
     */
    'showerrors' => true,
    'errorreporting' => true,

    'debug.validatexml' => false,

    'secretsalt' => get_site_identifier(), // TODO is this safe?

    /*
     * Some information about the technical persons running this installation.
     * The email address will be used as the recipient address for error reports, and
     * also as the technical contact in generated metadata.
     */
    'technicalcontact_name' => $CFG->supportname,
    'technicalcontact_email' => $CFG->supportemail,

    /*
     * The timezone of the server. This option should be set to the timezone you want
     * simpleSAMLphp to report the time in. The default is to guess the timezone based
     * on your system timezone.
     *
     * See this page for a list of valid timezones: http://php.net/manual/en/timezones.php
     */
    'timezone' => null,

    /*
     * Logging.
     *
     * define the minimum log level to log
     *		SimpleSAML_Logger::ERR		No statistics, only errors
     *		SimpleSAML_Logger::WARNING	No statistics, only warnings/errors
     *		SimpleSAML_Logger::NOTICE	Statistics and errors
     *		SimpleSAML_Logger::INFO		Verbose logs
     *		SimpleSAML_Logger::DEBUG	Full debug logs - not recommended for production
     *
     * Choose logging handler.
     *
     * Options: [syslog,file,errorlog]
     *
     */
    'logging.level' => SimpleSAML_Logger::NOTICE,
    'logging.handler' => 'errorlog', // TODO check working.


    /* (New) statistics output configuration.
     *
     * This is an array of outputs. Each output has at least a 'class' option, which
     * selects the output.
     */
    'statistics.out' => array(// Log statistics to the normal log.
        /*
        array(
            'class' => 'core:Log',
            'level' => 'notice',
        ),
        */
        // Log statistics to files in a directory. One file per day.
        /*
        array(
            'class' => 'core:File',
            'directory' => '/var/log/stats',
        ),
        */
    ),

    /*
     * Enable
     *
     * Which functionality in simpleSAMLphp do you want to enable. Normally you would enable only
     * one of the functionalities below, but in some cases you could run multiple functionalities.
     * In example when you are setting up a federation bridge.
     */
    'enable.saml20-idp' => false,
    'enable.shib13-idp' => false,
    'enable.adfs-idp' => false,
    'enable.wsfed-sp' => false,
    'enable.authmemcookie' => false,

    'session.duration' => 8 * (60 * 60), // 8 hours. TODO same as moodle.
    'session.datastore.timeout' => (4 * 60 * 60),
    'session.state.timeout' => (60 * 60),
    'session.cookie.name' => 'SimpleSAMLSessionID',
    'session.cookie.lifetime' => 0,
    'session.cookie.path' => '/',
    'session.cookie.domain' => null,
    'session.cookie.secure' => false, // TODO.

    'enable.http_post' => false,

    /*
     * Options to override the default settings for php sessions.
     */
    'session.phpsession.cookiename' => null,
    'session.phpsession.savepath' => null,
    'session.phpsession.httponly' => true,

    /*
     * Option to override the default settings for the auth token cookie
     */
    'session.authtoken.cookiename' => 'SimpleSAMLAuthToken',

    /*
     * Languages available, RTL languages, and what language is default
     */
    'language.available' => array(
        'en', 'no', 'nn', 'se', 'da', 'de', 'sv', 'fi', 'es', 'fr', 'it', 'nl', 'lb', 'cs',
        'sl', 'lt', 'hr', 'hu', 'pl', 'pt', 'pt-br', 'tr', 'ja', 'zh', 'zh-tw', 'ru', 'et',
        'he', 'id', 'sr', 'lv', 'ro', 'eu'
    ),
    'language.rtl' => array('ar', 'dv', 'fa', 'ur', 'he'),
    'language.default' => 'en',

    /*
     * Options to override the default settings for the language parameter
     */
    'language.parameter.name' => 'language',
    'language.parameter.setcookie' => true,

    /*
     * Options to override the default settings for the language cookie
     */
    'language.cookie.name' => 'language',
    'language.cookie.domain' => null,
    'language.cookie.path' => '/',
    'language.cookie.lifetime' => (60 * 60 * 24 * 900),

    /*
     * Default IdP for WS-Fed.
     */
    'default-wsfed-idp' => 'urn:federation:pingfederate:localhost',

    'authproc.sp' => array(
        90 => 'core:LanguageAdaptor',
    ),

    'metadatadir' => "$CFG->dirroot/auth/saml2/metadata",
    'metadata.sources' => array(
        array('type' => 'flatfile'),
    ),

    /*
     * Piggy back sessions inside the moodle DB
     */
    'store.type'           => 'sql',
    'store.sql.username'   => $CFG->dbuser,
    'store.sql.password'   => $CFG->dbpass,
    'store.sql.prefix'     => $CFG->prefix . 'authsaml_',
    'store.sql.persistent' => false,
    // TODO this should be broken for psql as not supported by SSP but works! why?
    'store.sql.dsn'        => (
        $CFG->dbtype == 'pgsql' ? "pgsql:host={$CFG->dbhost};dbname={$CFG->dbname}" :
        $CFG->dbtype == 'mysql' ? "mysql:host={$CFG->dbhost};dbname={$CFG->dbname}" :
        'unknowndbtype'
    ),

    'metadata.sign.enable' => false,

    /*
     * The default key & certificate which should be used to sign generated metadata. These
     * are files stored in the cert dir.
     * These values can be overridden by the options with the same names in the SP or
     * IdP metadata.
     *
     * If these aren't specified here or in the metadata for the SP or IdP, then
     * the 'certificate' and 'privatekey' option in the metadata will be used.
     * if those aren't set, signing of metadata will fail.
     */
    'metadata.sign.privatekey' => null,
    'metadata.sign.privatekey_pass' => null,
    'metadata.sign.certificate' => null,

    'proxy' => null, // TODO inherit from moodle conf see http://moodle.local/admin/settings.php?section=http for more.

);
