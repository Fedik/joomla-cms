/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const popupTemplate = `<div class="joomla-popup-container">
  <header class="joomla-popup-header"></header>
  <section class="joomla-popup-body"></section>
  <footer class="joomla-popup-footer"></footer>
</div>`;

class JoomlaPopup extends HTMLElement {
  popupType = 'inline';
  popupHeader = '';
  popupContent = '';
  src = '';
  popupTemplate = popupTemplate;
  popupButtons = [];
  width = '';
  height = '';
  textClose = 'Close';

  connectedCallback() {
    this.renderLayout();
  }

  renderLayout() {
    if (this.dialog) return this;

    // Check for existing layout
    if (this.firstElementChild && this.firstElementChild.nodeName === 'DIALOG') {
      this.dialog = this.firstElementChild;
      this.popupTmplB = this.querySelector('.joomla-popup-body');
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
    this.popupTmplH = this.querySelector('.joomla-popup-header');
    this.popupTmplB = this.querySelector('.joomla-popup-body');
    this.popupTmplF = this.querySelector('.joomla-popup-footer');
    this.popupContentElement = null;

    if (!this.popupTmplB) {
      throw new Error('The popup body not found in the template.');
    }

    // Set the header
    if (this.popupTmplH && this.popupHeader) {
      const h = document.createElement('h3');
      h.insertAdjacentHTML('afterbegin', this.popupHeader);
      this.popupTmplH.insertAdjacentElement('afterbegin', h);
    }

    // Set the body
    switch (this.popupType) {
      case 'inline':
        this.popupTmplB.insertAdjacentHTML('afterbegin', this.popupContent);
        this.popupContentElement = this.popupTmplB;
        break;
      case 'iframe':
        const frame = document.createElement('iframe');
        const onLoad = () => {
          this.classList.add('loaded');
          this.classList.remove('loading');
          frame.removeEventListener('load', onLoad);
        }
        frame.addEventListener('load', onLoad);
        this.classList.add('loading');
        frame.src = this.src;
        frame.style.width = '100%';
        frame.style.height = '100%';
        this.popupContentElement = frame;
        this.popupTmplB.appendChild(frame);
        break;
      case 'image':
      case 'ajax':
      default:
        throw new Error('Unknown popup type requested');
    }

    this.setAttribute('type', this.popupType);

    // Create buttons if any
    if (this.popupButtons.length) {
      const buttonsLocation = this.popupTmplF || this.popupTmplB;

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

        buttonsLocation.appendChild(btn);
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

    console.log(this)
    return this;
  }

  getBody() {
    this.renderLayout();

    return this.popupTmplB;
  }

  getBodyContent() {
    this.renderLayout();

    return this.popupContentElement || this.popupTmplB;
  }

  show(){
    if (!this.parentElement) {
      document.body.appendChild(this);
    }

    this.dialog.showModal();
  }

  close() {
    if (!this.dialog) {
      throw new Error('Calling close for non opened dialog is discouraged.');
    }

    this.dialog.close();
  }

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

  static alert(message){
    const popup = new this;
    popup.popupType = 'inline';
    popup.popupContent = message;
    popup.popupButtons = [{label: 'Okay', onClick: () => popup.close()}]
    popup.classList.add('joomla-popup-alert');
    popup.show();

    return popup;
  }

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
popup.popupType = 'iframe';
popup.popupHeader = 'The header';
popup.popupContent = '<strong>blabla very strong text</strong>';
popup.src = 'index.php?option=com_content&view=articles&tmpl=component&layout=modal';
popup.popupButtons1 = [
  {label: 'Yes', onClick: () => popup.close()},
  {label: 'No', onClick: () => popup.close(), className: 'btn btn-outline-danger ms-2'}
]
popup.width = '80vw';
popup.height = '80vh';

console.log([popup]);
//console.log(JoomlaPopup.alert('message'))
//console.log(JoomlaPopup.confirm('message?', () => {console.log(this)}))

popup.show();
