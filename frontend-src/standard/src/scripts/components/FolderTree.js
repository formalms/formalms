import { contextmenu } from 'easycontext';
import Config from '../config/config';
const axios = require('axios');
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import Tree from '../twig/tree.html.twig';
import Content from '../twig/content.html.twig';

class FolderTree {

  constructor(type) {
    this._this = this
    this.type = type;
    this.currentEl;
    this.currentElId;
    this.container = document.querySelector('*[data-container=' + this.type + ']');
    this.dragged;

    const btn = document.querySelector('.js-ft-rename-el');
    const inputRename = document.querySelector('.folderTree__rename__input');

    if (!document.querySelector('.js-disable-context-menu')) {
      if (this.container.querySelectorAll('.folderTree__link').length) {
        this.contextMenu();
      }
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

    this.container.addEventListener('contextmenu', (event) => {
      if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
        this.container.querySelector('.context-menu').classList.remove('menu-visible');
      }
    });

    this.container.addEventListener('click', (event) => {
      this.clickOnFolder(event, this.container, this.type);
    });

    if (!this.container.querySelector('.js-disable-sortable')) {
      this.initSortable(this.container, this.type);
    }
    if (!this.container.querySelector('.js-disable-drag-and-drop')) {
      this.removeDragDropListener(this.container);
      this.initDragDrop(this.container);
    }
  }

  clickOnFolder(event, container, type) {
    this.container = container;
    this.type = type;

    let el = event.target.closest('.folderTree__link');
    if (!el) {
      const li = event.target.closest('.folderTree__li');
      if (li) {
        el = li.querySelector('.folderTree__link');
      }
    }

    if (el) {
      const isOpen = el.classList.contains('ft-is-folderOpen');
      const noClick = el.classList.contains('ft-no-click');

      if (!noClick) {
        const els = this.container.querySelectorAll('.folderTree__link');

        if (els) {
          els.forEach(el => {
            el.classList.remove('ft-is-selected');
            if (!el.classList.contains('ft-has-child') && !el.classList.contains('ft-is-root')) {
              el.classList.remove('ft-is-folderOpen');
            }
          });
        }
        el.classList.add('ft-is-selected');

        const uls = el.parentNode.querySelectorAll('.folderTree__ul');
        uls.forEach(ul => {
          ul.remove();
        });

        if (isOpen) {
          el.classList.remove('ft-is-folderOpen');
          if (event.target.classList.contains('arrow')) {
            return; // don't open
          }
        }

        el.classList.add('ft-is-folderOpen');

        const elId = el.getAttribute('data-id');
        const getLoData = getApiUrl('get', elId, { type: this.type });

        axios.get(getLoData).then((response) => {
          const child = Tree(response.data);
          const childView = Content(response.data);
          const folderView = this.container.querySelector('.folderView');
          const inputParent = this.container.querySelector('#treeview_selected_' + this.type);
          const inputState = this.container.querySelector('#treeview_state_' + this.type);
          inputParent.value = elId;
          inputState.value = response.data.currentState;
          el.insertAdjacentHTML('afterend', child);
          folderView.innerHTML = childView;

          if (!document.querySelector('.js-disable-context-menu')) {
            this.contextMenu();
          }

          if (!document.querySelector('.js-disable-sortable')) {
            this.initSortable(this.container, this.type);
          }

          if (!document.querySelector('.js-disable-drag-and-drop')) {
            this.removeDragDropListener(this.container);
            this.initDragDrop(this.container);
          }
        }).catch((error) => {
          console.log(error)
        });
      }
    }
  }

