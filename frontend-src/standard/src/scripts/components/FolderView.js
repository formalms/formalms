class FolderView {

  constructor() {
    this.config = {
      el: '.js-fv-select-el'
    }

    this.toggleSelectEl();
  }

  toggleSelectEl() {
    const els = document.querySelectorAll(this.config.el);

    if (els) {
      els.forEach(el => {
        el.addEventListener('click', (e) => {
          const currentId = e.currentTarget.id;

          els.forEach(el => {
            const id = el.id;
            if (currentId !== id) {
              el.classList.remove('fv-is-selected');
            }
          });

          el.classList.toggle('fv-is-selected');
        })
      });
    }

  }
}

export default FolderView;
