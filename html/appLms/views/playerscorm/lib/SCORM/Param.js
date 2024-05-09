function SCORM_Param(api, param) {
    this.api        = api;
    this.param      = param.split('.');
    
    if(this.param[0] === 'adl') {
        this.error("402");
    }
}

SCORM_Param.log = function(log, label) {
    if(label !== undefined) {
        console.log("SCORM_Param", "-", label + ":", log);
    } else {
        console.log("SCORM_Param", "-", log);
    }
}

SCORM_Param.debug = function(debug, label) {
    if(label !== undefined) {
        console.debug("SCORM_Param", "-", label + ":", debug);
    } else {
        console.debug("SCORM_Param", "-", debug);
    }
}

SCORM_Param.prototype.error = function(error) {
    throw error;
}

SCORM_Param.prototype.model = function(param, required) {
    var model = deepObjGetValue(this.api.datamodel(), param);
    if(required && model === undefined) {
        this.error("");
    }
    return model;
}

SCORM_Param.prototype.get = function() {
    if(!this.param[0]) {
        this.error("301");
    }
    switch(this.param[this.param.length - 1]) {
        case '_count':      return this.getCount();
        case '_children':   return this.getChildren();
        case '_version':    return this.getVersion();
        default:            return this.getValue();
    }
}

SCORM_Param.prototype.getCount = function() {
    var param = this.param;
    param.pop();
    var model = this.model(param, true);
    if(model.index !== "yes") {
        this.error("301");
    }
    //check if is a runtime element that is reader before setted
    return this.api.value(param).length;
}

SCORM_Param.prototype.getChildren = function() {
    var model = this.model(this.param, true);
    return model.text;
}

SCORM_Param.prototype.getVersion = function() {
    var model = this.model(this.param, true);
    return model.text;
}

SCORM_Param.prototype.getValue = function() {
    var model = this.model(this.param, true);
    if(model.item !== "yes") {
        this.error("401");
    }
    if(!this.checkPermission(model.permission, 'read')) {
        this.error("405");
    }
    //check if is a runtime element that is reader before setted
    return this.api.value(this.param);
}

SCORM_Param.prototype.set = function(value) {
    if(!this.param[0]) {
        this.error("351"); // cambiare
    }
    switch(this.param[this.param.length - 1]) {
        case '_count':
        case '_children':
        case '_version': this.error(404);
        default: return this.setValue(value);
    }
}

SCORM_Param.prototype.setValue = function(value) {    
    var model = this.model(this.param, false);
    if(model === undefined) {
        // vari controlli....
        // cose...
    }
    if(model.item !== "yes") {
        this.error("401");
    }
    if(!this.checkPermission(model.permission, 'write')) {
        this.error("404");
    }
    var type = model.type.split(',');
    var type_ok = false;    
    for(var i = 0; i < type.length; i++) {
        try {
            if(this.api.typesHandler().validate(value, type[i], model.subtype)) {
                type_ok = true;
                break;
            }
        } catch(e) {
            // Already in debug.
        }
    }
    if(!type_ok) {
        this.error("406");
    }
    this.api.value(this.param, value);
    // set isset
}

SCORM_Param.prototype.checkPermission = function(permission, access) {
    switch(permission) {
        case 'readwrite':   return true;
        case 'readonly':    return access === 'read';
        case 'writeonly':   return access === 'write';
    }
    return false;
}
