/**
 * Lightbox
 */
 // import Content from '../twig/li.html.twig';

 class Lightbox {

  constructor() {
    this.ActiveModals = [];
    this.ModalExists = false;
  }

  open(label = 'modal-' + this.ActiveModals.length, onOpen = null, onClose = null) {
    var modalCreated = new ModalElement(label);
    modalCreated.Open(onOpen);
    modalCreated.OnClose = onClose;
    this.ActiveModals.push({
      label,
      modalCreated
    });
  }

}


/**
 * Modal
 */
class ModalElement { 

  constructor(modalClass = '', target = '#lms_main_container') {
    this.ModalClass = modalClass;
    this.DOMElWrapper = this.Create(modalClass, target);
    this._OnCloseEvent = null;
  }

  set Title(val) {
    this.GetModal().querySelector('#title').innerHTML = val;
  }

  set OnClose(val) {
    this._OnCloseEvent = val;
  }

  Create(modalClass, target) {
    var dest = document.querySelector(target);
    var modalDiv = document.createElement('div');
    modalDiv.classList.add(modalClass);
    modalDiv.innerHTML = '\
    <div class="modal" id="easy-modal">\
      <div class="modal-navbar">\
        <div id="modal-title" class="modal-title">\
          <h1 id="title">Titolo della modale</h1>\
        </div>\
        <div id="close_handler" class="modal-exit close_handler">\
          <div id="mdiv">\
            <div class="mdiv">\
              <div class="md"></div>\
            </div>\
          </div>\
        </div>\
      </div>\
      <div class="modal-bg"></div>\
      <div class="modal-container">\
        <div class="content-wrapper">\
        </div>\
      </div>\
    </div>';
    dest.parentNode.insertBefore( modalDiv, dest.nextSibling );
    var exits = modalDiv.querySelectorAll('.modal-exit');
    exits.forEach(exit => {
      exit.addEventListener('click', (event) => {
        event.preventDefault();
        this.Close()
      });
    });
    return modalDiv;
  }

  Open(onOpen) {
    this.GetModal().classList.add('open');
    if(onOpen) {
      onOpen(this);
    }
  }

  Close() {
    document.querySelector(`.${this.ModalClass}`).remove();
    this.GetModal().remove();
    if(this._OnCloseEvent) {
      this._OnCloseEvent(this);
    }
  }

  GetModal() {
    return this.DOMElWrapper.querySelectorAll('#easy-modal')[0];
  }

  GetContentWrapper() {
    return this.DOMElWrapper.querySelector('.content-wrapper');
  }

  InjectIframe(src = null, options = {}) {
    var iframe = document.createElement('iframe');
    iframe.setAttribute('src', src);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('scrolling', 'no');
    Object.keys(options).forEach(optionKey => {
      iframe.setAttribute(optionKey, options[optionKey]);
    })
    this.InjectContent(iframe);
  }

  InjectContent(element) {
    this.GetContentWrapper().appendChild(element);
    return this;
  }
  
}


module.exports = Lightbox;