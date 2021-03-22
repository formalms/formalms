class FolderView {

  constructor() {
    document.addEventListener('click', this.toggleSelectEl);
  }

  toggleSelectEl(event) {
    const el = event.target;
    const els = document.querySelectorAll('.folderView__li');

    if (el && (el.classList.contains('js-fv-open-actions'))) {
      const currentId = el.id;

      els.forEach(el => {
        const id = el.id;
        if (currentId !== id) {
          el.classList.remove('fv-is-selected');
        }
      });

      el.closest('.folderView__li').classList.toggle('fv-is-selected');
    }

  }
}

export default FolderView;
