import { contextmenu } from 'easycontext';
import Config from '../config/config';
const axios = require('axios');
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import Tree from '../twig/tree.html.twig';
import Content from '../twig/content.html.twig';

class FolderTree {

  constructor(type) {
    const _this = this;
    _this.type = type;
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');

    const btn = document.querySelector('.js-ft-rename-el');
    const inputRename = document.querySelector('.folderTree__rename__input');

    if (!document.querySelector('.js-disable-context-menu')) {
      if (_this.container.querySelectorAll('.folderTree__link').length) {
        _this.contextMenu();
      }
    }

    if (btn) {
      btn.addEventListener('click', () => {
        _this.renameEl();
      });
    }

    if (inputRename) {
      inputRename.addEventListener('keyup', (e) => {
        if (e.keyCode === 13) {
          _this.renameEl();
          e.preventDefault();
        }
      });
    }

    _this.container.addEventListener('contextmenu', (event) => {
      if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
        _this.container.querySelector('.context-menu').classList.remove('menu-visible');
      }
    });

    _this.container.addEventListener('click', (event) => {
      this.clickOnFolder(event);
    });

    if (!_this.container.querySelector('.js-disable-sortable')) {
      _this.initSortable();
    }
    if (!_this.container.querySelector('.js-disable-drag-and-drop')) {
      _this.removeDragDropListener();
      _this.initDragDrop();
    }
  }

  clickOnFolder(event) {
    const _this = this;
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
        const els = _this.container.querySelectorAll('.folderTree__link');

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
        const getLoData = _this.getApiUrl('get', elId, { type: _this.type });

        axios.get(getLoData).then((response) => {
          const child = Tree(response.data);
          const childView = Content(response.data);
          const folderView = _this.container.querySelector('.folderView');
          const inputParent = _this.container.querySelector('#treeview_selected_' + _this.type);
          const inputState = _this.container.querySelector('#treeview_state_' + _this.type);
          inputParent.value = elId;
          inputState.value = response.data.currentState;
          el.insertAdjacentHTML('afterend', child);
          folderView.innerHTML = childView;

          if (!document.querySelector('.js-disable-context-menu')) {
            _this.contextMenu();
          }

          if (!document.querySelector('.js-disable-sortable')) {
            _this.initSortable();
          }
        }).catch((error) => {
          console.log(error)
        });
      }
    }
  }

  contextMenu() {
    const _this = this;

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
                _this.renameEl();
              });
            }

            if (inputRename) {
              inputRename.addEventListener('keyup', (e) => {
                if (e.keyCode === 13) {
                  _this.renameEl();
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
              const elsNotClick = _this.container.querySelectorAll('.ft-no-click');
              if (elsNotClick) {
                for (let el of elsNotClick) {
                  el.addEventListener('click', (e) => {
                    e.preventDefault();
                  })
                }
              }

              // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
              _this.container.addEventListener('click', (event) => {
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
              const deleteLoData = _this.getApiUrl('delete', elId, { type: _this.type });
              axios.get(deleteLoData).then(() => {
                const elTree = _this.container.querySelector('.folderTree__li[data-id="' + elId + '"]');
                if (elTree) {
                  const ul = elTree.parentNode;
                  elTree.remove();

                  if (!ul.querySelector('li')) {
                    ul.remove();
                  }
                }
                const el = _this.container.querySelector('.folderView__li[data-id="' + elId + '"]');
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
    const rename = this.container.querySelector('.folderTree__rename');
    const input = rename.querySelector('.folderTree__rename__input');
    const value = input ? input.value : null;
    const el = input.closest('.folderTree__li');
    const elId = el.getAttribute('data-id');
    const renameLoData = this.getApiUrl('rename', elId, { type: this.type, newName: value });

    axios.get(renameLoData).then((res) => {
      if (res) {
        rename.classList.remove('is-show');
        el.querySelector('span').innerHTML = value;
        el.classList.remove('ft-no-click');

        const li = this.container.querySelector('.folderView__li[data-id="' + elId + '"]');
        if (li) {
          li.querySelector('.folderView__label').innerHTML = value;
        }

        rename.remove();
      }
    }).catch( (error) => {
      console.log(error);
    });
  }

  removeDragDropListener() {
    this.container.removeEventListener('dragstart', this.onDragStart.bind(this))
    this.container.removeEventListener('dragover', this.onDragOver.bind(this))
    this.container.removeEventListener('dragleave', this.onDragLeave.bind(this))
    this.container.removeEventListener('drop', this.onDrop.bind(this))
  }

  initDragDrop() {
    this.container.addEventListener('dragstart', this.onDragStart.bind(this))
    this.container.addEventListener('dragover', this.onDragOver.bind(this))
    this.container.addEventListener('dragleave', this.onDragLeave.bind(this))
    this.container.addEventListener('drop', this.onDrop.bind(this))
  }

  selectItems() {
    this.container.querySelectorAll('.folderView__li').forEach((item) => {
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

      this.currentEls = this.container.querySelectorAll('.fv-is-selected');
      this.currentElsIds = [];
      this.currentEls.forEach((item) => {
        this.currentElsIds.push(parseInt(item.getAttribute('data-id')));
      });

      // Single drop
      if (!this.currentElsIds.includes(this.currentElId)) {
        this.currentEls = [event.target];
        this.currentElsIds = [this.currentElId];
        this.selectItems();
      }
      console.log(this.currentElsIds, 'this.currentElsIds onDragStart');
    }
  }

  onDragOver(event) {
    const target = event.target;

    if (this.currentElsIds) {
      if (!this.currentElsIds.includes(target.getAttribute('data-id')) && target.classList.contains('is-dropzone')) {
        target.classList.add('fv-is-dropped');
        event.preventDefault();
      }
    }
  }

  onDragLeave(event) {
    const target = event.target;

    if (this.currentElsIds) {
      if (!this.currentElsIds.includes(target.getAttribute('data-id')) && target.classList.contains('is-dropzone')) {
        target.classList.remove('fv-is-dropped');
      }
    }
  }

  refresh() {
    this.container.querySelector('.folderTree__link.ft-is-root').click();
    this.currentElId = null;
    this.currentEl = null;
    this.currentElsId = null;
    this.currentEls = null;
  }

  onDrop(event) {
    const target = event.target;
    target.classList.remove('fv-is-dropped');

    if (this.currentElsIds) {
      const parentId = parseInt(target.getAttribute('data-id'));

      if (!this.currentElsIds.includes(parentId) && target.classList.contains('is-dropzone')) {
        const type = window.type;

        console.log(this.currentElsIds, '-> ' + parentId);

        const reorderLoData = this.getApiUrl('reorder', this.currentElsIds, { type, newParent: parentId });
        axios.get(reorderLoData).then(() => {
          // Refresh
          this.refresh();
        }).catch((error) => {
          console.log(error);
        });
      }
    }
  }

  initSortable() {
    const _this = this;
    const view = this.container.querySelector('.js-sortable-view');

    if (view) {
      new Sortable.create(view, {
        draggable: '.folderView__li',
        dataIdAttr: 'data-id',
        multiDrag: true, // Enable the plugin
        multiDragKey: 'Meta', // Fix 'ctrl' or 'Meta' button pressed
        selectedClass: 'fv-is-selected',
        animation: 150,
        easing: 'cubic-bezier(1, 0, 0, 1)',
        fallbackOnBody: true,
        invertSwap: true,
        swapThreshold: 0.43,
        onUpdate: function (evt) {
          const currentElement = evt.item;
          const currentElementId = currentElement.getAttribute('data-id');
          const parentElement = _this.container.querySelector('.ft-is-selected');
          const childElement = _this.container.querySelector('.folderView__ul').querySelectorAll('.folderView__li');
          const childElementArray = [];
          const parentElementId = parentElement ? parentElement.getAttribute('data-id') : 0;

          childElement.forEach(el => {
            const elId = el.getAttribute('data-id');
            childElementArray.push(elId);
          });

          const reorderLoData = _this.getApiUrl('reorder', currentElementId, { type: _this.type, newParent: parentElementId, newOrder: childElementArray });
          axios.get(reorderLoData).then(() => {
            _this.refresh();
          }).catch( (error) => {
            console.log(error);
          });
        }
      });
    }
  }

  getApiUrl(action, id, params) {
    let url = `${Config.apiUrl}lms/lo/${action}&id=${id}`;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }
}

export default FolderTree
