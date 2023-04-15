/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const delegateSelector = '[data-content-select]';
const onLoadSelector = '[data-content-select-on-load]';

/**
 * A helper to Post a Message
 * @param {Object} data
 */
const send = (data) => {
  // Set the message type and send it
  data.messageType = 'joomla:content-select';
  window.parent.postMessage(data);
};

// Bind the buttons
document.addEventListener('click', (event) => {
  const button = event.target.closest(delegateSelector);
  if (!button) return;
  event.preventDefault();

  // Extract the data and send
  const data = { ...button.dataset };
  delete data.contentSelect;
  send(data);
});

// Check for "select on load"
window.addEventListener('load', () => {
  const dataElement = document.querySelector(onLoadSelector);
  if (dataElement) {
    // Extract the data and send
    const data = { ...dataElement.dataset };
    delete data.contentSelectOnLoad;
    send(data);
  }
});
