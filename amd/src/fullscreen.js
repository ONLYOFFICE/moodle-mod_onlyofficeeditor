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
 * Fullscreen mode for an ONLYOFFICE editor container.
 *
 * @module     mod_onlyofficeeditor/fullscreen
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import Templates from 'core/templates';

const CLASSES = {
    CONTAINER: 'onlyofficeeditor-fullscreen',
    BODY: 'onlyofficeeditor-fullscreen-open',
};

const SELECTORS = {
    EXIT: '[data-action="onlyofficeeditor-fullscreen-exit"]',
    TOAST_REGION: '.toast-wrapper',
};

/**
 * Renders a template into a single element.
 *
 * @param {string} name Template name.
 * @param {Object} context Template context.
 * @returns {Promise<HTMLElement>}
 */
const renderElement = async(name, context) => {
    const {html} = await Templates.renderForPromise(name, context);
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstElementChild;
};

/**
 * Makes the page content outside the container inert.
 *
 * @param {HTMLElement} container Element that remains interactive.
 * @returns {HTMLElement[]} Elements made inert.
 */
const makeOutsideInert = (container) => {
    const elements = [];
    for (let node = container; node.parentElement && node !== document.body; node = node.parentElement) {
        for (const sibling of node.parentElement.children) {
            if (sibling !== node && !sibling.inert && !sibling.matches(SELECTORS.TOAST_REGION)) {
                sibling.inert = true;
                elements.push(sibling);
            }
        }
    }
    return elements;
};

/**
 * Adds the fullscreen button before the container and the fullscreen navbar inside it.
 *
 * @param {HTMLElement} container Parent element of the editor placeholder replaced by the iframe.
 * @param {string} documentType Document type of the editor config: word, cell, slide or pdf.
 * @returns {{destroy: Function}} Handle that removes the fullscreen controls.
 */
export const init = (container, documentType) => {
    let destroyed = false;
    let enterButton = null;
    let navbar = null;
    let inertElements = [];

    const exit = () => {
        toggle(false);
        enterButton.focus();
    };

    const onKeydown = (event) => {
        // Ignores the Escape key of a dialog opened over the editor.
        if (event.key === 'Escape' && !event.target.closest('[role="dialog"]')) {
            exit();
        }
    };

    const toggle = (enabled) => {
        container.classList.toggle(CLASSES.CONTAINER, enabled);
        document.body.classList.toggle(CLASSES.BODY, enabled);

        inertElements.forEach((element) => {
            element.inert = false;
        });
        inertElements = enabled ? makeOutsideInert(container) : [];

        if (enabled) {
            document.addEventListener('keydown', onKeydown);
        } else {
            document.removeEventListener('keydown', onKeydown);
        }
    };

    if (container) {
        Promise.all([
            renderElement('mod_onlyofficeeditor/fullscreen_button', {}),
            renderElement('mod_onlyofficeeditor/fullscreen_navbar', {documenttype: documentType}),
        ]).then(([button, bar]) => {
            if (destroyed) {
                return;
            }
            enterButton = button;
            navbar = bar;
            const exitButton = navbar.querySelector(SELECTORS.EXIT);

            enterButton.addEventListener('click', () => {
                toggle(true);
                // Moves the focus to the editor.
                (container.querySelector(':scope > iframe') || exitButton).focus();
            });

            exitButton.addEventListener('click', exit);

            container.before(enterButton);
            container.prepend(navbar);
            return;
        }).catch(Notification.exception);
    }

    return {
        destroy: () => {
            destroyed = true;
            if (enterButton) {
                toggle(false);
                enterButton.remove();
                navbar.remove();
            }
        },
    };
};
