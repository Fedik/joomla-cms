/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const eventCodeToKeysMap = {
  AltLeft: ['Alt', 'AltLeft'],
  AltRight: ['Alt', 'AltRight'],
  ControlLeft: ['Control', 'ControlLeft'],
  ControlRight: ['Control', 'ControlRight'],
  ShiftLeft: ['Shift', 'ShiftLeft'],
  ShiftRight: ['Shift', 'ShiftRight'],
};

/**
 * Checking whether the keys match the pressed key
 * @param {KeyboardEvent} event
 * @param {String[]} keys
 * @returns {boolean}
 */
const doesKeysMatch = (event, keys) => {
  const eventKeys = eventCodeToKeysMap[event.code];
  if (!eventKeys) {
    return false;
  }
  // Check if one of keys exist in eventKeys
  for (let i = 0, l = keys.length; i < l; i += 1) {
    if (eventKeys.includes(keys[i])) {
      return true;
    }
  }

  return false;
};

window.customElements.define('joomla-toolbar-button', class extends HTMLElement {
  // Attribute getters
  get task() { return this.getAttribute('task'); }

  get listSelection() { return this.hasAttribute('list-selection'); }

  get form() { return this.getAttribute('form'); }

  get formValidation() { return this.hasAttribute('form-validation'); }

  get confirmMessage() { return this.getAttribute('confirm-message'); }

  get hideOnKey() { return this.getAttribute('hide-on-key'); }

  get showOnKey() { return this.getAttribute('show-on-key'); }

  /**
   * Lifecycle
   */
  constructor() {
    super();

    this.keysShowOnKey = [];
    this.keysHideOnKey = [];

    this.onChange = this.onChange.bind(this);
    this.executeTask = this.executeTask.bind(this);
  }

  /**
   * Lifecycle
   */
  connectedCallback() {
    // We need a button to support button behavior,
    // because we cannot currently extend HTMLButtonElement
    this.buttonElement = this.querySelector('button, a');

    this.buttonElement.addEventListener('click', this.executeTask);

    // Check whether we have a form
    const formSelector = this.form || 'adminForm';
    this.formElement = document.getElementById(formSelector);

    this.disabled = false;
    // If list selection is required, set button to disabled by default
    if (this.listSelection) {
      this.setDisabled(true);
    }

    if (this.listSelection) {
      if (!this.formElement) {
        throw new Error(`The form "${formSelector}" is required to perform the task, but the form was not found on the page.`);
      }

      // Watch on list selection
      this.formElement.boxchecked.addEventListener('change', this.onChange);
    }

    // Toggle button visibility on key press
    if (this.showOnKey || this.hideOnKey) {
      this.keysShowOnKey = this.showOnKey ? this.showOnKey.split(',') : [];
      this.keysHideOnKey = this.hideOnKey ? this.hideOnKey.split(',') : [];
      this.visibilityTogglerListener = (event) => {
        const matchShow = this.keysShowOnKey.length ? doesKeysMatch(event, this.keysShowOnKey) : false;
        const matchHide = this.keysHideOnKey.length ? doesKeysMatch(event, this.keysHideOnKey) : false;
        if (matchShow) {
          if (event.type === 'keydown') {
            this.removeAttribute('hidden');
          } else {
            this.setAttribute('hidden', '');
          }
        }
        if (matchHide) {
          if (event.type === 'keydown') {
            this.setAttribute('hidden', '');
          } else {
            this.removeAttribute('hidden');
          }
        }
      };
      document.addEventListener('keydown', this.visibilityTogglerListener);
      document.addEventListener('keyup', this.visibilityTogglerListener);
    }
  }

  /**
   * Lifecycle
   */
  disconnectedCallback() {
    if (this.formElement.boxchecked) {
      this.formElement.boxchecked.removeEventListener('change', this.onChange);
    }

    this.buttonElement.removeEventListener('click', this.executeTask);
  }

  onChange({ target }) {
    // Check whether we have selected something
    this.setDisabled(target.value < 1);
  }

  setDisabled(disabled) {
    // Make sure we have a boolean value
    this.disabled = !!disabled;

    // Switch attribute for native element
    // An anchor does not support "disabled" attribute, so use class
    if (this.buttonElement) {
      if (this.disabled) {
        if (this.buttonElement.nodeName === 'BUTTON') {
          this.buttonElement.disabled = true;
        } else {
          this.buttonElement.classList.add('disabled');
        }
      } else if (this.buttonElement.nodeName === 'BUTTON') {
        this.buttonElement.disabled = false;
      } else {
        this.buttonElement.classList.remove('disabled');
      }
    }
  }

  executeTask() {
    if (this.disabled) {
      return false;
    }

    // eslint-disable-next-line no-restricted-globals
    if (this.confirmMessage && !confirm(this.confirmMessage)) {
      return false;
    }

    if (this.task) {
      Joomla.submitbutton(this.task, this.form, this.formValidation);
    }

    return true;
  }
});
