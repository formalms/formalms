(function() {
    if(typeof window.CustomEvent === "function") return false;
    function CustomEvent(event, params) {
        params = params || { bubbles: false, cancelable: false, detail: null };
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }
    window.CustomEvent = CustomEvent;
})();

function triggerEvent(name, obj) {
    obj = obj || {};
    var event = new CustomEvent(name, obj);
    document.dispatchEvent(event);
}

function deepObjSetValue(obj, path, value) {
    var index = path.shift();
    if(path.length > 0) {
        obj[index] = deepObjSetValue(obj[index], path, value);
    } else {
        obj[index] = value;
    }
    return obj;
}

function deepObjGetValue(obj, path) {
    for(var i = 0; i < path.length; i++) {
        obj = obj[path[i]];
    }
    return obj;
}