  contextMenu() {
    const type = window.type;
    const container = document.querySelector('*[data-container=' + type + ']');
    const obj = this;

    contextmenu('.folderTree__link:not(.ft-is-root)', (target) => {
      return [
        {
          text: 'Rinomina',
          onClick() {
            const renameOrig = document.querySelector('.folderTree__rename');
            const rename = renameOrig.cloneNode(true);
            const renameInput = rename.querySelector('.folderTree__rename__input');

            const btn = rename.querySelector('.js-ft-rename-el');
            const inputRename = rename.querySelector('.folderTree__rename__input');

            if (btn) {
              btn.addEventListener('click', () => {
                obj.renameEl();
              });
            }

            if (inputRename) {
              inputRename.addEventListener('keyup', (e) => {
                if (e.keyCode === 13) {
                  obj.renameEl();
                  e.preventDefault();
                }
              });
            }

            if (target.classList.contains('folderTree__rename__input') === false) {
              if (target.hasAttribute('data-id')) {
                target.classList.add('ft-no-click');
                target.appendChild(rename);
              } else {
                target.parentNode.classList.add('ft-no-click');
                target.parentNode.appendChild(rename);
              }
              rename.classList.add('is-show');
              renameInput.focus();
              renameInput.setAttribute('value', target.textContent);

              // Rendo tutti gli elementi non cliccabili se sono in modalità rinomina
              const elsNotClick = container.querySelectorAll('.ft-no-click');
              if (elsNotClick) {
                for (let el of elsNotClick) {
                  el.addEventListener('click', (e) => {
                    e.preventDefault();
                  })
                }
              }

              // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
              container.addEventListener('click', (event) => {
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
            const elId = target.getAttribute('data-id');

            if (confirm('Sei sicuro di voler eliminare questo elemento?')) {
              const deleteLoData = getApiUrl('delete', elId, { type });
              axios.get(deleteLoData).then(() => {
                const elTree = container.querySelector('.folderTree__li[data-id="' + elId + '"]');
                if (elTree) {
                  const ul = elTree.parentNode;
                  elTree.remove();

                  if (!ul.querySelector('li')) {
                    ul.remove();
                  }
                }
                const el = container.querySelector('.folderView__li[data-id="' + elId + '"]');
                if (el) {
                  el.remove();
                }
              }).catch((error) => {
                console.log(error);
              });
            }
          }
        }
      ];
    });
  }

  renameEl() {
    const type = window.type;
    const container = document.querySelector('*[data-container=' + type + ']');
    const rename = container.querySelector('.folderTree__rename');
    const input = rename.querySelector('.folderTree__rename__input');
    const value = input ? input.value : null;
    const el = input.closest('.folderTree__li');
    const elId = el.getAttribute('data-id');
    const renameLoData = getApiUrl('rename', elId, { type, newName: value });

    axios.get(renameLoData).then((res) => {
      if (res) {
        rename.classList.remove('is-show');
        el.querySelector('span').innerHTML = value;
        el.classList.remove('ft-no-click');

        const li = container.querySelector('.folderView__li[data-id="' + elId + '"]');
        if (li) {
          li.querySelector('.folderView__label').innerHTML = value;
        }

        rename.remove();
      }
    }).catch( (error) => {
      console.log(error);
    });
  }

  removeDragDropListener(container) {
    container.removeEventListener('dragstart', this.onDragStart.bind(this))
    container.removeEventListener('dragover', this.onDragOver.bind(this))
    container.removeEventListener('dragleave', this.onDragLeave.bind(this))
    container.removeEventListener('drop', this.onDrop.bind(this))
  }

  initDragDrop(container) {
    container.addEventListener('dragstart', this.onDragStart.bind(this))
    container.addEventListener('dragover', this.onDragOver.bind(this))
    container.addEventListener('dragleave', this.onDragLeave.bind(this))
    container.addEventListener('drop', this.onDrop.bind(this))
  }

  selectItems(container) {
    container.querySelectorAll('.folderView__li').forEach((item) => {
      item.classList.remove('fv-is-selected');
      const item_id = parseInt(item.getAttribute('data-id'));

      if (this.currentElsIds.includes(item_id)) {
        item.classList.add('fv-is-selected');
      }
    });
  }

  onDragStart(event) {
    if (event.target.classList.contains('is-droppable')) {
      this.currentEl = event.target;
      this.currentElId = parseInt(this.currentEl.getAttribute('data-id'));

      const container = event.target.closest('.folderView__ul');
      this.currentEls = container.querySelectorAll('.fv-is-selected');
      this.currentElsIds = [];
      this.currentEls.forEach((item) => {
        this.currentElsIds.push(parseInt(item.getAttribute('data-id')));
      });

      // Single drop
      if (!this.currentElsIds.includes(this.currentElId)) {
        this.currentEls = [event.target];
        this.currentElsIds = [this.currentElId];
        this.selectItems(container);
      }
    }
  }

  onDragOver(event) {
    const target = event.target;
    // console.log(this.currentElsIds, 'selectedItems DRAG OVER');

    if (this.currentElsIds) {
      if (!this.currentElsIds.includes(target.getAttribute('data-id')) && (target.classList.contains('is-dropzone'))) {
        target.classList.add('fv-is-dropped');
        event.preventDefault();
      }
    }
  }

  onDragLeave(event) {
    const target = event.target;
    // console.log(this.currentElsIds, 'selectedItems DRAG LEAVE');

    if (this.currentEl) {
      if (this.currentElsIds.includes(target.getAttribute('data-id')) && (target.classList.contains('is-dropzone'))) {
        target.classList.remove('fv-is-dropped');
      }
    }
  }

  onDrop(event) {
    const target = event.target;
    console.log(this.currentElsIds, 'selectedItems DROP');
    target.classList.remove('fv-is-dropped');

    if (this.currentEl) {
      const parentId = target.getAttribute('data-id');
      if ((this.currentElId !== parentId) && (target.classList.contains('is-dropzone'))) {
        const type = window.type;

        const reorderLoData = getApiUrl('reorder', this.currentElId, { type, newParent: parentId });
        axios.get(reorderLoData).then(() => {
          if (target.classList.contains('ft-is-folderOpen') && (this.currentEl.classList.contains('folderTree__li') )) {
            const nextElementSibling = target.nextElementSibling;
            if (nextElementSibling && nextElementSibling.classList.contains('folderTree__ul')) {
              nextElementSibling.appendChild(this.currentEl);
            } else {
              this.currentEl.remove();
            }
          } else {
            this.currentEl.remove();
          }
          const el = this.querySelector('.folderView__li[data-id="' + this.currentElId + '"]');

          // Refresh
          this.querySelector('.folderTree__link.ft-is-root').click();
          
          setTimeout(() => {
            const parentLiArrow = document.querySelector('.folderTree__li[data-id="' + parentId + '"] .arrow');

            if (parentLiArrow) {
              // Open parent dir after refresh
              parentLiArrow.click();
            }
          }, 500);

          if (el) {
            el.remove();
            this.currentElId = null;
            this.currentEl = null;
          }
        }).catch((error) => {
          console.log(error);
        });
      }
    }
  }

  initSortable(container, type) {
    const view = container.querySelector('.js-sortable-view');

    if (view) {
      console.log(Sortable);
      new Sortable.create(view, {
        draggable: '.folderView__li',
        multiDrag: true, // Enable the plugin
        multiDragKey: 'Meta',
        selectedClass: 'xxselected',
        animation: 150,
        easing: 'cubic-bezier(1, 0, 0, 1)',
        fallbackOnBody: true,
        invertSwap: true,
        swapThreshold: 0.43,
        onUpdate: function (evt) {
          const currentElement = evt.item;
          const currentElementId = currentElement.getAttribute('data-id');
          const parentElement = container.querySelector('.ft-is-selected');
          const childElement = container.querySelector('.folderView__ul').querySelectorAll('.folderView__li');
          const childElementArray = [];
          let parentElementId = 0;

          if (parentElement) {
            parentElementId = parentElement ? parentElement.getAttribute('data-id') : 0;
          }

          childElement.forEach(el => {
            const elId = el.getAttribute('data-id');
            childElementArray.push(elId);
          });

          const reorderLoData = getApiUrl('reorder', currentElementId, { type, newParent: parentElementId, newOrder: childElementArray });
          axios.get(reorderLoData).then(() => {
            const parentEl = container.querySelector('.folderTree__link[data-id="' + parentElementId + '"]');
            if (parentEl) {
              parentEl.click();
            }
          }).catch( (error) => {
            console.log(error);
          });
        }
      });
    }
  }
}

function getApiUrl(action, id, params) {
  let url = `${Config.apiUrl}lms/lo/${action}&id=${id}`;
  if (!params) {
    params = {};
  }
  url += '&' + new URLSearchParams(params).toString();

  return url;
}

export default FolderTree
