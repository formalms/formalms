i18n = {};

i18n.defaultLanguage = "en";

i18n.init = function() {
    try {
        r = RESTClient.get("./lang.json", null, false);
    } catch(e) {
        throw e;
    }
    this.languages = r.response;
    let user_language = (window.navigator.languages ? window.navigator.languages[0] : (window.navigator.language || window.navigator.userLanguage));
    if(!this.languages[user_language]) {
        user_language = user_language.substring(0, 2);
        if(!this.languages[user_language]) {
            user_language = this.defaultLanguage;
        }
    }
    document.documentElement.lang = user_language;
}

i18n.translate = function(key) {
    if(this.languages[document.documentElement.lang] && this.languages[document.documentElement.lang][key]) {
        return this.languages[document.documentElement.lang][key];
    } else if(this.languages[document.documentElement.lang.substring(0, 2)] && this.languages[document.documentElement.lang.substring(0, 2)][key]) {
        return this.languages[document.documentElement.lang.substring(0, 2)][key];
    } else if(this.languages[this.defaultLanguage] && this.languages[this.defaultLanguage][key]) {
        return this.languages[this.defaultLanguage][key];
    } else {
        return key;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    i18n.init();
});
