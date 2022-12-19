import 'regenerator-runtime/runtime'
const axios = require('axios');

/**
 * Constants
 */
const FOLDER_TREE_CLASS = 'folder-tree';


/**
 * FolderTree base class
 */
class FolderTreeBase {

  constructor(baseApiUrl, controller, type, endpoint = 'getfoldertree', options = {}) {

    // Options
    this.options = {
      dragAndDrop: true,
      sortable: true,
      activeStatus: true
    }

    this.options = Object.assign(this.options, options);

    this.onLoad = null;
    this.baseApiUrl = baseApiUrl;
    this.controller = controller;
    this.endpoint = endpoint;
    this.type = type;
    this.openedIds = [];
    this.Tree = null;
    this.selectedId = null;
    this.container = document.querySelector('*[data-container=' + this.type + ']');
    
    if(this.container) {
        const self = this;
        this.container.addEventListener('createTreeItem', (e) => {
            self.refresh(e.detail.selectedId);
        });
        this.container.addEventListener('deleteTreeItem', () => {
            self.refresh();
        });
        this.container.addEventListener('reorderData', () => {
            self.refresh();
        });
        this.container.addEventListener('click', (event) => {
            self.clickOnFolder(event);
        });

        if(this.options.dragAndDrop) {
          this.initDragDrop();
        }

        this.getStatus();// From localStorage
        this.getData(this.getApiUrl(this.endpoint));
    }
  }

  render(targetQuery = FOLDER_TREE_CLASS) {

    const mock = [{
      id:1, 
      name: 'Ok', 
      children: [
        {
          id: 2,
          name: 'Sub ok', 
          actions: [{
            type: 'radioButton',
            options: [{
                label: 'No',
                name: 'name',
                value: 0
              },
              {
                label: 'Si',
                name: 'name',
                value: 1
              },
              {
                label: 'Discendenti',
                name: 'name',
                value: 2
              }
            ]
          }],
          children: []}
      ]}];

    // mock
    const tree = this.Tree({ data: mock, extra: {form: this.hasForm} });
    const targetDom = document.querySelector(`.${targetQuery}`);
    targetDom.innerHTML = tree;
    return this;
  }

  getBaseApiUrl(action) {
    let url = `${window.frontend.config.url.appLms}/index.php?r=${action}`;
    if (action) {
        url += `/${action}`;
    }

    return url;
  }


  clickOnFolder(event) {
    const _this = this;

    // Try to get clicked dir
    let el = event.target.closest('.folderTree__link');
    if (!el) {
      const li = event.target.closest('.folderTree__li');
      if (li) {
        el = li.querySelector('.folderTree__link');
      }
    }

    if (!el) {
      return;
    }

    const elId = el.getAttribute('data-id');

    // dispatch openDir event
    _this.container.dispatchEvent(new CustomEvent('openDir', {
      detail: { selectedId: elId, }
    }));

    const isOpen = el.classList.contains('ft-is-folderOpen');
    const clickOnArrow = event.target.classList.contains('arrow');

    // Remove all selections
    const els = _this.container.querySelectorAll('.folderTree__link');
    if (els) {
      els.forEach(el => {
        el.classList.remove('ft-is-selected');
        if(this.options.activeStatus) {
          el.parentNode.classList.remove('selected');
        }
      });
    }

    // Hide/Show children of clicked dir
    let childrenUl = el.parentNode.querySelector('.folderTree__ul');
    if(!childrenUl) { // previous twig has only one parent, new version 2
      childrenUl = el.parentNode.parentNode.querySelector('.folderTree__ul');
    }

    if (isOpen) {
      if (clickOnArrow) {
        event.target.classList.remove('opened');
        el.classList.remove('ft-is-folderOpen');

        if (childrenUl) {
          childrenUl.classList.add('hidden');
        }
      } else {
          el.classList.add('ft-is-selected');
          if(this.options.activeStatus) {
            el.parentNode.classList.add('selected');
          }
      }
    } else {
      if (clickOnArrow) {
        event.target.classList.add('opened');
        el.classList.add('ft-is-folderOpen');
      } else {
        const li = event.target.closest('.folderTree__li');
        const arrow = li.querySelector('.arrow');
        if(this.options.activeStatus) {
          el.parentNode.classList.add('selected');
        }
        el.classList.add('ft-is-selected');
        if (arrow) {
          arrow.classList.add('opened');
          el.classList.add('ft-is-folderOpen');
        }
      }
      if (childrenUl) {
        childrenUl.classList.remove('hidden');
      }
    }

    this.setOpenedDirs();
    this.setSelectedDir();
  }

