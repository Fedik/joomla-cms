/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const delegateSelector = '[data-content-select]';

// Bind the buttons
document.addEventListener('click', (event) => {
  const button = event.target.closest(delegateSelector);
  if (!button) return;
  event.preventDefault();

  // Extract the data
  const data = { ...button.dataset };
  delete data.contentSelect;

  // Set the message type and send it
  data.messageType = 'joomla:content-select';
  window.parent.postMessage(data);
});
