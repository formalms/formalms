import ContextMenu from '../components/ContextMenu';
import Content from '../twig/content.html.twig';
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
const axios = require('axios');

class FolderView {

  constructor(baseApiUrl, controller, type) {
    const _this = this;
    _this.contextMenu = new ContextMenu();

    _this.baseApiUrl = baseApiUrl;
    _this.controller = controller;
    _this.type = type;
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');
    _this.container.addEventListener('click', (e) => { _this.toggleSelectEl(e); });
    _this.container.addEventListener('click', (e) => { _this.triggerClick(e); });
    _this.container.addEventListener('dblclick', (e) => { _this.triggerDblClick(e); });
    _this.emptySelectedItems();

    _this.getData(_this.getApiUrl('get'));
  }

  getContainer() {
    return this.container;
  }

  getType() {
    return this.type;
  }

  getSelectedItems() {
    return this.selectedItems;
  }

  emptySelectedItems() {
    this.selectedItems = {};
    this.container = this.getContainer();

    // Unselect all
    this.container.querySelectorAll('.fv-is-selected').forEach((item) => {
      item.classList.remove('fv-is-selected');
    });
  }

  toggleSelectedItem(el, id, notEmpty) {
    const li = el.closest('.folderView__li');

    if (!li) {
      return;
    }

    if (notEmpty) {
      this.selectedItems[id] = !this.selectedItems[id];

      if (this.selectedItems[id]) {
        li.classList.add('fv-is-selected');
      } else {
        li.classList.remove('fv-is-selected');
      }
    } else {
      this.emptySelectedItems();
      this.selectedItems[id] = true;
      li.classList.add('fv-is-selected');
    }
    return this.selectedItems[id];
  }

  toggleSelectEl(e) {
    const el = e.target;
    this.toggleSelectedItem(el, el.getAttribute('data-id'), (e.ctrlKey || e.metaKey));
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

          _this.reorderData(_this.getApiUrl('reorder', { id: currentElementId, newParent: parentElementId, newOrder: childElementArray }));
        }
      });
    }
  }

  async reorderData(endpoint) {
    const _this = this;

    if (!_this.checkExist) {
      _this.checkExist = true;
      try {
        await axios.get(endpoint).then(() => {
          _this.getData(_this.getApiUrl('get'));
          _this.checkExist = false;
        }).catch( (error) => {
          console.log(error);
        });
      } catch (e) {
        console.log(e);
      }
    }
  }

  async getData(endpoint, el , elId) {
    const _this = this;
    try {
      await axios.get(endpoint).then((response) => {
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
        
        const folderView = _this.container.querySelector('.folderView');
        folderView.innerHTML = childView;

        if (!document.querySelector('.js-disable-context-menu')) {
          _this.contextMenu.set(_this.type);
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

  triggerClick(e) {
    const el = e.target;
    const _this = this;

    if (el) {
      if (el.tagName === 'BUTTON') {
        e.preventDefault();
      }
      
      const li = el.closest('.folderView__li');
      if (!li) {
        return;
      }
      const elId = li.getAttribute('data-id');

      if (!elId) {
        return;
      }

      if (el.classList.contains('fv-is-delete')) {
        e.preventDefault();
        if (confirm('Sei sicuro di voler eliminare questo elemento?')) {
          const deleteLoData = _this.getApiUrl('delete', { id: elId });
          axios.get(deleteLoData).then(() => {
            const elTree = _this.container.querySelector('.folderTree__li[data-id="' + elId + '"]');
            if (elTree) {
              const ul = elTree.parentNode;
              elTree.remove();

              if (!ul.querySelector('li')) { // Last element in parent dir
                ul.remove();
                // Refresh tree of parent node
                const parent = _this.container.querySelector('.folderTree__link.ft-is-folder[data-id="' + elId + '"]');
                if (parent) {
                  parent.click();
                }
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
  }

  triggerDblClick(e) {
    const el = e.target;
    const _this = this;

    if (el) {
      const li = el.closest('.folderView__li');
      if (!li) {
        return;
      }
      const elId = li.getAttribute('data-id');

      if (!elId) {
        return;
      }

      if (el.classList.contains('js-folderView-folder')) {
        _this.container.querySelector('.folderTree__link[data-id="' + elId + '"]').click();
      } else if (el.classList.contains('js-folderView-file')) {
        el.querySelector('.fv-is-play').click();
      }
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
}

export default FolderView;
