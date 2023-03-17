var Twig = require('twig');
import Lang from './Lang';


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

Twig.extendFunction('arrayContains', (array, idString, value) => {
    const spliced = idString.split('_');
    const found = array.filter((item) => spliced.indexOf(item) !== -1 && spliced.indexOf(item)+1 === Number(value) ? true : false );
    return found.length ? true : false;
})
  
Twig.extendFunction('getId', (str, position) => {
    const spliced = str.split('_');
    if(position) {
      return spliced[position-1];
    }
})