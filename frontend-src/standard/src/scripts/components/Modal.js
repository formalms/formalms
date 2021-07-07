import ModalTwig from '../twig/modal.html.twig';

/**
 * Modal
 */
class ModalElement { 

  constructor(modalClass = '', target = '#lms_main_container') {
    this.ModalClass = modalClass;
    this.Target = target;
    this.Title = 'Title';
    this.Content = null;
    this._OnCloseEvent = null;
  }

  set OnClose(val) {
    this._OnCloseEvent = val;
  }

  Create(modalClass, target) {
    var dest = document.querySelector(target);
    var modalDiv = this.CreateElementFromHTML(ModalTwig({
      class: modalClass,
      title: this.Title,
      content: this.Content
    }));
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

  CreateElementFromHTML(htmlString) {
    var div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild; 
  }

  Open(onOpen) {
    this.DOMElWrapper = this.Create(this.ModalClass, this.Target);
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
    return document.querySelectorAll('#easy-modal')[0];
  }
  
}

module.exports = ModalElement