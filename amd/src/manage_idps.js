
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
 * SAML2 IdPs management.
 *
 * @module auth_saml2/manage_idps
 * @copyright University of Graz 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import {updateIdpField} from './repository';
import * as DynamicTable from 'core_table/dynamic';
import * as IdpsFilter from './manage_idps_filters';
import {getString} from 'core/str';
import {init as openIdPEditModal} from './manage_idp_modal';

/**
 * Handle toggling a checkbox for active/default.
 *
 * @param {HTMLButtonElement} button Change event from the checkbox.
 */
const handleToggle = async(button) => {
    const idpId = parseInt(button.dataset.idpId, 10);
    const field = button.dataset.field;
    const currentActive = button.dataset.active === '1';
    const newActive = !currentActive;
    const value = newActive ? 1 : 0;

    if (!idpId || !field) {
        Notification.addNotification({
            message: 'Missing idpId or field on checkbox',
            type: 'error',
        });
        return;
    }

    // Disable the button while request is pending.
    button.disabled = true;

    try {
        const response = await updateIdpField(idpId, field, value);

        if (!response || !response.success) {
            throw new Error(response?.message || 'Update failed');
        }
    } catch (error) {
        Notification.exception(error);
    } finally {
        await updateButtonState(button, newActive);
        // Re-enable the button.
        button.disabled = false;
    }
};

/**
 * Update a toggle button's visual and accessibility state.
 *
 * @param {HTMLButtonElement} button
 * @param {boolean} isActive
 */
const updateButtonState = async(button, isActive) => {
    let activeteLabel = await getString('activateidp', 'auth_saml2');
    let deactivateLabel = await getString('deactivateidp', 'auth_saml2');

    // Data attribute.
    button.dataset.active = isActive ? '1' : '0';

    // ARIA pressed.
    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');

    // ARIA label (uses Moodle strings if available).
    const newLabel = isActive ? activeteLabel : deactivateLabel;
    button.setAttribute('aria-label', newLabel);

    // Icon swap.
    const icon = button.querySelector('i, span.icon');
    if (icon) {
        icon.classList.remove('fa-eye', 'fa-eye-slash');

        if (isActive) {
            icon.classList.add('fa-eye');
        } else {
            icon.classList.add('fa-eye-slash');
        }
    }
};

/**
 * Initialise IdPs management JS on the dynamic table region.
 *
 * @param {string} tableUniqueId
 */
export const init = (tableUniqueId) => {
    // Get IdP table element from DOM.
    const tableRoot = DynamicTable.getTableFromId(tableUniqueId);
    if (!(tableRoot instanceof HTMLElement)) {
        return;
    }

    // Add Event Listeners for actions that update the table.
    // The parent element is used because the table itself might get replaced by the filters.
    tableRoot.parentElement.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        // We might click the <i> inside the <button>. Use closest() to find the button.
        const button = target.closest('button[type="button"][data-idp-id]');
        if (!button || !(button instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();

        if (button.classList.contains('auth_saml2-idp-active-toggle')) {
            handleToggle(button);
        }

        if (button.classList.contains('auth_saml2-idp-edit')) {
            openIdPEditModal(button);
        }
    });

    // Initialise CoreFilter-based filter UI.
    const filterRegionId = 'core-filter-' + tableUniqueId;
    IdpsFilter.init(filterRegionId);
};
