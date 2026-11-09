// This file is part of Moodle - http://moodle.org.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

/**
 * Manage the IdP edit modal.
 *
 * The modal is deliberately rendered client-side because this form edits
 * an individual row of the IdP flexible table.
 *
 * @module     auth_saml2/manage_idp_modal
 * @copyright  2026 University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import Templates from 'core/templates';
import {getString} from 'core/str';

import {updateIdpFields} from './repository';

/**
 * Read the IdP state from the edit button.
 *
 * The table already has this data available, so there is deliberately no
 * additional request just to populate the edit form.
 *
 * @param {HTMLButtonElement} button
 * @returns {Object}
 */
const getIdpData = (button) => ({
    idpid: Number.parseInt(button.dataset.idpId, 10),
    entityid: button.dataset.idpEntityid || '',
    defaultname: button.dataset.idpDefaultname || '',
    displayname: button.dataset.idpDisplayname || '',
    whitelist: button.dataset.idpWhitelist || '',
    defaultidp: button.dataset.idpDefaultidp === '1',
    adminidp: button.dataset.idpAdminidp === '1',
});

/**
 * Get all strings required by the edit form.
 *
 * Keeping localisation here means the Mustache template remains a pure
 * presentation template.
 *
 * @param {string} entityid
 *
 * @returns {Promise<Object>}
 */
const getFormStrings = async(entityid) => {
    const [
        source,
        displayName,
        status,
        defaultIdp,
        admin,
        whitelist,
        settings,
        adminHelp,
        whitelistHelp,
        save,
        cancel,
    ] = await Promise.all([
        getString('source', 'auth_saml2', entityid),
        getString('multiidp:label:displayname', 'auth_saml2'),
        getString('status', 'auth_saml2'),
        getString('multiidp:label:defaultidp', 'auth_saml2'),
        getString('multiidp:label:admin', 'auth_saml2'),
        getString('multiidp:label:whitelist', 'auth_saml2'),
        getString('settings'),
        getString('multiidp:label:admin_help', 'auth_saml2'),
        getString('multiidp:label:whitelist_help', 'auth_saml2'),
        getString('save'),
        getString('cancel'),
    ]);

    return {
        sourceLabel: source,
        displayNameLabel: displayName,
        statusLabel: status,
        defaultIdpLabel: defaultIdp,
        adminLabel: admin,
        whitelistLabel: whitelist,
        settingsLabel: settings,
        adminHelp,
        whitelistHelp,
        saveLabel: save,
        cancelLabel: cancel,
    };
};

/**
 * Build the Mustache context for the edit form.
 *
 * @param {Object} idp
 * @param {Object} strings
 * @returns {Object}
 */
const getTemplateContext = (idp, strings) => ({
    ...idp,
    ...strings,


    /*
     * Mustache handles boolean sections, which means the template doesn't
     * need to know anything about the underlying "1"/"0" representation.
     */
    defaultIdpChecked: idp.defaultidp,
    adminChecked: idp.adminidp,

    /*
     * Help text is deliberately provided inline. This retains the information
     * supplied by Moodle's addHelpButton() while making it available directly
     * to assistive technology.
     */
    hasAdminHelp: Boolean(strings.adminHelp),
    hasWhitelistHelp: Boolean(strings.whitelistHelp),
});

/**
 * Read the editable fields from the form.
 *
 * @param {HTMLFormElement} form
 * @returns {Object}
 */
const getFieldMap = (form) => {
    const formData = new FormData(form);

    return {
        displayname: formData.get('displayname') || '',
        defaultidp: formData.has('defaultidp') ? 1 : 0,
        adminidp: formData.has('adminidp') ? 1 : 0,
        whitelist: formData.get('whitelist') || '',
    };
};

/**
 * Update the table button's cached data after a successful save.
 *
 * This keeps the next opening of the modal in sync without requiring
 * another request or a full table reload.
 *
 * @param {HTMLButtonElement} button
 * @param {Object} fieldMap
 */
const updateButtonData = (button, fieldMap) => {
    button.dataset.idpDisplayname = fieldMap.displayname;
    button.dataset.idpDefaultidp = String(fieldMap.defaultidp);
    button.dataset.idpAdminidp = String(fieldMap.adminidp);
    button.dataset.idpWhitelist = fieldMap.whitelist;
};

/**
 * Open the edit modal for an IdP.
 *
 * @param {HTMLButtonElement} button
 * @returns {Promise<void>}
 */
export const init = async(button) => {
    try {
        const idp = getIdpData(button);
        const strings = await getFormStrings(idp.entityid);
        const context = getTemplateContext(idp, strings);

        const body = await Templates.render(
            'auth_saml2/idp_edit_form',
            context
        );

        const title = await getString('editidp', 'auth_saml2');

        const modal = await ModalSaveCancel.create({
            title: title + ': ' + idp.defaultname,
            body,
            show: true,
            large: true,
        });

        const root = modal.getRoot();
        const form = root[0].querySelector('.auth_saml2-idp-edit-form');

        root.on(ModalEvents.save, async(event) => {
            event.preventDefault();

            /*
             * Let native HTML validation handle required fields and other
             * constraints before sending anything to Moodle.
             */
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            try {
                const fieldMap = getFieldMap(form);

                await updateIdpFields(idp.idpid, fieldMap);

                /*
                 * Keep the row's cached data current. The next time the user
                 * opens this modal it therefore reflects the saved state.
                 */
                updateButtonData(button, fieldMap);

                /*
                 * TODO:
                 *
                 * Update the visible flexible-table row here if necessary.
                 * This is preferable to reloading the complete page.
                 */

                modal.destroy();
            } catch (error) {
                Notification.exception(error);
            }
        });
    } catch (error) {
        Notification.exception(error);
    }
};
