/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function(customElements) {
    "use strict";

    class JoomlaToolbarButton extends HTMLElement {

        // Attribute getters
        get task()              { return this.getAttribute('task'); }
        get execute()           { return this.getAttribute('execute'); }
        get listSelection()     { return this.hasAttribute('list-selection'); }
        get form()              { return this.getAttribute('form'); }
        get formValidation()    { return this.hasAttribute('form-validation'); }
        get confirmMessage()    { return this.getAttribute('confirm-message'); }

        constructor() {
            super();

            // We need a button to support button behavior, because we cannot currently extend HTMLButtonElement
            this.buttonElement = this.querySelector('button');
            this.disabled      = false;

            // If list selection are required, set button to disabled by default
            if (this.listSelection) {
                this.setDisabled(true);
            }

            this.addEventListener('click', e => this.executeTask());
        }

        connectedCallback() {
            // Check whether we have a form
            let formSelector = this.form || '#adminForm';
            this.formElement = document.querySelector(formSelector);

            if (this.listSelection) {
                if (!this.formElement) {
                    throw new Error('The form "' + formSelector + '" is required to perform the task, but the form not found on the page.');
                }

                // Watch on list selection
                this.formElement.boxchecked.addEventListener('change', (event) => {
                    // Check whether we have selected something
                    this.setDisabled(event.target.value == 0);
                });
            }
        }

        setDisabled(disabled) {
            // Make sure we have a boolean value
            this.disabled = !!disabled;

            if (this.buttonElement) {
                if (this.disabled) {
                    this.buttonElement.setAttribute('disabled', true);
                } else {
                    this.buttonElement.removeAttribute('disabled');
                }
            }
        }

        executeTask() {
            if (this.disabled) {
                return;
            }

            if (this.confirmMessage && !confirm(this.confirmMessage)) {
                return;
            }

            if (this.task) {
                Joomla.submitbutton(this.task, this.form, this.formValidation);
            } else if (this.execute) {
                let method = new Function(this.execute);
                method.call({});
            } else {
                throw new Error('Either "task" or "execute" attribute must be preset to perform an action.');
            }

        }

    }

    customElements.define('joomla-toolbar-button', JoomlaToolbarButton);

})(customElements);
