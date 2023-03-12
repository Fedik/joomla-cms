/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Default template for the popup
const popupTemplate = `<div class="joomla-popup-container">
  <header class="joomla-popup-header"></header>
  <section class="joomla-popup-body"></section>
  <footer class="joomla-popup-footer"></footer>
</div>`;

/**
 * JoomlaPopup class implementation for <joomla-popup> element
 */
class JoomlaPopup extends HTMLElement {
  /**
   * The popup type, supported: inline, iframe, image, ajax.
   * @type {string}
   */
  popupType = 'inline';

  /**
   * An optional text for header.
   * @type {string}
   */
  textHeader = '';

  /**
   * An optional text for close button. Applied when no Buttons provided.
   * @type {string}
   */
  textClose = 'Close';

  /**
   * Content string (html) for inline type popup.
   * @type {string}
   */
  popupContent = '';

  /**
   * Source path for iframe, image, ajax.
   * @type {string}
   */
  src = '';

  /**
   * An optional list of buttons, to be rendered in footer, or bottom of the popup body.
   * Example:
   *   [{label: 'Yes', onClick: () => popup.close()},
   *   {label: 'No', onClick: () => popup.close(), className: 'btn btn-danger'}]
   * @type {[]}
   */
  popupButtons = [];

  /**
   * An optional limit for the popup width, any valid CSS value.
   * @type {string}
   */
  width = '';

  /**
   * An optional height for the popup, any valid CSS value.
   * @type {string}
   */
  height = '';

  /**
   * A template for the popup
   * @type {string|HTMLTemplateElement}
   */
  popupTemplate = popupTemplate;

  connectedCallback() {
    this.renderLayout();
  }

  /**
   * Render a main layout, based on given template.
   * @returns {JoomlaPopup}
   */
  renderLayout() {
    if (this.dialog) return this;

    // Check for existing layout
    if (this.firstElementChild && this.firstElementChild.nodeName === 'DIALOG') {
      this.dialog = this.firstElementChild;
      this.popupTmplB = this.querySelector('.joomla-popup-body') || this.dialog;
      this.popupContentElement = this.popupTmplB;
      return this;
    }

    // Render a template
    let templateContent;
    if (this.popupTemplate.tagName && this.popupTemplate.tagName === 'TEMPLATE') {
      templateContent = this.popupTemplate.content.cloneNode(true);
    } else {
      const template = document.createElement('template');
      template.innerHTML = this.popupTemplate;
      templateContent = template.content;
    }

    this.dialog = document.createElement('dialog');
    this.dialog.appendChild(templateContent);
    this.appendChild(this.dialog);

    // Get template parts
    this.popupTmplH = this.dialog.querySelector('.joomla-popup-header');
    this.popupTmplB = this.dialog.querySelector('.joomla-popup-body');
    this.popupTmplF = this.dialog.querySelector('.joomla-popup-footer');
    this.popupContentElement = null;

    if (!this.popupTmplB) {
      throw new Error('The popup body not found in the template.');
    }

    // Set the header
    if (this.popupTmplH && this.textHeader) {
      const h = document.createElement('h3');
      h.insertAdjacentHTML('afterbegin', this.textHeader);
      this.popupTmplH.insertAdjacentElement('afterbegin', h);
    }

    // Set the body
    this.renderBodyContent();
    this.setAttribute('type', this.popupType);

    // Create buttons if any
    if (this.popupButtons.length) {
      const buttonsHolder = document.createElement('div');
      buttonsHolder.classList.add('buttons-holder');
      (this.popupTmplF || this.popupTmplB).appendChild(buttonsHolder);

      this.popupButtons.forEach((btnData) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = btnData.label;

        if (btnData.className) {
          btn.classList.add(...btnData.className.split(' '));
        } else {
          btn.classList.add('button', 'button-primary', 'btn', 'btn-primary');
        }

        if (btnData.onClick) {
          btn.addEventListener('click', btnData.onClick);
        }

        buttonsHolder.appendChild(btn);
      });
    } else {
      // Add at least one button to close the popup
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.ariaLabel = this.textClose;
      btn.classList.add('button-close', 'btn-close');
      btn.addEventListener('click', () => this.close());

      if (this.popupTmplH) {
        this.popupTmplH.insertAdjacentElement('beforeend', btn);
      } else {
        this.popupTmplB.insertAdjacentHTML('afterbegin', btn);
      }
    }

    // Adjust the sizes if requested
    if (this.width) {
      this.dialog.style.width = '100%';
      this.dialog.style.maxWidth = this.width;
    }

    if (this.height) {
      this.dialog.style.height = this.height;
    }

