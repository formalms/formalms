const axios = require('axios');

class CopyItem {

   constructor(baseApiUrl) {
      const _this = this;
      _this.baseApiUrl = baseApiUrl;

      document.addEventListener('click', this.openOverlay);
      document.querySelector('.js-fv-close-overlay').addEventListener('click', () => {
         this.closeOverlay();
      });
      document.addEventListener('keydown', () => {
         if (event.key === 'Escape') {
            _this.closeOverlay();
         }
      });
      document.querySelectorAll('.js-fv-copy-target').forEach(el => {
         el.addEventListener('click', () => {
            const targetType = el.getAttribute('data-type');
            _this.copyElement(targetType);
         })
      })
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

   copyElement(newtype) {
      const _this = this;
      let ids = [];

      document.querySelectorAll('.is-ready-for-copy').forEach((item) => {
         ids.push(item.getAttribute('data-id'));
      });

      axios.get(_this.getApiUrl('copy', { ids: ids, newtype, type: window.type })).then(() => {
         const container = document.querySelector('*[data-container=' + newtype + ']');

         _this.closeOverlay();
         document.querySelector('.tab-link[data-type="' + newtype + '"]').click();
         container.querySelector('.ft-is-root').click();
      }).catch( (error) => {
         console.log(error);
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
