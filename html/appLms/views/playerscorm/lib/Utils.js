(function() {
    if(typeof window.Object.values !== "function") {
        window.Object.values = function(obj) {
            var allowedTypes = ["[object String]", "[object Object]", "[object Array]", "[object Function]"];
            var objType = Object.prototype.toString.call(obj);
        
            if(obj === null || typeof obj === "undefined") {
                throw new TypeError("Cannot convert undefined or null to object");
            } else if(!~allowedTypes.indexOf(objType)) {
                return [];
            } else {
                // if ES6 is supported
                if (Object.keys) {
                    return Object.keys(obj).map(function (key) {
                        return obj[key];
                    });
                }
                
                var result = [];
                for (var prop in obj) {
                    if (obj.hasOwnProperty(prop)) {
                        result.push(obj[prop]);
                    }
                }
                
                return result;
            }
        };
    }
    if (!Array.prototype.includes) {
        Array.prototype.includes = function (searchElement /*, fromIndex*/) {
            'use strict';
            if (this == null) {
                throw new TypeError('Array.prototype.includes called on null or undefined');
            }

            var O = Object(this);
            var len = parseInt(O.length, 10) || 0;
            if (len === 0) {
                return false;
            }
            var n = parseInt(arguments[1], 10) || 0;
            var k;
            if (n >= 0) {
                k = n;
            } else {
                k = len + n;
                if (k < 0) { k = 0; }
            }
            var currentElement;
            while (k < len) {
                currentElement = O[k];
                if (searchElement === currentElement ||
                    (searchElement !== searchElement && currentElement !== currentElement)) { // NaN !== NaN
                    return true;
                }
                k++;
            }
            return false;
        };
    }
})();

function deepObjSetValue(obj, path, value) {
    var index = path.shift();
    if(path.length > 0) {
        if(!(index in obj)) {
            obj[index] = isNaN(path[0]) ? {} : [];
        }
        obj[index] = deepObjSetValue(obj[index], path, value);
    } else {
        obj[index] = value;
    }
    return obj;
}

function deepObjGetValue(obj, path) {
    for(var i = 0; i < path.length; i++) {
        if(obj[path[i]] === undefined) {
            return undefined;
        }
        obj = obj[path[i]];
    }
    return obj;
}

function parseQuery(queryString) {
    var query = {};
    var pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
    for(var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split('=');
        query[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
    }
    return query;
}

function buildQuery(obj) {
    var str = [];
    for(var property in obj)
        if(obj.hasOwnProperty(property) && obj[property] !== null && obj[property] !== undefined) {
            str.push(encodeURIComponent(property) + "=" + encodeURIComponent(obj[property]));
        }
    return '?' + str.join('&');
}

function getSiloAndCustomer() {
    let src = "";
    if (document.location.pathname.indexOf("/player") > -1) {
      src = document.location.pathname.substring(0, document.location.pathname.indexOf('/player')) || '';
    }
    return src;
}