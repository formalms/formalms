const axios = require('axios');

class CopyItem {


   constructor(baseApiUrl) {
      const _this = this;
      _this.baseApiUrl = baseApiUrl;

      document.addEventListener('click', this.openOverlay);
      document.querySelector('.js-fv-close-overlay').addEventListener('click', () => {
         _this.closeOverlay();
      });
      document.querySelector('.folderView__copyOverlay').addEventListener('click', (e) => {
         if(e.target.classList.contains('folderView__copyOverlay')) {
            const copyCloseButton = document.querySelector('.folderView__copyOverlay__close');
            if (copyCloseButton.style.pointerEvents !== 'none') {
               _this.closeOverlay();
            }
          }
      });
      document.addEventListener('keydown', (e) => {
         if (e.key === 'Esc' || e.key === 'Escape') {
            const copyCloseButton = document.querySelector('.folderView__copyOverlay__close');
            if (copyCloseButton.style.pointerEvents !== 'none') {
               _this.closeOverlay();
            }
         }
      });
      document.querySelectorAll('.js-fv-copy-target').forEach(el => {
         el.addEventListener('click', () => {
            _this.toggleBlockCopyClick(el);
            const targetType = el.getAttribute('data-type');
            _this.copyElement(targetType);
         })
      })
   }

   setCurrentType(type){
      this.currentType = type;
   }

   openOverlay(event) {
      const el = event.target;
      if (el.classList.contains('js-fv-open-overlay')) {
         document.querySelector('.folderView__copyOverlay').classList.add('is-shown');
      }
   }

   closeOverlay() {
      const els = document.querySelectorAll('.is-ready-for-copy');
      if (els) {
         els.forEach((el) => {
            el.classList.remove('is-ready-for-copy');
         });
      }
      document.querySelector('.folderView__copyOverlay').classList.remove('is-shown');
   }

   toggleBlockCopyClick(el) {
      el = typeof el !== 'undefined' ? el : document.querySelector('.folderView__copyOverlay .folderView__label > img').closest('.folderView__li');
      const copyParent = document.querySelector('.folderView__copyOverlay');
      const copyCloseButton = copyParent.querySelector('.folderView__copyOverlay__close');
      const copiesTo = copyParent.querySelectorAll('.js-fv-copy-target');
      if (copyCloseButton.style.pointerEvents == 'none') { // unblock
         copyCloseButton.style.pointerEvents = 'auto';
         copiesTo.forEach((copyTo) => {
            copyTo.style.pointerEvents = 'auto';
         });
         const copyLoadingGif = el.querySelector('.folderView__label').getElementsByTagName('img')[0];
         copyLoadingGif.parentNode.removeChild(copyLoadingGif);
      } else {
         copyCloseButton.style.pointerEvents = 'none';
         copiesTo.forEach((copyTo) => {
            copyTo.style.pointerEvents = 'none';
         });
         el.querySelector('.folderView__label').appendChild(document.createElement('img')).src = `${window.frontend.config.url.template}/images/standard/loading_snake.gif`;
      }
   }

   copyElement(newtype) {
      const _this = this;
      let ids = [];

      document.querySelectorAll('.is-ready-for-copy').forEach((item) => {
         ids.push(item.getAttribute('data-id'));
      });
      axios.get(_this.getApiUrl('copy', { ids: ids, newtype, type: this.currentType })).then((response) => {
         const data = response.data;
         _this.toggleBlockCopyClick();
         if (data && data.success === false) {
            _this.closeOverlay();
            const message = data.message || 'Copy operation failed';
            alert(message);
            return;
         }
         const container = document.querySelector('*[data-container=' + newtype + ']');
         _this.closeOverlay();
         document.querySelector('.tab-link[data-type="' + newtype + '"]').click();
         container.querySelector('.ft-is-root').click();
      }).catch( (error) => {
         _this.toggleBlockCopyClick();
         _this.closeOverlay();
         console.log(error);
         alert('Copy operation failed');
      });
   }

   getApiUrl(action, params) {
      let url = `${this.baseApiUrl}/${action}`;
      if (!params) {
        params = {};
      }
      url += '&' + new URLSearchParams(params).toString();
  
      return url;
   }
}

export default CopyItem;
