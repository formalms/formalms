class FolderView {

  constructor() {
    document.addEventListener('click', this.toggleSelectEl);
    document.addEventListener('click', this.triggerClick);
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
      const id = el.id;
      if (el.classList.contains('js-folderView-folder')) {
        document.querySelector('.js-folder-tree').querySelector('.folderTree__link[id="' + id + '"]').click();
      } else if (el.classList.contains('js-folderView-file')) {
        el.querySelector('.fv-is-play').click();
      }
    }

  }
}

export default FolderView;