// device
require('@ciffi-js/device');
require('./pages/allpages');
var Twig = require('twigjs');


/**
 * Translations
 */
import Lang from './helpers/Lang';

Twig.extendFunction('Lang_translate', function(translationKey, moduleKey, paramsObj) {
  return Lang.Translation(translationKey, moduleKey, paramsObj);
});

Twig.extendFunction('Utils_getImage', function(path, iconName, defaultIcon) {
  var http = new XMLHttpRequest();
  const image_url = `${window.frontend.config.url.template}/static/images/${path}/${iconName}`;
  http.open('HEAD', image_url, false);
  http.send();
  return http.status === 404 ? `${window.frontend.config.url.template}/static/images/${path}/${defaultIcon}` : image_url;
});





// router
// require('@ciffi-js/router').pushState(false);
