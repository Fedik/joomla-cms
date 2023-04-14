/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* global JoomlaDialog */

/**
 * Create a dialog instance
 *
 * @param {Object} config
 * @returns {JoomlaDialog}
 */
const createDialog = (config) => {
  const dialog = new JoomlaDialog(config);
  dialog.addEventListener('joomla-dialog:open', () => {
    Joomla.Modal.setCurrent(dialog);
  });
  dialog.addEventListener('joomla-dialog:close', () => {
    Joomla.Modal.setCurrent(null);
    dialog.destroy();
  });
  return dialog;
};

/**
 * Show Select dialog
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @param {Object} dialogConfig
 * @returns {Promise}
 */
const doSelect = (inputValue, inputTitle, dialogConfig) => {
  const dialog = createDialog(dialogConfig);
  dialog.show();

  return new Promise((resolve) => {
    resolve();
  });
};

/**
 * Show Create dialog
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @param {Object} dialogConfig
 * @returns {Promise}
 */
const doCreate = (inputValue, inputTitle, dialogConfig) => {
  const dialog = createDialog(dialogConfig);
  dialog.show();

  return new Promise((resolve) => {
    resolve();
  });
};

/**
 * Show Edit dialog
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @param {Object} dialogConfig
 * @returns {Promise}
 */
const doEdit = (inputValue, inputTitle, dialogConfig) => {
  const dialog = createDialog(dialogConfig);
  dialog.show();

  return new Promise((resolve) => {
    resolve();
  });
};

/**
 * Clear selected values
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @returns {Promise}
 */
const doClear = (inputValue, inputTitle) => {
  inputValue.value = '';
  if (inputTitle) {
    inputTitle.value = '';
  }

  return new Promise((resolve) => {
    resolve();
  });
};

/**
 * Update view, depending if value is selected or not
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLElement} container
 */
const updateView = (inputValue, container) => {
  const btnSel = container.querySelector('[data-dialog-field-action="select"]');
  const btnNew = container.querySelector('[data-dialog-field-action="create"]');
  const btnEdit = container.querySelector('[data-dialog-field-action="edit"]');
  const btnClr = container.querySelector('[data-dialog-field-action="clear"]');

  if (inputValue.value) {
    // eslint-disable-next-line no-unused-expressions
    btnSel && btnSel.setAttribute('hidden', '');
    // eslint-disable-next-line no-unused-expressions
    btnNew && btnNew.setAttribute('hidden', '');
    // eslint-disable-next-line no-unused-expressions
    btnEdit && btnEdit.removeAttribute('hidden');
    // eslint-disable-next-line no-unused-expressions
    btnClr && btnClr.removeAttribute('hidden');
  } else {
    // eslint-disable-next-line no-unused-expressions
    btnSel && btnSel.removeAttribute('hidden');
    // eslint-disable-next-line no-unused-expressions
    btnNew && btnNew.removeAttribute('hidden');
    // eslint-disable-next-line no-unused-expressions
    btnEdit && btnEdit.setAttribute('hidden', '');
    // eslint-disable-next-line no-unused-expressions
    btnClr && btnClr.setAttribute('hidden', '');
  }
};

const delegateSelector = '[data-dialog-field-action]';
const actionConfigKey = 'dialogFieldAction';
const dialogConfigKey = 'dialogField';

// Bind the buttons
document.addEventListener('click', (event) => {
  const button = event.target.closest(delegateSelector);
  if (!button) return;
  event.preventDefault();

  // Extract the data
  const action = button.dataset[actionConfigKey];
  const dialogConfig = button.dataset[dialogConfigKey] ? JSON.parse(button.dataset[dialogConfigKey]) : {};
  const container = button.closest('.js-content-dialog-field');
  const inputValue = container ? container.querySelector('.js-input-value') : null;
  const inputTitle = container ? container.querySelector('.js-input-title') : null;

  if (!container || !inputValue) {
    throw new Error('Incomplete markup of Content dialog field');
  }

  // Handle requested action
  let handle;
  switch (action) {
    case 'select':
      handle = doSelect(inputValue, inputTitle, dialogConfig);
      break;
    case 'create':
      handle = doCreate(inputValue, inputTitle, dialogConfig);
      break;
    case 'edit':
      handle = doEdit(inputValue, inputTitle, dialogConfig);
      break;
    case 'clear':
      handle = doClear(inputValue, inputTitle);
      break;
    default:
      throw new Error(`Unknown action ${action}`);
  }

  handle.then(() => {
    updateView(inputValue, container);
  });
});
