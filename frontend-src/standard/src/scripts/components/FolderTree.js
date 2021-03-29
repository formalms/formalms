import { contextmenu } from 'easycontext';
import Config from '../config/config';
const axios = require('axios');
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import Tree from '../twig/tree.html.twig';
import Content from '../twig/content.html.twig';

class FolderTree {

  constructor(tabAlias) {
      console.log(tabAlias, 'INIT');
      const btn = document.querySelector('.js-ft-rename-el');
      const inputRename = document.querySelector('.folderTree__rename__input');
    
      this.dragged;

      if (document.querySelectorAll('.folderTree__link').length) {
        contextMenu();
      }

      if (btn) {
        btn.addEventListener('click', () => {
          this.renameEl();
        });
      }

      if (inputRename) {
        inputRename.addEventListener('keyup', (e) => {
          if (e.keyCode === 13) {
            this.renameEl();
            e.preventDefault();
          }
        });
      }

      document.addEventListener('contextmenu', (event) => {
        if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
          document.querySelector('.context-menu').classList.remove('menu-visible');
        }
      });

      document.addEventListener('click', (e) => { this.clickOnFolder(e, tabAlias); });
      initSortable();
      initDragDrop();
  }

  clickOnFolder(event, tabAlias) {
    const target = event.target;
    const el = target.closest('.folderTree__link');

    if (el) {
      console.log(tabAlias, 'CLICK');
      const isOpen = el.classList.contains('ft-is-folderOpen')
      const noClick = el.classList.contains('ft-no-click');

      if (!noClick) {
        const els = document.querySelectorAll('.folderTree__link');
        if (els) {
          els.forEach(el => {
            el.classList.remove('ft-is-selected');
            if (!el.classList.contains('ft-has-child')) {
              el.classList.remove('ft-is-folderOpen');
            }
          });
        }
        el.classList.add('ft-is-selected');
        if (isOpen && (!el.classList.contains('ft-is-root')) ) {
          el.parentNode.querySelector('.folderTree__ul').remove();
        } else {
          el.classList.add('ft-is-folderOpen');
        }
        const elId = el.getAttribute('id');
        const getLoData = Config.apiUrl + 'lms/lo/get&id=' + elId;
        axios.get(getLoData).then( (response) => {
          const child = Tree(response.data);
          const childView = Content(response.data);
          const folderView = document.querySelector('.folderView');
          const inputParent = document.querySelector('#treeview_selected_organization_' + tabAlias);
          const inputState = document.querySelector('#treeview_state_organization_' + tabAlias);
          inputParent.value = elId;
          inputState.value = response.data.currentState;
          if (!el.classList.contains('ft-is-root')) {
            el.insertAdjacentHTML('afterend',child);
          }
          folderView.innerHTML = childView;
          contextMenu();
          initSortable();
          initDragDrop();
        }).catch( (error) => {
          console.log(error)
        });
        event.preventDefault();
      }
    }

  }

  renameEl() {
    const rename = document.querySelector('.folderTree__rename');
    const input = document.querySelector('.folderTree__rename__input');
    const value = input.value;
    const el = input.parentNode.parentNode;
    const elId = el.getAttribute('id');
    const renameLoData = Config.apiUrl + 'lms/lo/rename&id=' + elId + '&newName=' + value;

    axios.get(renameLoData).then().catch( (error) => {
      console.log(error);
    });

    rename.classList.remove('is-show');
    el.childNodes[0].innerHTML = value;
    el.classList.remove('ft-no-click');

    document.querySelector('.folderView').querySelector('.folderView__li[data-id="' + elId + '"]').querySelector('.folderView__label').innerHTML = value;
  }

}

function initSortable() {
  const view = document.querySelector('.js-sortable-view');

  if (view) {
    new Sortable.create(view, {
      draggable: '.folderView__li',
      animation: 150,
      easing: 'cubic-bezier(1, 0, 0, 1)',
      fallbackOnBody: true,
      invertSwap: true,
      swapThreshold: 0.43,
      onUpdate: function (evt) {
        const currentElement = evt.item;
        const currentElementId = currentElement.id;
        const parentElement = document.querySelector('.ft-is-selected');
        const childElement = document.querySelector('.folderView__ul').querySelectorAll('.folderView__li');
        const childElementArray = [];
        let parentElementId = 0;
        console.log('current: ' + currentElementId);

        if (parentElement) {
          parentElementId = parentElement ? parentElement.id : 0;
          console.log('parent: ' + parentElementId);
        }

        childElement.forEach(el => {
          const elId = el.id;
          childElementArray.push(elId);
        });

        console.log('child order: ' + childElementArray);

        const reorderLoData = Config.apiUrl + 'lms/lo/reorder&id=' + currentElementId + '&newParent=' + parentElementId + '&newOrder=' + childElementArray;
        axios.get(reorderLoData).then().catch( (error) => {
          console.log(error);
        });
      }
    });
  }
}

