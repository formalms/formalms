class FolderView {

  constructor() {
    document.addEventListener('click', this.toggleSelectEl);
  }

  toggleSelectEl(event) {
    const el = event.target.closest('.js-fv-select-el');
    const els = document.querySelectorAll('.js-fv-select-el');

    if (el) {
      const currentId = el.id;

      els.forEach(el => {
        const id = el.id;
        if (currentId !== id) {
          el.querySelector('.folderView__el').classList.remove('fv-is-selected');
        }
      });

      el.querySelector('.folderView__el').classList.toggle('fv-is-selected');
    }

  }
}

export default FolderView;
