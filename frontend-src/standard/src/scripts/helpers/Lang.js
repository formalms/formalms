
/**
 * Lang
 * Translatio helper
 */

class Lang {

  static Translation(translationKey, moduleKey, varsObj = null) {
    if(window.frontend.config.lang) {
      let translation = window.frontend.config.lang.translations[moduleKey] ? 
        window.frontend.config.lang.translations[moduleKey][translationKey] ? 
          window.frontend.config.lang.translations[moduleKey][translationKey] : 
            `${moduleKey}.${translationKey}` 
        : `${moduleKey}.${translationKey}`;
        if(varsObj) {
          Object.keys(varsObj).forEach(key => {
            translation = translation.replace(key, varsObj[key]);
          });
        }
        return translation;
    } else {
      return '-Missing translations-';
    }
  }

}

export default Lang;