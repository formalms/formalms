const axios = require('axios');

class CreateItem {

  constructor(baseApiUrl, type) {
    this.baseApiUrl = baseApiUrl;
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');

    this.initDropdown();
  }

  initDropdown() {
    const dropdown = this.container.querySelector('#dropdownMenu_' + this.type);
    const types = dropdown.querySelectorAll('.itemType');
    const treeLinks = this.container.querySelectorAll('.folderTree__li');
    const folderView = this.container.querySelectorAll('.folderView');

    if (types) {
      types.forEach(type => {
        type.addEventListener('click', this.clickOnType.bind(this));
      });
    }
    if (treeLinks) {
      treeLinks.forEach(l => {
        l.addEventListener('click', this.clickOnFolder.bind(this));
      });
    }
    if (folderView) {
      folderView.forEach(l => {
        l.addEventListener('dblclick', this.clickOnFolder.bind(this));
      });
    }
    const createFolderForm = this.container.querySelector('.createFolderForm');
    const createFolderBtn = createFolderForm.querySelector('.createFolder__btn');
    const cancelCreateFolderBtn = createFolderForm.querySelector('.cancelCreateFolder__btn');
    const folderInputText = createFolderForm.querySelector('.createFolder__input');
    
    createFolderBtn.addEventListener('click', (e) => { this.createNewFolder(e, createFolderForm) });
    cancelCreateFolderBtn.addEventListener('click', () => {
      const dropdownBtn = this.container.querySelector('#dropdownMenuBtn_' + this.type);
      dropdownBtn.classList.remove('hidden');
      dropdown.classList.remove('hidden');
      createFolderForm.classList.add('hidden');
      createFolderForm.querySelector('.createFolder__input').value = '';
    });

    folderInputText.addEventListener('keypress', (e) => {
      if (e.keyCode === 13) {
        this.createNewFolder(e, createFolderForm);
        return false;
      }
    });
  }

  clickOnFolder(event) {
    const el = event.target;
    const elId = el.getAttribute('data-id');
    if (elId >= 0) {
      this.selectedId = elId;
    }

    event.preventDefault();
  }

  createNewFolder(event, createFolderForm) {
    const _this = this;

    this.showErr();

    const text = createFolderForm.querySelector('.createFolder__input').value;
    
    const authentic_request = createFolderForm.querySelector('input[name=authentic_request]').value;
    const dropdownBtn = _this.container.querySelector('#dropdownMenuBtn_' + _this.type);
    const dropdown = _this.container.querySelector('#dropdownMenu_' + _this.type);

    const apiUrl = _this.getApiUrl('createFolder', {
      folderName: text,
      selectedNode: _this.selectedId ? _this.selectedId : 0,
      authentic_request,
    });

    axios.get(apiUrl).then((response) => {
      if (response) {
        dropdownBtn.classList.remove('hidden');
        dropdown.classList.remove('hidden');
        createFolderForm.classList.add('hidden');
        createFolderForm.querySelector('.createFolder__input').value = '';

        // dispatch createdItem event
        _this.container.dispatchEvent(new CustomEvent('createTreeItem', {
          detail: { selectedId: _this.selectedId, }
        }));
      }
    }).catch((error) => {
      _this.showErr(error.response.data.error);
    });

    event.preventDefault();
  }

  showErr(msg) {
    const _this = this;
    const err = _this.container.querySelector('.createFolder__input_err');
    if (msg) {
      err.innerHTML = msg;
      err.classList.remove('hidden');
    } else {
      err.classList.add('hidden');
    }
  }

  getNewLoUrl(type) {
    const _this = this;
    const selectedId = _this.selectedId ? _this.selectedId : 0;
    //const currentState = document.getElementById(`treeview_state_${_this.type}`).value;

    //return `index.php?modname=storage&op=display&${_this.type}_createLOSel=1&radiolo=${type}&treeview_selected_${_this.type}=${selectedId}&treeview_state_${_this.type}=${currentState}`;
    return `index.php?modname=storage&op=display&${_this.type}_createLOSel=1&radiolo=${type}&treeview_selected_${_this.type}=${selectedId}`;
  }

  clickOnType(event) {
    const _this = this;
    const el = event.target;
    const dropdownBtn = _this.container.querySelector('#dropdownMenuBtn_' + _this.type);
    const dropdown = _this.container.querySelector('#dropdownMenu_' + _this.type);
    const createFolderForm = _this.container.querySelector('.createFolderForm');

    if (el) {
      const type = el.getAttribute('data-id');
      if (type === 'folder') {
        dropdownBtn.classList.add('hidden');
        dropdown.classList.add('hidden');
        createFolderForm.classList.remove('hidden');
        // Focus on folder title input
        createFolderForm.querySelector('.createFolder__input').focus();
      } else {
        location.href = _this.getNewLoUrl(type);
      }
      event.preventDefault();
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

export default CreateItem
