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
 * Common class loaders etc.
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/_autoload.php');
require_once("$CFG->dirroot/auth/saml2/auth.php");

/**
 * Ensure that valid certificates exist.
 *
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @param stdObj  $saml2auth config object
 * @param array   $dn Certificate Distinguished name details
 * @param integer $numberofdays Certificate expirey period
 */
function create_certificates($saml2auth, $dn = false, $numberofdays = 3650) {
    global $CFG, $SITE;

    $opensslargs = array();
    if (array_key_exists('OPENSSL_CONF', $_SERVER)) {
        $opensslargs['config'] = $_SERVER['OPENSSL_CONF'];
    }

    if ($dn == false) {
        // These are somewhat arbitrary and aren't really seen except inside
        // the auto created certificate used to sign saml requests.
        $dn = array(
            'commonName' => 'moodle',
            'countryName' => 'AU',
            'localityName' => 'moodleville',
            'emailAddress' => $CFG->supportemail ? $CFG->supportemail : $CFG->noreplyaddress,
            // TODO \core_user::get_support_user().
            'organizationName' => $SITE->shortname,
            'stateOrProvinceName' => 'moodle',
            'organizationalUnitName' => 'moodle',
        );
    }

    certificate_openssl_error_strings(); // Ensure existing messages are dropped
    $privkeypass = get_site_identifier();
    $privkey = openssl_pkey_new($opensslargs);
    $csr     = openssl_csr_new($dn, $privkey, $opensslargs);
    $sscert  = openssl_csr_sign($csr, null, $privkey, $numberofdays, $opensslargs);
    openssl_x509_export($sscert, $publickey);
    openssl_pkey_export($privkey, $privatekey, $privkeypass, $opensslargs);
    openssl_pkey_export($privkey, $privatekey, $privkeypass);
    $errors = certificate_openssl_error_strings();

    // Write Private Key and Certificate files to disk.
    // If there was a generation error with either explode.
    if (empty($privatekey)) {
        return get_string('nullprivatecert', 'auth_saml2') . $errors;
    }
    if (empty($publickey)) {
        return get_string('nullpubliccert', 'auth_saml2') . $errors;
    }

    if ( !file_put_contents($saml2auth->certpem, $privatekey) ) {
        return get_string('nullprivatecert', 'auth_saml2');
    }
    if ( !file_put_contents($saml2auth->certcrt, $publickey) ) {
        return get_string('nullpubliccert', 'auth_saml2');
    }

}

/**
 * Collect and render a list of OpenSSL error messages.
 *
 * @return string
 */
function certificate_openssl_error_strings() {
    $errors = array();
    while ($error = openssl_error_string()) {
        $errors[] = $error;
    }

    return html_writer::alist($errors);
}

/**
 * A nicer version of print_r
 *
 * @param mixed $arr A variable to display
 * @return string html table
 */
function pretty_print($arr) {
    if (is_object($arr)) {
        $arr = (array) $arr;
    }
    $retstr = '<table class="generaltable">';
    $retstr .= '<tr><th class="header">Key</th><th class="header">Value</th></tr>';
    if (is_array($arr)) {
        foreach ($arr as $key => $val) {
            if (is_object($val)) {
                $val = (array) $val;
            }
            if (is_array($val)) {
                $retstr .= '<tr><td>' . $key . '</td><td>' . pretty_print($val) . '</td></tr>';
            } else {
                if (strpos($key, 'valid') !== false && ($val * 1) === $val) {
                    $val = userdate($val) . " ($val)";
                }
                $retstr .= '<tr><td>' . $key . '</td><td>' . ($val == '' ? '""' : $val) . '</td></tr>';
            }
        }
    }
    $retstr .= '</table>';
    return $retstr;
}
