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
  popupTemplate = popupTemplate;
  popupButtons = [];

  constructor() {
    super();
  }

  connectedCallback() {
    if (this.children.length) return;

    // Render a template
    const template = document.createElement('template');
    template.innerHTML = this.popupTemplate;

    this.dialog = document.createElement('dialog');
    this.dialog.appendChild(template.content);
    this.appendChild(this.dialog);

    // Get template parts
    this.popupTmplH = this.querySelector('header');
    this.popupTmplB = this.querySelector('section');
    this.popupTmplF = this.querySelector('footer');

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
        break;
      default:
        throw new Error('Unknown popup type requested');
    }

    // Create buttons if any
    if (this.popupButtons.length) {
      const buttonsLocation = this.popupTmplF || this.popupTmplB;

      this.popupButtons.forEach((btnData) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.insertAdjacentHTML('afterbegin', btnData.label);

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
    }

    console.log(this)
  }

  getBody() {
    return this.popupTmplB;
  }

  show(){
    if (!this.parentElement) {
      document.body.appendChild(this);
    }

    this.dialog.showModal();
  }

  close() {
    this.dialog.close();
  }
}

window.JoomlaPopup = JoomlaPopup;
customElements.define('joomla-popup', JoomlaPopup);

export { JoomlaPopup };


// ================= testing ======================= //
const popup = new JoomlaPopup;
popup.popupType = 'inline';
popup.popupHeader = 'The header';
popup.popupContent = '<strong>blabla very strong text</strong>';
popup.popupButtons = [
  {label: 'Yes', onClick: () => popup.close()},
  {label: 'No', onClick: () => popup.close(), className: 'btn btn-outline-danger ms-2'}
]

console.log([popup]);

popup.show();
