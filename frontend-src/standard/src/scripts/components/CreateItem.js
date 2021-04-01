import Config from '../config/config';
// const axios = require('axios');

class CreateItem {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');

    this.initDropdown();
  }

  getApiUrl(action, id, params) {
    let url = `${Config.apiUrl}lms/lo/${action}&id=${id}`;
    if (!params) {
      params = {};
    }
    params.type = this.type;
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

  initDropdown() {
    const el = this.container.querySelector('#dropdownMenu_' + this.type);
    console.log(el, 'EL');
    
    /*if (el) {
        const elId = el.getAttribute('id');
        const getLoData = this.getApiUrl('get', elId);
        axios.get(getLoData).then( (response) => {
          const childView = DropdownItem(response.data); ?
        }).catch( (error) => {
          console.log(error)
        });
      }
    }*/

  }
}

export default CreateItem