    return this;
  }

  /**
   * Render the body content, based on popupType.
   * @returns {JoomlaPopup}
   */
  renderBodyContent() {
    if (!this.popupTmplB || this.popupContentElement) return this;

    // Callback for loaded content event listener
    const onLoad = () => {
      this.classList.add('loaded');
      this.classList.remove('loading');
      this.popupContentElement.removeEventListener('load', onLoad);
    }

    this.classList.add('loading');

    switch (this.popupType) {
      // Create an Inline content
      case 'inline':
        this.popupTmplB.insertAdjacentHTML('afterbegin', this.popupContent);
        this.popupContentElement = this.popupTmplB;
        onLoad();
        break;

      // Create an IFrame content
      case 'iframe':
        const frame = document.createElement('iframe');
        frame.addEventListener('load', onLoad);
        frame.src = this.src;
        frame.style.width = '100%';
        frame.style.height = '100%';
        this.popupContentElement = frame;
        this.popupTmplB.appendChild(frame);
        break;

      // Create an Image content
      case 'image':
        const img = document.createElement('img');
        img.addEventListener('load', onLoad);
        img.src = this.src;
        this.popupContentElement = img;
        this.popupTmplB.appendChild(img);
        break;

      // Create an AJAX content
      case 'ajax':
        fetch(this.src)
          .then((response) => {
            if (response.status !== 200) {
              throw new Error(response.statusText);
            }
            return response.text();
          }).then((text) => {
          this.popupTmplB.insertAdjacentHTML('afterbegin', text);
          this.popupContentElement = this.popupTmplB;
          onLoad();
        }).catch((error) => {
          throw error;
        });
        break;
      default:
        throw new Error('Unknown popup type requested');
    }

    return this;
  }

  /**
   * Return the popup body element.
   * @returns {HTMLElement}
   */
  getBody() {
    this.renderLayout();

    return this.popupTmplB;
  }

  /**
   * Return the popup content element, or body for inline and ajax types.
   * @returns {HTMLElement}
   */
  getBodyContent() {
    this.renderLayout();

    return this.popupContentElement || this.popupTmplB;
  }

  /**
   * Open the popup as modal window.
   * Will append the element to Document body if not appended before.
   *
   * @returns {JoomlaPopup}
   */
  show(){
    if (!this.parentElement) {
      document.body.appendChild(this);
    }

    this.dialog.showModal();
    return this;
  }

  /**
   * Closes the popup
   *
   * @returns {JoomlaPopup}
   */
  close() {
    if (!this.dialog) {
      throw new Error('Calling close for non opened dialog is discouraged.');
    }

    this.dialog.close();
    return this;
  }

  /**
   * Destroys the popup.
   */
  destroy() {
    if (this.dialog) {
      this.dialog.close();
    }
    this.removeChild(this.dialog);
    this.parentElement.removeChild(this);
    this.dialog = null;
    this.popupTmplH = null;
    this.popupTmplB = null;
    this.popupTmplF = null;
    this.popupContentElement = null;
  }

  /**
   * Helper method to show an Alert.
   * @param {String} message
   * @returns {JoomlaPopup}
   */
  static alert(message){
    const popup = new this;
    popup.popupType = 'inline';
    popup.popupContent = message;
    popup.popupButtons = [{label: 'Okay', onClick: () => popup.close()}]
    popup.classList.add('joomla-popup-alert');
    popup.show();

    return popup;
  }

  /**
   * Helper method to show a Confirmation popup.
   *
   * @param {String}   message
   * @param {Callback} onAccept
   * @param {Callback} onReject
   * @returns {JoomlaPopup}
   */
  static confirm(message, onAccept, onReject){
    const popup = new this;
    popup.popupType = 'inline';
    popup.popupContent = message;
    popup.popupButtons = [
      {label: 'Yes', onClick: () => {
          popup.close();
          onAccept && onAccept();
        }},
      {label: 'No', onClick: () => {
          popup.close();
          onReject && onReject();
        }, className: 'button btn btn-outline-primary ms-2'},
    ];
    popup.classList.add('joomla-popup-confirm');
    popup.show();

    return popup;
  }
}

window.JoomlaPopup = JoomlaPopup;
customElements.define('joomla-popup', JoomlaPopup);

export { JoomlaPopup };


// ================= testing ======================= //
const popup = new JoomlaPopup;
popup.textHeader = 'The header';
popup.popupContent = '<strong>blabla very strong text</strong>';

// popup.popupType = 'iframe';
// popup.src = 'index.php?option=com_content&view=articles&tmpl=component&layout=modal';

// popup.popupType = 'image';
// popup.src = '../images/headers/walden-pond.jpg';

// popup.popupType = 'ajax';
// popup.src = 'index.php?option=com_content&view=articles&tmpl=component&layout=modal';

popup.popupButtons = [
  {label: 'Yes', onClick: () => popup.close()},
  {label: 'No', onClick: () => popup.close(), className: 'btn btn-outline-danger ms-2'}
]
// popup.width = '80vw';
// popup.height = '80vh';

console.log([popup]);
//console.log(JoomlaPopup.alert('message'))
//console.log(JoomlaPopup.confirm('message?', () => {console.log(this)}))

popup.show();
