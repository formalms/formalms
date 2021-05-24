import 'regenerator-runtime/runtime'
import { contextmenu } from 'easycontext';
const axios = require('axios');
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import Tree from '../twig/tree.html.twig';
import Content from '../twig/content.html.twig';

class FolderTree {

  constructor(baseApiUrl, controller, type) {
    const _this = this;
    _this.baseApiUrl = baseApiUrl;
    _this.controller = controller;
    _this.type = type;
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');
    _this.checkExist = false;

    _this.getStatus();// From localStorage

    if (!document.querySelector('.js-disable-context-menu')) {
      if (_this.container.querySelectorAll('.folderTree__link').length) {
        _this.contextMenu();
      }
    }

    document.querySelectorAll('.tab-link').forEach((tab) => {
      tab.addEventListener('click', (e) => {
        let tabEl = e.target.closest('.tab-link');
        if (tabEl) {
          _this.type = tabEl.getAttribute('data-type');
          _this.container = document.querySelector('*[data-container=' + _this.type + ']');
        }
      });
    });

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
      const elId = el.getAttribute('data-id');
      const isOpen = el.classList.contains('ft-is-folderOpen');
      const noClick = el.classList.contains('ft-no-click');

      if (!noClick) {
        const els = _this.container.querySelectorAll('.folderTree__link');
        const clickOnArrow = event.target.classList.contains('arrow');

        if (els) {
          els.forEach(el => {
            el.classList.remove('ft-is-selected');
            if (!el.classList.contains('ft-has-child') && !el.classList.contains('ft-is-root')) {
              el.classList.remove('ft-is-folderOpen');
              event.target.classList.remove('opened');
            }
          });
        }
        if (!clickOnArrow) {
          el.classList.add('ft-is-selected');
        }

        const uls = el.parentNode.querySelectorAll('.folderTree__ul');
        uls.forEach(ul => {
          ul.remove();
        });

        if (isOpen) {
          el.classList.remove('ft-is-folderOpen');
          if (clickOnArrow) {
            event.target.classList.remove('opened');
            _this.setOpenedDirs();
            _this.setSelectedDir();
            return; // don't open
          }
        } else {
          if (clickOnArrow) {
            event.target.classList.add('opened');
          } else {
            const li = event.target.closest('.folderTree__li');
            const arrow = li.querySelector('.arrow');
            if (arrow) {
              arrow.classList.add('opened');
            }
          }
        }

        el.classList.add('ft-is-folderOpen');
        if (elId != 0) {
          // Not a refresh
          _this.setOpenedDirs();
          _this.setSelectedDir();
        }

        const Data = _this.getApiUrl('get', { id: elId });
        _this.getData(Data, el, elId, clickOnArrow);
      }
    }
  }

  contextMenu() {
    const _this = this;

    document.querySelectorAll('.context-menu').forEach((menu) => {
      menu.remove();
    });
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');

    contextmenu('.folderTree__link:not(.ft-is-root), .folderView__li', (target) => {
      _this.currentEls = _this.container.querySelectorAll('.fv-is-selected');
      _this.currentElsIds = [];
      _this.currentEls.forEach((item) => {
        _this.currentElsIds.push(parseInt(item.getAttribute('data-id')));
      });

      if (_this.currentElsIds.length == 0) {
        _this.currentElsIds = [target.getAttribute('data-id')];
      }

      const renameBtn = {
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
      };

      const copyBtn = {
        text: 'Copia',
        onClick() {
          _this.currentElsIds.forEach((id) => {
            document.querySelector('.folderView__li[data-id="' + id + '"]').classList.add('is-ready-for-copy');
          });
          document.querySelector('.folderView__copyOverlay').classList.add('is-shown');
        }
      };

      const deleteBtn = {
        text: 'Elimina',
        onClick() {
          if (confirm('Sei sicuro di voler eliminare questo elemento?')) {
            const deleteData = _this.getApiUrl('delete', { ids: _this.currentElsIds });
            axios.get(deleteData).then(() => {
              _this.currentElsIds.forEach((elId) => {
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
                  document.querySelector('.context-menu').classList.remove('menu-visible');
                }
                _this.refresh();
              });
            }).catch((error) => {
              console.log(error);
            });
          }
        }
      };

      const buttons = _this.currentElsIds.length > 1 ? [copyBtn, deleteBtn] : [renameBtn, copyBtn, deleteBtn];

      return buttons;
    });
  }

  setActiveTab() {
    const _this = this;

    _this.type = document.querySelector('.tab-link.active').getAttribute('data-type');
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');
  }

  renameEl() {
    const _this = this;

    _this.setActiveTab();

    const rename = document.querySelector('.folderTree__rename.is-show');
    const input = rename.querySelector('.folderTree__rename__input');
    const value = input.value;
    const el = input.closest('.folderTree__li') ? input.closest('.folderTree__li') : input.closest('.folderView__li');
    const elId = el.getAttribute('data-id');
    const renameData = _this.getApiUrl('rename', { id: elId, newName: value });

    axios.get(renameData).then((res) => {
      if (res) {
        rename.classList.remove('is-show');
        const treeEl = _this.container.querySelector('.folderTree__link[data-id="' + elId + '"] span');
        if (treeEl) {
          treeEl.innerHTML = value;
          el.classList.remove('ft-no-click');
        }

        const li = _this.container.querySelector('.folderView__li[data-id="' + elId + '"]');
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

  setOpenedDirs() {
    const openedEls = this.container.querySelectorAll('.ft-is-folderOpen');
    this.openedIds = [];

    openedEls.forEach((item) => {
      let id = item.getAttribute('data-id');
      if (id > 0) {
        this.openedIds.push(id);
      }
    });
    this.storeStatus();
  }

  setSelectedDir() {
    const item = this.container.querySelector('.ft-is-selected');
    if (item) {
      this.selectedId = item.getAttribute('data-id');
    }
    this.storeStatus();
  }

  storeStatus() {
    localStorage.setItem('openedIds', this.openedIds);
    localStorage.setItem('selectedId', this.selectedId);
  }

  getStatus() {
    const _this = this;
    _this.openedIds = localStorage.getItem('openedIds') ? localStorage.getItem('openedIds').split(',') : [];
    _this.selectedId = localStorage.getItem('selectedId');

    setTimeout(function() {
      _this.container.querySelector('.folderTree__link.ft-is-root').click();
    }, 1000);
  }

  refresh() {
    this.setOpenedDirs();
    this.setSelectedDir();

    this.container.querySelector('.folderTree__link.ft-is-root').click();
  }

  onDrop(event) {
    const target = event.target;
    target.classList.remove('fv-is-dropped');

    if (this.currentElsIds) {
      const parentId = parseInt(target.getAttribute('data-id'));

      if (!this.currentElsIds.includes(parentId) && target.classList.contains('is-dropzone')) {
        const reorderData = this.getApiUrl('move', { ids: this.currentElsIds, newParent: parentId });
        this.getReorderData(reorderData);
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
        // multiDragKey: 'Meta', // Fix 'ctrl' or 'Meta' button pressed
        selectedClass: 'fv-is-selectedx',
        animation: 150,
        easing: 'cubic-bezier(1, 0, 0, 1)',
        fallbackOnBody: true,
        invertSwap: true,
        swapThreshold: 0.13,
        onUpdate: function (evt) {
          const currentElement = evt.item;
          const currentElementId = currentElement.getAttribute('data-id');

          // Get parent dir from selected tree element
          const parentElement = _this.container.querySelector('.ft-is-parent .ft-is-selected');
          const parentElementId = parentElement ? parentElement.getAttribute('data-id') : 0;
          const childElement = _this.container.querySelector('.folderView__ul').querySelectorAll('.folderView__li');
          const childElementArray = [];

          if (currentElementId == parentElementId) {
            return;
          }

          childElement.forEach(el => {
            const elId = el.getAttribute('data-id');
            if (parentElementId != elId) {
              childElementArray.push(elId);
            }
          });

          const reorderData = _this.getApiUrl('reorder', { id: currentElementId, newParent: parentElementId, newOrder: childElementArray });
          _this.getReorderData(reorderData);
        }
      });
    }
  }

  getApiUrl(action, params) {
    let url = `${this.baseApiUrl}/${action}`;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

  async getReorderData(endpoint) {
    const _this = this;

    if (!_this.checkExist) {
      _this.checkExist = true;
      try {
        await axios.get(endpoint).then(() => {
          this.refresh();
          _this.checkExist = false;
        }).catch( (error) => {
          console.log(error);
        });
      } catch (e) {
        console.log(e);
      }
    }
  }

  async getData(endpoint, el , elId, clickOnArrow) {
    const _this = this;
    try {
      await axios.get(endpoint).then((response) => {
        const child = Tree(response.data);
        const childView = Content(response.data);
        const inputParent = _this.container.querySelector('#treeview_selected_' + _this.type);
        const inputState = _this.container.querySelector('#treeview_state_' + _this.type);
        inputParent.value = elId;
        inputState.value = response.data.currentState;

        if (el && el.classList.contains('ft-is-root')) {
          el.parentNode.childNodes.forEach(node => {
            if ( (node.classList) && (node.classList.contains('folderTree__ul'))) {
              node.remove();
            }
          })
        }

        if (el) {
          el.insertAdjacentHTML('afterend', child);
        }
        if (!clickOnArrow) {
          const folderView = _this.container.querySelector('.folderView');
          folderView.innerHTML = childView;
        }

        if (!document.querySelector('.js-disable-context-menu')) {
          _this.contextMenu();
        }

        if (!document.querySelector('.js-disable-sortable')) {
          _this.initSortable();
        }

        if (elId == 0) {
          if (_this.openedIds) {
            _this.openedIds.forEach((id) => {
              if (id != _this.selectedId) {
                let arrow = _this.container.querySelector('.folderTree__li[data-id="' + id + '"] .arrow');
                if (arrow) {
                  arrow.click(); // ???
                }
              }
            });
          }
          if (_this.selectedId > 0) {
            let dir = _this.container.querySelector('.folderTree__link[data-id="' + _this.selectedId + '"]');
            if (dir) {
              dir.classList.add('ft-is-selected');
              dir.click();
            }
          }
        }
        _this.selectedId = null;
      }).catch((error) => {
        console.log(error)
      });
    } catch (e) {
      console.log(e);
    }
  }
}

export default FolderTree
