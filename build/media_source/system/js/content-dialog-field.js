/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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

const doSelect = (inputValue, inputTitle, dialogConfig) => {
  const dialog = createDialog(dialogConfig);
  dialog.show();
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
  switch (action) {
    case 'select':
      doSelect(inputValue, inputTitle, dialogConfig);
      break;
    case 'create':
      console.log(action, button);
      break;
    case 'edit':
      console.log(action, button);
      break;
    case 'clear':
      console.log(action, button);
      break;
    default:
      throw new Error(`Unknown action ${action}`);
  }
});
