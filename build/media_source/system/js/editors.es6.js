/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* global JoomlaDialog */

if (!window.Joomla) {
  throw new Error('JoomlaEditors API require Joomla to be loaded.');
}

// === The code for keep backward compatibility ===
// Joomla.editors is deprecated use Joomla.Editor instead.
// @TODO: Remove this section in Joomla 6.

// Only define editors if not defined
Joomla.editors = Joomla.editors || {};

// An object to hold each editor instance on page, only define if not defined.
Joomla.editors.instances = new Proxy({}, {
  set(target, p, editor) {
    // eslint-disable-next-line no-use-before-define
    if (!(editor instanceof JoomlaEditorDecorator)) {
      // Add missed method in Legacy editor
      editor.getId = () => p;
      // eslint-disable-next-line no-console
      console.warn('Legacy editors is deprecated. Register the editor instance with Joomla.Editor.register().');
    }
    target[p] = editor;
    return true;
  },
});
// === End of code for keep backward compatibility ===

/**
 * A decorator for Editor instance.
 */
class JoomlaEditorDecorator {
  /**
   * Internal! The property should not be accessed directly.
   * The editor instance.
   * @type {Object}
   */
  // instance = null;

  /**
   * Internal! The property should not be accessed directly.
   * The editor type/name, eg: tinymce, codemirror, none etc.
   * @type {string}
   */
  // type = '';

  /**
   * Internal! The property should not be accessed directly.
   * HTML ID of the editor.
   * @type {string}
   */
  // id = '';

  /**
   * Class constructor.
   *
   * @param {Object} instance The editor instance
   * @param {string} type The editor type/name
   * @param {string} id The editor ID
   */
  constructor(instance, type, id) {
    if (!instance || !type || !id) {
      throw new Error('Missed values for class constructor');
    }

    this.instance = instance;
    this.type = type;
    this.id = id;
  }

  /**
   * Returns the editor instance object.
   *
   * @returns {Object}
   */
  getRawInstance() {
    return this.instance;
  }

  /**
   * Returns the editor type/name.
   *
   * @returns {string}
   */
  getType() {
    return this.type;
  }

  /**
   * Returns the editor id.
   *
   * @returns {string}
   */
  getId() {
    return this.id;
  }

  /**
   * Return the complete data from the editor.
   * Should be implemented by editor provider.
   *
   * @returns {string}
   */
  // eslint-disable-next-line class-methods-use-this
  getValue() {
    throw new Error('Not implemented');
  }

  /**
   * Replace the complete data of the editor
   * Should be implemented by editor provider.
   *
   * @param {string} value Value to set.
   *
   * @returns {JoomlaEditorDecorator}
   */
  // eslint-disable-next-line class-methods-use-this, no-unused-vars
  setValue(value) {
    throw new Error('Not implemented');
  }

  /**
   * Return the selected text from the editor.
   * Should be implemented by editor provider.
   *
   * @returns {string}
   */
  // eslint-disable-next-line class-methods-use-this
  getSelection() {
    throw new Error('Not implemented');
  }

  /**
   * Replace the selected text. If nothing selected, will insert the data at the cursor.
   * Should be implemented by editor provider.
   *
   * @param {string} value
   *
   * @returns {JoomlaEditorDecorator}
   */
  // eslint-disable-next-line class-methods-use-this, no-unused-vars
  replaceSelection(value) {
    throw new Error('Not implemented');
  }

  /**
   * Toggles the editor disabled mode. When the editor is active then everything should be usable.
   * When inactive the editor should be unusable AND disabled for form validation.
   * Should be implemented by editor provider.
   *
   * @param {boolean} enable True to enable, false or undefined to disable.
   *
   * @returns {JoomlaEditorDecorator}
   */
  // eslint-disable-next-line class-methods-use-this, no-unused-vars
  disable(enable) {
    throw new Error('Not implemented');
  }
}

/**
 * Editor API.
 */
