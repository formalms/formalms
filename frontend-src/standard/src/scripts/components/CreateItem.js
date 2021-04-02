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

    if (types) {
      types.forEach(type => {
        type.addEventListener('click', (e) => { this.clickOnType(e, this.container, this.type) });
      });
    }
    const createFolderForm = this.container.querySelector('.createFolderForm');
    const createFolderBtn = createFolderForm.querySelector('.createFolder__btn');
    createFolderBtn.addEventListener('click', (e) => { this.createNewFolder(e, this.type, this.container, createFolderForm) });
  }

  createNewFolder(event, type, container, createFolderForm) {
    const text = createFolderForm.querySelector('.createFolder__input').value;
    const authentic_request = createFolderForm.querySelector('input[name=authentic_request]').value;
    const dropdownBtn = container.querySelector('#dropdownMenuBtn_' + type);
    const dropdown = container.querySelector('#dropdownMenu_' + type);

    const params = {
      folderName: text,
      selectedNode: 0, // Passare l'ID della cartella selezionata!!!
      type,
      authentic_request,
    }
    const apiUrl = this.getApiUrl('createFolder', null, params);

    axios.get(apiUrl).then((response) => {
      console.log(response);
      if (response.data > 0) {
        dropdownBtn.classList.remove('hidden');
        dropdown.classList.remove('hidden');
        createFolderForm.classList.add('hidden');
        createFolderForm.querySelector('.createFolder__input').value = '';

        // Refreshare alberatura o appendere nuova cartella
      } else {
        console.log(response.data);
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
      }
      event.preventDefault();
    }
    
  }

}

export default CreateItem
