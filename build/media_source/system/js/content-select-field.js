/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* global JoomlaDialog */

/**
 * Show Select dialog
 *
 * @param {HTMLInputElement} inputValue
 * @param {HTMLInputElement} inputTitle
 * @param {Object} dialogConfig
 * @returns {Promise}
 */
const doSelect = (inputValue, inputTitle, dialogConfig) => {
  // Create and show the dialog
  const dialog = new JoomlaDialog(dialogConfig);
  dialog.classList.add('joomla-content-dialog-field');
  dialog.show();
  // Joomla.Modal.setCurrent(dialog);

  return new Promise((resolve) => {
    const msgListener = (event) => {
      // Avoid cross origins
      if (event.origin !== window.location.origin) return;
      // Check message type
      if (event.data.messageType === 'joomla:content-select') {
        inputValue.value = event.data.id || '';
        if (inputTitle) {
          inputTitle.value = event.data.title || inputValue.value;
        }
        dialog.close();
      } else if (event.data.messageType === 'joomla:cancel') {
        dialog.close();
      }
    };

    // Clear all when dialog is closed
    dialog.addEventListener('joomla-dialog:close', () => {
      window.removeEventListener('message', msgListener);
      // Joomla.Modal.setCurrent(null);
      dialog.destroy();
      resolve();
    });

    // Wait for message
    window.addEventListener('message', msgListener);
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
  const hasValue = !!inputValue.value;
  container.querySelectorAll('[data-show-when-value]').forEach((el) => {
    if (el.dataset.showWhenValue) {
      // eslint-disable-next-line no-unused-expressions
      hasValue ? el.removeAttribute('hidden') : el.setAttribute('hidden', '');
    } else {
      // eslint-disable-next-line no-unused-expressions
      hasValue ? el.setAttribute('hidden', '') : el.removeAttribute('hidden');
    }
  });
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
    case 'create':
      handle = doSelect(inputValue, inputTitle, dialogConfig);
      break;
    case 'edit': {
      // Update current value in the URL
      const url = dialogConfig.src.indexOf('http') === 0 ? new URL(dialogConfig.src) : new URL(dialogConfig.src, window.location.origin);
      url.searchParams.set('id', inputValue.value);
      dialogConfig.src = url.toString();

      handle = doSelect(inputValue, inputTitle, dialogConfig);
      break;
    }
    case 'clear':
      handle = doClear(inputValue, inputTitle);
      break;
    default:
      throw new Error(`Unknown action ${action} for Content dialog field`);
  }

  handle.then(() => {
    updateView(inputValue, container);

    // Perform checkin when needed
    if (dialogConfig.checkinUrl) {
      const url = dialogConfig.checkinUrl.indexOf('http') === 0
        ? new URL(dialogConfig.checkinUrl) : new URL(dialogConfig.checkinUrl, window.location.origin);
      // Add value to request
      url.searchParams.set('id', inputValue.value);
      url.searchParams.set('cid[]', inputValue.value);
      // Also add value to POST, because Controller may expect it from there
      const data = new FormData();
      data.append('id', inputValue.value);
      data.append('cid[]', inputValue.value);

      Joomla.request({
        url: url.toString(), method: 'POST', promise: true, data,
      });
    }
  });
});
