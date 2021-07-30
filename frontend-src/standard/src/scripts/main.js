// device
require('@ciffi-js/device');
window.config = require('./modules/Config');
require('./pages/allpages');
var Twig = require('twig');


/**
 * Translations
 */
import Lang from './helpers/Lang';
/*
Twig.extendFilter('translate', function(value, args = null) {
  if(value && value.indexOf('.') !== -1) {
    const keys = value.split('.');
    return Lang.Translation(keys[1], keys[0], args[0]);
  }
});
*/
Twig.extendFunction('Lang_translate', function(translationKey, moduleKey, paramsObj) {
  return Lang.Translation(translationKey, moduleKey, paramsObj);
});



// router
// require('@ciffi-js/router').pushState(false);
