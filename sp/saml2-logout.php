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
 * Test page for SAML
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// @codingStandardsIgnoreStart
require_once(__DIR__ . '/../../../config.php');
// @codingStandardsIgnoreEnd
require('../setup.php');

// First setup the PATH_INFO because that's how SSP rolls.
$_SERVER['PATH_INFO'] = '/' . $saml2auth->spname;

/*
 * There are 4 methods of logging out:
 *
 * 1) Initiated from moodle logout, in which case we first logout of
 *    moodle and then log out of the middle SP and then optionally
 *    redirect to the IdP to do full Single Logout. This is the way
 *    a majority of users logout and is fully supported. Notice that in this
 *    case SAML session is not authenticated by the time we reach this point.
 *
 * 2) If doing SLO via IdP via the HTTP-Redirect binding
 *
 * 3) Same as 2 but via the front channel HTTP-Post binding. This should
 *    work but is untested. TODO.
 *
 * 4) Backchannel logout via the SOAP binding. TODO.
 *
 */
try {
    $session = \SimpleSAML\Session::getSessionFromRequest();
    // When logout is initiated from IdP (we land here from SingleLogoutService call),
    // session is still authenticated, so we can register the handler that will log
    // user out in Moodle.
    if (!is_null($session->getAuthState($saml2auth->spname))) {
        $session->registerLogoutHandler($saml2auth->spname, '\auth_saml2\api', 'logout_from_idp_front_channel');
    }  else {
        // check if binding message exists and is logout request
         try {
             $binding = \SAML2\Binding::getCurrentBinding();
         } catch (\Exception $e) {
             // TODO: look for a specific exception
             // This is dirty. Instead of checking the message of the exception, \SAML2\Binding::getCurrentBinding() should throw
             // an specific exception when the binding is unknown, and we should capture that here
             if ($e->getMessage() === 'Unable to find the current binding.') {
                 throw new \SimpleSAML\Error\Error('SLOSERVICEPARAMS', $e, 400);
             } else {
                 throw $e; // do not ignore other exceptions!
             }
         }
         $message = $binding->receive();
         if ($message instanceof \SAML2\LogoutRequest) {
            $nameId = $message->getNameId();
            $sessionIndexes = $message->getSessionIndexes();

            // Getting session from $nameId and $sessionIndexes           
            $authId = $saml2auth->spname;

            assert(is_string($authId));

            $store = \SimpleSAML\Store::getInstance();
            if ($store === false) {
                // We don't have a datastore
                // TODO throw error
            }
    
            // serialize and anonymize the NameID
            $strNameId = serialize($nameId);
            $strNameId = sha1($strNameId);

            // Normalize SessionIndexes
            foreach ($sessionIndexes as &$sessionIndex) {
                assert(is_string($sessionIndex));
                if (strlen($sessionIndex) > 50) {
                    $sessionIndex = sha1($sessionIndex);
                }
            }
  
            // Remove reference
            unset($sessionIndex);
    
            if ($store instanceof \SimpleSAML\Store\SQL) {
                // TODO : ssp_sessions stored in db option
                //$sessions = self::getSessionsSQL($store, $authId, $strNameId);
            } else {
                if (empty($sessionIndexes)) {
                    // We cannot fetch all sessions without a SQL store
                    return false;
                }
    
                foreach ($sessionIndexes as $sessionIndex) {
                    $sessionId = $store->get('saml.LogoutStore', $strNameId . ':' . $sessionIndex);
                    if ($sessionId === null) {
                        continue;
                    }
                    assert(is_string($sessionId));
                    $session = \SimpleSAML\Session::getSession($sessionId);
                    $session->registerLogoutHandler($authId, '\auth_saml2\api', 'logout_from_idp_back_channel');
                    $sp_sessionId = $sessionId;
                    continue; // only registering first session...
                }
            }
        }
    }

    require('../.extlib/simplesamlphp/modules/saml/www/sp/saml2-logout.php');
} catch (Exception $e) {
    // TODO SSPHP uses Exceptions for handling valid conditions, so a succesful
    // logout is an Exception. This is a workaround to just go back to the home
    // page but we should probably handle SimpleSAML_Error_Error similar to how
    // extlib/simplesamlphp/www/_include.php handles it.
    redirect(new moodle_url('/'));
}

