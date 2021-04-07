import Config from '../config/config';
const axios = require('axios');

class CreateItem {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');

    this.initDropdown();
  }

  getApiUrl(action, id, params) {
    let url = `${Config.apiUrl}lms/lo/${action}`;
    if (id) {
      url += `&id=${id}`;
    }
    if (!params) {
      params = {};
    }
    params.type = this.type;
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

  initDropdown() {
    const dropdown = this.container.querySelector('#dropdownMenu_' + this.type);
    const types = dropdown.querySelectorAll('.itemType');
    const treeLinks = this.container.querySelectorAll('.folderTree__li');

    if (types) {
      types.forEach(type => {
        type.addEventListener('click', (e) => { this.clickOnType(e, this.container, this.type) });
      });
    }
    if (treeLinks) {
      treeLinks.forEach(l => {
        l.addEventListener('click', (e) => { this.clickOnFolder(e) });
      });
    }
    const createFolderForm = this.container.querySelector('.createFolderForm');
    const createFolderBtn = createFolderForm.querySelector('.createFolder__btn');
    createFolderBtn.addEventListener('click', (e) => { this.createNewFolder(e, this.type, this.container, createFolderForm) });
  }

  clickOnFolder(event) {
    const el = event.target;
    const elId = el.getAttribute('data-id');
    this.selectedNodeId = elId;
    event.preventDefault();
  }

  createNewFolder(event, type, container, createFolderForm) {
    const text = createFolderForm.querySelector('.createFolder__input').value;
    const authentic_request = createFolderForm.querySelector('input[name=authentic_request]').value;
    const dropdownBtn = container.querySelector('#dropdownMenuBtn_' + type);
    const dropdown = container.querySelector('#dropdownMenu_' + type);
    const selectedNodeId = this.selectedNodeId ? this.selectedNodeId : 0;

    const params = {
      folderName: text,
      selectedNode: selectedNodeId,
      type,
      authentic_request,
    }
    const apiUrl = this.getApiUrl('createFolder', null, params);

    axios.get(apiUrl).then((response) => {
      if (response.data >= 0) {
        dropdownBtn.classList.remove('hidden');
        dropdown.classList.remove('hidden');
        createFolderForm.classList.add('hidden');
        createFolderForm.querySelector('.createFolder__input').value = '';

        // Refresh tree of parent node
        container.querySelector('.folderTree__link.ft-is-folder[data-id="' + selectedNodeId + '"]').click();
      } else {
        console.log(response.data, 'error');
      }
    }).catch( (error) => {
      console.log(error)
    });

    event.preventDefault();
  }

  clickOnType(event, container, type) {
    const el = event.target;
    const dropdownBtn = container.querySelector('#dropdownMenuBtn_' + type);
    const dropdown = container.querySelector('#dropdownMenu_' + type);
    const createFolderForm = container.querySelector('.createFolderForm');

    if (el) {
      const type = el.getAttribute('data-id');
      if (type === 'folder') {
        dropdownBtn.classList.add('hidden');
        dropdown.classList.add('hidden');
        createFolderForm.classList.remove('hidden');
      } else {
        /*
          http://formalms.local/appLms/index.php?modname=storage&op=display&homerepo_createLOSel=1

          radiolo: glossary
          treeview_selected_homerepo: 24
          treeview_idplayitem_homerepo: 0
          treeview_state_homerepo: a:1:{i:0;a:5:{i:24;a:3:{i:27;i:27;i:28;i:28;i:29;i:29;}i:34;i:34;i:25;i:25;i:26;i:26;i:33;i:33;}}
          homerepo[REPO_ID_SELECTIONSTATE]: %5B%5D
          homerepo_createLOSel: Nuovo
        */
      }
      event.preventDefault();
    }

  }

}

export default CreateItem
