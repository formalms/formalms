//import Lang from '../helpers/Lang';
import dt from'datatables.net';

/**
 * FormaTable
 */
 class FormaTable {

    constructor(idOrClassOrElement = null, options =  {}) {
     
        // Properties
        this.Name = 'FormaTable';

         // Init
         if(!idOrClassOrElement) {
          this.Error(`constructor() -> undefined target reference ${idOrClassOrElement}`);
        } else {
          this.Element = typeof idOrClassOrElement === 'object' ? idOrClassOrElement : document.querySelector(idOrClassOrElement);
     
        }
        this.DataTable = new dt(idOrClassOrElement,options);
      }

 }

 /**
 * Javascript plugin FormaFileUploader
 */
Element.prototype.FormaTable = function(options) {
    new FormaTable(this, options);
  }
  
module.exports = FormaTable;