  storeStatus() {
    localStorage.setItem('openedIds', this.openedIds);
    localStorage.setItem('selectedId', this.selectedId);
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

  getApiUrl(action, params) {
    let url = `${this.baseApiUrl}/${action}`;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();
    return url;
  }

  async reorderData(endpoint) {
    try {
      await axios.get(endpoint).then(() => {
        this.refresh();
      }).catch( (error) => {
        console.log(error);
      });
    } catch (e) {
      console.log(e);
    }
  }

  getStatus() {
    this.openedIds = localStorage.getItem('openedIds') ? localStorage.getItem('openedIds').split(',') : [];
    this.selectedId = localStorage.getItem('selectedId');
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
        // this.selectItems();
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

  onDrop(event) {
    const target = event.target;
    target.classList.remove('fv-is-dropped');
    event.preventDefault();
    if (this.currentElsIds) {
      const parentId = parseInt(target.getAttribute('data-id'));
      if (!this.currentElsIds.includes(parentId) && target.classList.contains('is-dropzone')) {
        // this.reorderData(this.getApiUrl('move', { ids: this.currentElsIds, newParent: parentId }));
      }
    }
  }

  initDragDrop() {
    this.container.addEventListener('dragstart', this.onDragStart.bind(this));
    this.container.addEventListener('dragover', this.onDragOver.bind(this));
    this.container.addEventListener('dragleave', this.onDragLeave.bind(this));
    this.container.addEventListener('drop', this.onDrop.bind(this));
  }

  async getData(endpoint) {
    const _this = this;
    try {
      await axios.get(endpoint).then((response) => {

        const tree = this.Tree({ data: response.data.data[0].children, extra: {options: this.options, form: this.hasForm} });

        const treeView = _this.container.querySelector('.folderTree__ul .folderTree__ul');
        treeView.innerHTML = tree;

        // Disable draggable in student-area
        if(document.body.classList.contains('student-area')) {
          treeView.querySelectorAll('li').forEach(b=>b.removeAttribute('draggable'));
        }

        if (_this.openedIds) {
          _this.openedIds.forEach((id) => {
            if (id != _this.selectedId) {
              _this._setAsOpen.call(_this, id);
            }
          });
        }
        if (_this.selectedId > 0) {
          let dir = _this.container.querySelector('.folderTree__link[data-id="' + _this.selectedId + '"]');
          if (dir) {
            dir.classList.add('ft-is-selected');
            if(_this.options.activeStatus) {
              dir.parentNode.classList.add('selected');
            }
            dir.click();
          }
        }
        if (!_this.container.querySelector('.js-disable-context-menu')) {
          // dispatch refreshContextMenu event
          document.dispatchEvent(new CustomEvent('refreshContextMenu', { detail: { controller: _this.controller }}));
        }
        _this.container.dispatchEvent(new CustomEvent('folderTreeIsReady', {
          detail: { selectedId: _this.getSelectedId(), }
        }));
        if(_this.onLoad) {
          _this.onLoad();
        }
      }).catch((error) => {
        console.log(error)
      });
    } catch (e) {
      console.log(e);
    }
  }

  getSelectedId() {
    return this.selectedId;
  }

  refresh(parentId) {
    if (parentId >= 0) {
        this.selectedId = parentId;
    }
    this.getData(this.getApiUrl('getfoldertree'));
    const self = this;
    // dispatch refreshTree event
    this.container.dispatchEvent(new CustomEvent('refreshTree', {
      detail: { selectedId: self.selectedId, }
    }));
  }

  _setAsOpen(id) {
    let subtree = this.container.querySelector('.folderTree__li[data-id="' + id + '"] .folderTree__ul.hidden');
    let directoryButton = this.container.querySelector('.folderTree__li[data-id="' + id + '"] button.folderTree__link');
    directoryButton.classList.add('ft-is-folderOpen');
    let arrowSpan = this.container.querySelector('.folderTree__li[data-id="' + id + '"] span.arrow');
    arrowSpan.classList.add('opened');
    if (subtree) {
      subtree.classList.remove('hidden');
    }
  }

}

export default FolderTreeBase
