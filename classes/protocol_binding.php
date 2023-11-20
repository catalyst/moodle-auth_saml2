<?php

namespace auth_saml2;

defined('MOODLE_INTERNAL') || die();

use coding_exception;

/**
 * Utility class for protocol bindings
 *
 * @package    auth_saml2
 * @copyright  Copyright (c) 2023 KS DIF (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class protocol_binding {
    public const HTTP_POST = 0;
    public const HTTP_ARTIFACT = 1;
    public const HOK_SSO = 2;
    public const HTTP_REDIRECT = 3;
    /**
     * Map the internal id of the protocol bindings to the actual binding string.
     * @param int $id
     * @return string
     * @throws coding_exception
     */
    public static function get_binding(int $id): string {
        switch ($id) {
            case self::HTTP_POST:
                return 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
            case self::HTTP_ARTIFACT:
                return 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
            case self::HOK_SSO:
                return 'urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser';
            case self::HTTP_REDIRECT:
                return 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
            default:
                throw new coding_exception('Invalid binding');
        }
    }
    public static function form_options(): array {
        return [
            self::HTTP_POST => 'HTTP Post',
            self::HTTP_ARTIFACT => 'HTTP Artifact',
            self::HOK_SSO => 'Holder-of-Key Web Browser SSO',
            self::HTTP_REDIRECT => 'HTTP Redirect',
        ];
    }
}
