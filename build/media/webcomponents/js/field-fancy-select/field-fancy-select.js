/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Fancy select field, which use Choices.js
 *
 * Example:
 * <joomla-field-fancy-select ...attributes>
 *   <select>...</select>
 * </joomla-field-fancy-select>
 *
 * Possible attributes:
 *
 * allow-custom          Whether allow User to dynamically add a new value.
 * new-item-prefix=""    Prefix for a dynamically added value.
 *
 * remote-search         Enable remote search.
 * url=""                Url for remote search.
 * term-key="term"       Variable key name for searched term, will be appended to Url.
 *
 * min-term-length="1"   The minimum length a search value should be before choices are searched.
 * placeholder=""        The value of the inputs placeholder.
 * search-placeholder="" The value of the search inputs placeholder.
 */
;(function(customElements){
  "use strict";

  class JoomlaFieldFancySelect extends HTMLElement {

    // Properties getters/setters
    get allowCustom()       { return this.hasAttribute('allow-custom'); }
    get remoteSearch()      { return this.hasAttribute('remote-search'); }
    get url()               { return this.getAttribute('url'); }
    get termKey()           { return this.getAttribute('term-key') || 'term'; }
    get minTermLength()     { return parseInt(this.getAttribute('min-term-length')) || 1; }
    get newItemPrefix()     { return this.getAttribute('new-item-prefix') || ''; }
    get placeholder()       { return this.getAttribute('placeholder'); }
    get searchPlaceholder() { return this.getAttribute('search-placeholder'); }
    get value()             { return this.choicesInstance.getValue(true); }
    set value($val)         { this.choicesInstance.setValueByChoice('' + $val); }

    connectedCallback() {
      // Make sure Choices are loaded
      if (window.Choices || document.readyState === 'complete') {
        this._doConnect();
      } else {
        const callback = () => {
          this._doConnect();
          window.removeEventListener('load', callback);
        };
        window.addEventListener('load', callback);
      }
    }

    _doConnect() {
      // The element was already initialised previously and perhaps was detached from DOM
      if (this.choicesInstance) {
        return;
      }

      if (!window.Choices) {
        throw new Error('JoomlaFieldFancySelect require Choices.js to work');
      }

      // Get a <select> element
      this.select = this.querySelector('select');

      if (!this.select) {
        throw new Error('JoomlaFieldFancySelect require <select> element to work');
      }

      // Init Choices
      this.choicesInstance = new Choices(this.select, {
        placeholderValue: this.placeholder,
        searchPlaceholderValue: this.searchPlaceholder,
        removeItemButton: true,
        searchFloor: this.minTermLength,
        searchResultLimit: 10,
        shouldSort: false,
        fuseOptions: {
          threshold: 0.3 // Strict search
        },
        noResultsText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
        noChoicesText: Joomla.Text._('JGLOBAL_SELECT_NO_RESULTS_MATCH', 'No results found'),
        itemSelectText: Joomla.Text._('JGLOBAL_SELECT_PRESS_TO_SELECT', 'Press to select'),

        // Redefine some classes
        classNames: {
          button: 'choices__button_joomla' // It is need because an original styling use unavailable Icon.svg file
        }
      });

      // Collect an existing values, to avoid duplications
      this.choicesCache = {};

      // Handle typing of custom term
      if (this.allowCustom) {
        this.addEventListener('keydown', (event) => {
          if (event.keyCode !== 13 || event.target !== this.choicesInstance.input) return;
          event.preventDefault();

          if (this.choicesInstance.highlightPosition
            || !event.target.value || this.choicesCache[event.target.value]) return;

          // Make sure nothing is highlighted
          const highlighted = this.choicesInstance.dropdown.querySelector('.' + this.choicesInstance.config.classNames.highlightedState);
          if (highlighted) return;

          this.choicesInstance.setChoices([{
            value: this.newItemPrefix + event.target.value,
            label: event.target.value,
            selected: true,
            customProperties: {
              value: event.target.value // Store real value, just in case
            }
          }], 'value', 'label', false);

          this.choicesCache[event.target.value] = event.target.value;

          event.target.value = null;
          this.choicesInstance.hideDropdown();

          return false;
        });
      }

      // Handle remote search
      if (this.remoteSearch && this.url) {
        // Cache existing
        this.choicesInstance.config.choices.forEach((choiceItem) => {
          this.choicesCache[choiceItem.value] = choiceItem.label;
        });

        const lookupDelay = 300;
        let   lookupTimeout = null;
        this.activeXHR = null;
        this.select.addEventListener('search', (event) => {
          clearTimeout(lookupTimeout);
          lookupTimeout = setTimeout(this.requestLookup.bind(this), lookupDelay);
        });
      }
    }

    disconnectedCallback() {
      if (this.activeXHR) {
        this.activeXHR.abort();
      }
    }

    requestLookup() {
      let url = this.url;
      url += (url.indexOf('?') === -1 ? '?' : '&');
      url += encodeURIComponent(this.termKey) + '=' +  encodeURIComponent(this.choicesInstance.input.value);

      // Stop previous request if any
      if (this.activeXHR){
        this.activeXHR.abort();
      }

      this.activeXHR = Joomla.request({
        url: url,
        onSuccess: (response, xhr) => {
          this.activeXHR = null;
          const items = response ? JSON.parse(response) : [];
          if (!items.length) return;

          // Remove duplications
          let item;
          for(let i = items.length - 1; i >= 0; i--) {
            item = items[i];
            item.value = '' + item.value; // Make sure the value is a string, choices.js expect a string.

            if (this.choicesCache[item.value]) {
              items.splice(i, 1);
            } else {
              this.choicesCache[item.value] = item.text;
            }
          }

          // Add new options to field, assume that each item is object, eg {value: "foo", text: "bar"}
          if (items.length) {
            this.choicesInstance.setChoices(items, 'value', 'text', false);
          }
        },
        onError: () => {
          this.activeXHR = null;
        }
      });
    }
  }

  customElements.define('joomla-field-fancy-select', JoomlaFieldFancySelect);

})(customElements);
