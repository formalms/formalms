import Config from '../config/config';
const axios = require('axios');

class CopyItem {

   constructor() {
      document.addEventListener('click', this.openOverlay);
      document.querySelector('.js-fv-close-overlay').addEventListener('click', () => {
         this.closeOverlay();
      });
      document.addEventListener('keydown', () => {
         if (event.key === 'Escape') {
            this.closeOverlay();
         }
      });
      document.querySelectorAll('.js-fv-copy-target').forEach(el => {
         el.addEventListener('click', () => {
            const targetType = el.getAttribute('data-type');
            this.copyElement(targetType);
         })
      })

   }

   openOverlay(event) {
      const el = event.target;
      if (el.classList.contains('js-fv-open-overlay')) {
         document.querySelector('.folderView__copyOverlay').classList.add('is-shown');
         el.closest('.folderView__li').classList.add('is-ready-for-copy');
      }
   }

   closeOverlay() {
      document.querySelector('.folderView__copyOverlay').classList.remove('is-shown');
      document.querySelector('.folderView__li.is-ready-for-copy').classList.remove('is-ready-for-copy');
   }

   copyElement(newtype) {
      const currentElementId = document.querySelector('.is-ready-for-copy').getAttribute('data-id');
      const type = window.type

      const copyLoData = getApiUrl('copy', currentElementId, { newtype, type });
      axios.get(copyLoData).then(() => {
         const container = document.querySelector('*[data-container=' + newtype + ']');

         this.closeOverlay();
         document.querySelector('.tab-link[data-type="' + newtype + '"]').click();
         container.querySelector('.ft-is-root').click();
      }).catch( (error) => {
         console.log(error);
      });
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

export default CopyItem;
