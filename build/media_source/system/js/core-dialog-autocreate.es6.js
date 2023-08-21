/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'core.dialog';

/**
 * Auto create a popup dynamically on click, eg:
 *
 * <button type="button" data-core-dialog='{"popupType": "iframe", "src": "content/url.html"}'>Click</button>
 * <button type="button" data-core-dialog='{"popupType": "inline", "popupContent": "#id-of-content-element"}'>Click</button>
 * <a href="content/url.html" data-core-dialog>Click</a>
 */
const delegateSelector = '[data-core-dialog]';
const configDataAttr = 'coreDialog';
const configCacheFlag = 'coreDialogCache';

document.addEventListener('click', (event) => {
  const triggerEl = event.target.closest(delegateSelector);
  if (!triggerEl) return;
  event.preventDefault();

  // Check for cached instance
  const cacheable = !!triggerEl.dataset[configCacheFlag];
  if (cacheable && triggerEl.JoomlaDialogInstance) {
    Joomla.Modal.setCurrent(triggerEl.JoomlaDialogInstance);
    triggerEl.JoomlaDialogInstance.show();
    return;
  }
  // Parse config
  const config = triggerEl.dataset[configDataAttr] ? JSON.parse(triggerEl.dataset[configDataAttr]) : {};

  // Check if the click is on anchor
  if (triggerEl.nodeName === 'A') {
    if (!config.popupType) {
      config.popupType = triggerEl.hash ? 'inline' : 'iframe';
    }
    if (!config.src && config.popupType === 'iframe') {
      config.src = triggerEl.href;
    } else if (!config.src && config.popupType === 'inline') {
      config.src = triggerEl.hash;
    }
  }

  // Template not allowed here
  delete config.popupTemplate;

  const popup = new JoomlaDialog(config);
  if (cacheable) {
    triggerEl.JoomlaDialogInstance = popup;
  }

  popup.addEventListener('joomla-dialog:close', () => {
    Joomla.Modal.setCurrent(null);
    if (!cacheable) {
      popup.destroy();
    }
  });

  Joomla.Modal.setCurrent(popup);
  popup.show();
});
