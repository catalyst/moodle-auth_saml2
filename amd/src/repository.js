
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
 * AJAX repository for auth_saml2 IdPs.
 *
 * @module auth_saml2/repository
 * @copyright University of Graz 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

// Still useful for simple toggles.
export const updateIdpField = (idpId, field, value) => fetchMany([{
    methodname: 'auth_saml2_manage_idp',
    args: {
        id: idpId,
        fields: [{field, value}],
    },
}])[0];

// New helper for the modal.
export const updateIdpFields = (idpId, fieldMap) => {
    const fields = Object.entries(fieldMap).map(([field, value]) => ({field, value}));

    return fetchMany([{
        methodname: 'auth_saml2_manage_idp',
        args: {
            id: idpId,
            fields,
        },
    }])[0];
};