function initDragDrop() {
    let currentEl, currentElId;

    document.addEventListener('dragstart', (event) => {
      if (event.target.classList.contains('is-droppable')) {
        currentEl = event.target;
        currentElId = currentEl.id;
      }
    });

    document.addEventListener('dragover', (event) => {
      const target = event.target;

      if (currentEl) {
        if ( (currentElId !== target.id) && (target.classList.contains('is-dropzone')) ) {
            console.log('drag over')
            target.classList.add('fv-is-dropped');
            event.preventDefault();
        }
      }
    });

    document.addEventListener('dragleave', (event) => {
      const target = event.target;

      if (currentEl) {
        if ((currentElId !== target.id) && (target.classList.contains('is-dropzone'))) {
          target.classList.remove('fv-is-dropped');
        }
      }
    });

    document.addEventListener('drop', (event) => {
      const target = event.target;
      target.classList.remove('fv-is-dropped');

      if (currentEl) {
        if ( (currentElId !== target.id) && (target.classList.contains('is-dropzone')) ) {
          const reorderLoData = Config.apiUrl + 'lms/lo/reorder&id=' + currentElId + '&newParent=' + event.target.id;
          axios.get(reorderLoData).then(() => {
            if (target.classList.contains('ft-is-folderOpen') && (currentEl.classList.contains('folderTree__li') )) {
              const nextElementSibling = target.nextElementSibling;
              if (nextElementSibling.classList.contains('folderTree__ul')) {
                nextElementSibling.appendChild(currentEl);
              } else {
                currentEl.remove();
              }
            } else {
              currentEl.remove();
            }
            document.querySelector('.folderView__li[data-id="' + currentElId + '"]').remove();
          }).catch((error) => {
            console.log(error);
          });
        }
      }
    });
}

function contextMenu() {
    contextmenu('.folderTree__link:not(.ft-is-root)', (target) => {
        return [
            {
                text: 'Rinomina',
                onClick() {
                    const rename = document.querySelector('.folderTree__rename');
                    const renameInput = document.querySelector('.folderTree__rename__input');

                    if (target.classList.contains('folderTree__rename__input') === false) {
                        if (target.hasAttribute('id')) {
                            target.classList.add('ft-no-click');
                            target.appendChild(rename);
                        } else {
                            target.parentNode.classList.add('ft-no-click');
                            target.parentNode.appendChild(rename);
                        }
                        rename.classList.add('is-show');
                        renameInput.focus();
                        renameInput.setAttribute('value', target.textContent);

                        // Rendo tutti gli elementi non cliccabile se sono in modalità rinomina
                        const elsNotClick = document.querySelectorAll('.ft-no-click');
                        if (elsNotClick) {
                            for (let el of elsNotClick) {
                                el.addEventListener('click', (e) => {
                                    e.preventDefault();
                                })
                            }
                        }

                        // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
                        document.addEventListener('click', (event) => {
                            if (event.detail) { // fix trigger click se premo su spazio
                                const clickInside = rename.contains(event.target);
                                if (event.target.classList.contains('menu-item-clickable')) {
                                    event.stopPropagation();
                                } else {
                                    if (!clickInside) {
                                        renameInput.blur();
                                        rename.classList.remove('is-show');
                                    }
                                }
                            }
                        });
                    }

                }
            },
            {
                text: 'Elimina',
                onClick() {
                    let siblings;
                    let elId;

                    if (target.hasAttribute('id')) {
                        siblings = target.parentNode.children;
                        target.parentNode.querySelector('.folderTree__link').remove();
                        elId = target.getAttribute('id');
                    } else {
                        siblings = target.parentNode.parentNode.children;
                        target.parentNode.parentNode.querySelector('.folderTree__link').remove();
                        elId = target.parentNode.getAttribute('id');
                    }

                    document.querySelector('.folderView').querySelector('.folderView__li[data-id="' + elId + '"]').parentNode.remove();

                    if (siblings) {
                        for (let el of siblings) {
                            if (el.classList.contains('folderTree__ul')) {
                                el.classList.remove('folderTree__ul');
                            }
                        }
                    }

                    const deleteLoData = Config.apiUrl + 'lms/lo/delete&id=' + elId;
                    axios.get(deleteLoData).then().catch( (error) => {
                        console.log(error);
                    });

                }
            }
        ]
    })
}

export default FolderTree
