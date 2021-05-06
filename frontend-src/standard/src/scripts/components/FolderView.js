import Config from '../config/config';
const axios = require('axios');

class FolderView {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');
    this.container.addEventListener('click', (e) => { this.toggleSelectEl(e); });
    this.container.addEventListener('dblclick', (e) => { this.triggerClick(e); });
    this.emptySelectedItems();
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

  toggleSelectEl(event) {
    const el = event.target;
    this.toggleSelectedItem(el, el.getAttribute('data-id'), (event.ctrlKey || event.metaKey));
  }

  triggerClick(event) {
    const el = event.target;
    const container = this.getContainer();
    const type = this.getType();
    event.preventDefault();

    if (el) {
      const li = el.closest('.folderView__li');
      if (!li) {
        return;
      }
      const elId = li.getAttribute('data-id');

      if (!elId) {
        return;
      }

      if (el.classList.contains('fv-is-delete')) {
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
      } else if (el.classList.contains('js-folderView-folder')) {
        container.querySelector('.folderTree__link[data-id="' + elId + '"]').click();
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
