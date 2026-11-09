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

namespace auth_saml2\external;

use auth_saml2\metadata_cache;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use context_system;
use invalid_parameter_exception;

/**
 * External API for SAML2 IdP field updates.
 *
 * @package   auth_saml2
 * @copyright University of Graz 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_idp extends external_api {
    /**
     * Parameter structure for auth_saml2_update.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id'     => new external_value(PARAM_INT, 'IdP id'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'field' => new external_value(
                        PARAM_ALPHAEXT,
                        'Field to update: displayname|active|defaultidp|adminonly|whitelist'
                    ),
                    'value' => new external_value(
                        PARAM_RAW,
                        'New value for the field'
                    ),
                ]),
                'List of field/value pairs to update',
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Update one or more fields of an IdP.
     *
     * @param int $id
     * @param array $fields
     * @return array
     */
    public static function execute(int $id, array $fields) {
        global $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'id'     => $id,
                'fields' => $fields,
            ]
        );

        require_login();
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        $idp = $DB->get_record('auth_saml2_idps', ['id' => $params['id']], '*', MUST_EXIST);

        foreach ($params['fields'] as $fielditem) {
            $field = $fielditem['field'];
            $value = $fielditem['value'];

            switch ($field) {
                case 'displayname':
                    $idp->displayname = $value;
                    break;

                case 'activeidp':
                    $value = (int)!empty($value);
                    if ($idp->activeidp !== $value) {
                        metadata_cache::schedule_cache_task();
                    }
                    $idp->activeidp = $value;
                    break;

                case 'defaultidp':
                    $idp->defaultidp = (int)!empty($value);
                    break;

                case 'adminidp':
                    $idp->adminidp = (int)!empty($value);
                    break;

                case 'whitelist':
                    $idp->whitelist = trim((string)$value);
                    break;

                default:
                    throw new invalid_parameter_exception("The field '{$field}' is invalid for updating the IdP table");
            }
        }

        $result = $DB->update_record('auth_saml2_idps', $idp);

        return [
            'success' => $result,
        ];
    }

    /**
     * Define the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the field has successfully been updated.'),
        ]);
    }
}