const JoomlaEditor = {
  /**
   * Internal! The property should not be accessed directly.
   *
   * List of registered editors.
   */
  instances: {},

  /**
   * Internal! The property should not be accessed directly.
   *
   * An active editor instance.
   */
  active: null,

  /**
   * Register editor instance.
   *
   * @param {JoomlaEditorDecorator} editor The editor instance.
   *
   * @returns {JoomlaEditor}
   */
  register(editor) {
    if (!(editor instanceof JoomlaEditorDecorator)) {
      throw new Error('Unexpected editor instance');
    }

    this.instances[editor.getId()] = editor;

    // For backward compatibility
    Joomla.editors.instances[editor.getId()] = editor;

    return this;
  },

  /**
   * Unregister editor instance.
   *
   * @param {JoomlaEditorDecorator|string} editor The editor instance or ID.
   *
   * @returns {JoomlaEditor}
   */
  unregister(editor) {
    let id;
    if (editor instanceof JoomlaEditorDecorator) {
      id = editor.getId();
    } else if (typeof editor === 'string') {
      id = editor;
    } else {
      throw new Error('Unexpected editor instance or identifier');
    }

    if (this.active && this.active === this.instances[id]) {
      this.active = null;
    }

    delete this.instances[id];

    // For backward compatibility
    delete Joomla.editors.instances[id];

    return this;
  },

  /**
   * Return editor instance by ID.
   *
   * @param {String} id
   *
   * @returns {JoomlaEditorDecorator|boolean}
   */
  get(id) {
    return this.instances[id] || false;
  },

  /**
   * Set currently active editor, the editor that in focus.
   *
   * @param {JoomlaEditorDecorator|string} editor The editor instance or ID.
   *
   * @returns {JoomlaEditor}
   */
  setActive(editor) {
    if (editor instanceof JoomlaEditorDecorator) {
      this.active = editor;
    } else if (this.instances[editor]) {
      this.active = this.instances[editor];
    } else {
      throw new Error('The editor instance not found or it is incorrect');
    }

    return this;
  },

  /**
   * Return active editor, if there exist eny.
   *
   * @returns {JoomlaEditorDecorator}
   */
  getActive() {
    return this.active;
  },
};

/**
 * Editor Buttons API.
 */
const JoomlaEditorButton = {
  /**
   * Internal! The property should not be accessed directly.
   *
   * A collection of button actions.
   */
  actions: {},

  /**
   * Register new button action, or override existing.
   *
   * @param {String} name Action name
   * @param {Function} handler Callback that will be executed.
   *
   * @returns {JoomlaEditorButton}
   */
  registerAction(name, handler) {
    this.actions[name] = handler;
    return this;
  },

  /**
   * Get registered handler by action name.
   *
   * @param {String} name Action name
   *
   * @returns {Function|false}
   */
  getActionHandler(name) {
    return this.actions[name] || false;
  },

  /**
   * Execute action.
   *
   * @param {String} name Action name
   * @param {Object} options An options object
   * @param {HTMLElement} button An optional element, that triggers the action
   *
   * @returns {*}
   */
  runAction(name, options, button) {
    const handler = this.getActionHandler(name);
    let editor = Joomla.Editor.getActive();
    if (!handler) {
      throw new Error(`Handler for "${name}" action not found`);
    }
    // Try to find a legacy editor
    // @TODO: Remove this section in Joomla 6
    if (!editor && button) {
      const parent = button.closest('fieldset, div:not(.editor-xtd-buttons)');
      const textarea = parent ? parent.querySelector('textarea[id]') : false;
      editor = textarea && Joomla.editors.instances[textarea.id] ? Joomla.editors.instances[textarea.id] : false;
      if (editor) {
        // eslint-disable-next-line no-console
        console.warn('Legacy editors is deprecated. Set active editor instance with Joomla.Editor.setActive().');
      }
    }
    if (!editor) {
      throw new Error('An active editor are not available');
    }

    return handler(editor, options);
  },
};

// Register couple default actions for Editor Buttons
// Insert static content on cursor
JoomlaEditorButton.registerAction('insert', (editor, options) => {
  const content = options.content || '';
  editor.replaceSelection(content);
});
// Display modal dialog
JoomlaEditorButton.registerAction('modal', (editor, options) => {
  if (options.src) {
    // Replace editor parameter to actual editor ID
    const url = options.src.indexOf('http') === 0 ? new URL(options.src) : new URL(options.src, window.location.origin);
    url.searchParams.set('editor', editor.getId());
    if (url.searchParams.has('e_name')) {
      url.searchParams.set('e_name', editor.getId());
    }
    options.src = url.toString();
  }
  const popup = new JoomlaDialog(options);
  popup.addEventListener('joomla-dialog:close', () => {
    Joomla.Modal.setCurrent(null);
    popup.destroy();
  });
  Joomla.Modal.setCurrent(popup);
  popup.show();
});

// Listen to click on Editor button, and run action.
const btnDelegateSelector = '[data-joomla-editor-button-action]';
const btnActionDataAttr = 'joomlaEditorButtonAction';
const btnConfigDataAttr = 'joomlaEditorButtonOptions';

document.addEventListener('click', (event) => {
  const btn = event.target.closest(btnDelegateSelector);
  if (!btn) return;
  const action = btn.dataset[btnActionDataAttr];
  const options = btn.dataset[btnConfigDataAttr] ? JSON.parse(btn.dataset[btnConfigDataAttr]) : {};

  if (action) {
    Joomla.EditorButton.runAction(action, options, btn);
  }
});

Joomla.Editor = JoomlaEditor;
Joomla.EditorButton = JoomlaEditorButton;
window.JoomlaEditorDecorator = JoomlaEditorDecorator;
