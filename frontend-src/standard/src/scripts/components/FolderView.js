import Config from '../config/config';
const axios = require('axios');

class FolderView {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');
    this.container.addEventListener('click', this.toggleSelectEl);
    this.container.addEventListener('click', (e) => { this.triggerClick(e, this.container, this.type) });
  }



  toggleSelectEl(event) {
    const el = event.target;

    if (el && (el.classList.contains('js-fv-open-actions'))) {
      el.closest('.folderView__li').classList.add('fv-is-selected');
    }

    if (el && (el.classList.contains('js-fv-close-actions'))) {
      el.closest('.folderView__li').classList.remove('fv-is-selected');
    }
  }

  triggerClick(event, container, type) {
    const el = event.target;

    if (el) {
      if (el.classList.contains('fv-is-delete')) {
        event.preventDefault();
        
        const li = el.closest('.folderView__li');
        const id = li.getAttribute('data-id');

        if (confirm('Sei sicuro di voler eliminare questo elemento?')) {
          const deleteLoData = getApiUrl('delete', id, { type });
          axios.get(deleteLoData).then(() => {
            li.remove();
            const item = container.querySelector('.folderTree__li[data-id="' + id + '"]');
            if (item) {
              item.remove();
            }
          }).catch((error) => {
            console.log(error);
          });
        }
      } else if (el.classList.contains('js-folderView-folder')) {
        const id = el.getAttribute('data-id');
        container.querySelector('.folderTree__link[data-id="' + id + '"]').click();
      } else if (el.classList.contains('js-folderView-file')) {
        el.querySelector('.fv-is-play').click();
      }
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

export default FolderView;
