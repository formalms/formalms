var Twig = require('twig');
import Lang from './Lang';
const axios = require('axios');

Twig.extendFunction('Lang_translate', function(translationKey, moduleKey, paramsObj) {
    return Lang.Translation(translationKey, moduleKey, paramsObj);
});
  
Twig.extendFunction('Utils_getImage', function(path, iconName, defaultIcon) {
    try {
        var http = new XMLHttpRequest();
        const image_url = `${window.frontend.config.url.template}/static/images/${path}/${iconName}`;
        http.open('HEAD', image_url, false);
        http.send();
        return http.status === 404 ? `${window.frontend.config.url.template}/static/images/${path}/${defaultIcon}` : image_url;
    } catch (e) {
        return `${window.frontend.config.url.template}/static/images/${path}/${defaultIcon}`;
    }
});

Twig.extendFunction('arrayContains', (array, idString, value) => {

    const find = (array, idString, value) => {
        const spliced = idString.split('_');
        const found = array.filter((item) => spliced.indexOf(item) !== -1 && spliced.indexOf(item)+1 === Number(value) ? true : false );
        return found.length ? true : false;
    }

    if(Array.isArray(value)) {
        let isFound = false;
        value.forEach((valueItem) => {
            if(!isFound)
                isFound = find(array, idString, valueItem.value);
        })
        return isFound;
    }
    return find(array, idString, value);
})
  
Twig.extendFunction('getId', (str, position) => {
    const spliced = str.split('_');
    if(position) {
      return spliced[position-1];
    }
})