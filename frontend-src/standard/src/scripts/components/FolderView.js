class FolderView {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');
    this.container.addEventListener('click', this.toggleSelectEl);
    this.container.addEventListener('click', this.triggerClick);
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

  triggerClick(event) {
    const el = event.target;

    if (el) {
      const id = el.getAttribute('data-id');
      if (el.classList.contains('js-folderView-folder')) {
        this.querySelector('.js-folder-tree').querySelector('.folderTree__link[data-id="' + id + '"]').click();
      } else if (el.classList.contains('js-folderView-file')) {
        el.querySelector('.fv-is-play').click();
      }
    }
  }
}

export default FolderView;
