/**
 * Base class 
 * for javascript plugins
 */

class FormaPlugin {

  constructor() {
    this.Name = '';
    this._Polyfills();
  }

  FormatBytes(bytes, decimals = 2) {
    if (bytes === 0) {
      return '0 Bytes';
    }
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }

  CreateElementFromHTML(htmlString) {
    var div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild; 
  }

  Error(message = '') {
    throw `${this.Name}: ${message}`
  }

  _Polyfills() {
    // object.watch
    if (!Object.prototype.watch) {
      Object.defineProperty(Object.prototype, 'watch', {
        enumerable: false
        , configurable: true
        , writable: false
        , value: function (prop, handler) {
          var
          oldval = this[prop]
          , newval = oldval
          , getter = function () {
            return newval;
          }
          , setter = function (val) {
            oldval = newval;
            return newval = handler.call(this, prop, oldval, val);
          };
          if (delete this[prop]) { // can't watch constants
            Object.defineProperty(this, prop, {
              get: getter
              , set: setter
              , enumerable: true
              , configurable: true
            });
          }
        }
      });
    }
    // object.unwatch
    if (!Object.prototype.unwatch) {
      Object.defineProperty(Object.prototype, 'unwatch', {
        enumerable: false
        , configurable: true
        , writable: false
        , value: function (prop) {
          var val = this[prop];
          delete this[prop]; // remove accessors
          this[prop] = val;
        }
      });
    }
  }

}

module.exports = FormaPlugin;