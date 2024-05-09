SCORM_API.factory = function(player) {
    switch(player.SCORM().version()) {
        case SCORM_API_12.version(): return new SCORM_API_12(player);
        case SCORM_API_13.version(): return new SCORM_API_13(player);
        default: throw new Error("Unsupported scorm version.");
    }
}

SCORM_API.errors = function(code) {
    let errors = {
        "0":    "No error",
        "101":  "General exception",
        "102":  "General initialization failure",
        "103":  "Already initialized",
        "104":  "Content instance terminated",
        "111":  "General termination failure",
        "112":  "Termination before initialization",
        "113":  "Termination after termination",
        "122":  "Retrieve data before initialization",
        "123":  "Retrieve data after termination",
        "132":  "Store data before initialization",
        "133":  "Store data after termination",
        "142":  "Commit before initialization",
        "143":  "Commit after termination",
        "201":  "General argument error",
        "301":  "General get error",
        "351":  "General set error",
        "391":  "General commit error",
        "401":  "Undefined data model element",
        "402":  "Unimplemented data model element",
        "403":  "Data model element value not initialized",
        "404":  "Data model element is read only",
        "405":  "Data model element is write only",
        "406":  "Data model element type mismatch",
        "407":  "Data model element value out of range",
        "408":  "Data model dependency not established"
    };
    if(code !== undefined) {
        return errors[code];
    } else {
        return errors;
    }
}

SCORM_API.log = function(log, label) {
    if(label !== undefined) {
        console.log("SCORM_API", "-", label + ":", log);
    } else {
        console.log("SCORM_API", "-", log);
    }
}

SCORM_API.debug = function(debug, label) {
    if(label !== undefined) {
        console.debug("SCORM_API", "-", label + ":", debug);
    } else {
        console.debug("SCORM_API", "-", debug);
    }
}

SCORM_API.triggerEvent = function(name, obj) {
    triggerEvent("SCORM_API." + name, obj);
}

function SCORM_API(player) {
    this.player = player;
    // ------------------------------------------------
    this.error("0");
	this.initialized(false);
    this.finished(false);
}

SCORM_API.prototype.initialized = function(initialized) {
    if(initialized !== undefined) {
        this._initialized = initialized;
    }
    return this._initialized;
}

SCORM_API.prototype.finished = function(finished) {
    if(finished !== undefined) {
        this._finished = finished;
    }
    return this._finished;
}

SCORM_API.prototype.value = function(param, value) {
    if(value !== undefined) {
        this.data = deepObjSetValue(this.data, param, value);
    }
    value = deepObjGetValue(this.data, param);
    if(value === undefined) {
        value = "";
    }
    return value;
}

SCORM_API.prototype.datamodel = function(datamodel) {
    if(datamodel !== undefined) {
        this._datamodel = datamodel;
    }
    return this._datamodel;
}

SCORM_API.prototype.typesHandler = function(typesHandler) {
    if(typesHandler !== undefined) {
        this._typesHandler = typesHandler;
    }
    return this._typesHandler;
}

SCORM_API.prototype.error = function(error) {
    if(error !== undefined) {
        if(error !== "0") {
            SCORM_API.debug(SCORM_API.errors(error), "Error " + error);
        }
        this._error = error;
    }
    return this._error;
}

SCORM_API.prototype.errorString = function(error) {
    return SCORM_API.errors()[error];
}

SCORM_API.prototype.initialize = function(arg) {
    SCORM_API.debug("Initialize - start");
    SCORM_API.triggerEvent("initializing");
	if(this.initialized()) {
        this.error("103");
        SCORM_API.debug("SCORM already initialized");
		return "false";
	}
	if(arg != "") {
        this.error("201");
        SCORM_API.debug("arg must be an empty string");
        SCORM_API.debug(arg, "arg");
		return "false";
    }
    try {
        let response = this.player.requestInitialize();
        this.data = response;
        this.initialized(true);
        this.finished(false)
        SCORM_API.debug(this.data, "data");
    } catch(e) {
        this.error("102");
		return "false";
    }
    SCORM_API.triggerEvent("initialized");
    SCORM_API.debug("Initialize - end");
    return "true";
}

SCORM_API.prototype.commit = function(arg) {
    SCORM_API.debug("Commit - start");
    SCORM_API.triggerEvent("committing");
    if(!this.initialized()) {
        this.error("142");
        SCORM_API.debug("SCORM not initialized");
		return "false";
    }
	if(this.finished()) {
        this.error("143");
        SCORM_API.debug("SCORM already finished");
        return "false";
    }
	if(arg !== "") {
        this.error("201");
        SCORM_API.debug("arg must be an empty string");
		return "false";
    }
    this.error("0");
    try {
        let response = this.player.requestCommit(this.data);
    } catch(e) {
        this.error("391");
		return "false";
    }
    SCORM_API.triggerEvent("committed");
    SCORM_API.debug("Commit - end");
	return "true";
}

SCORM_API.prototype.finish = function(arg) {
    SCORM_API.debug("Finish - start");
    SCORM_API.triggerEvent("finishing");
	if(this.finished()) {
        this.error("113");
        SCORM_API.debug("SCORM already finished");
        return "false";
    }
    if(!this.initialized()) {
        this.error("112");
        SCORM_API.debug("SCORM not initialized");
		return "false";
    }
	if(arg !== "") {
        this.error("201");
        SCORM_API.debug("arg must be an empty string");
		return "false";
	}
    this.error("0");
    try {
        let response = this.player.requestFinish(this.data);
        this.initialized(false)
        this.finished(true);
    } catch(e) {
        this.error("111");
		return "false";
    }
    SCORM_API.triggerEvent("finished");
    SCORM_API.debug("Finish - end");
	return "true";
}

SCORM_API.prototype.get = function(param) {
    SCORM_API.debug("Get " + param + " - start");
    if(!this.initialized()) {
        this.error("122");
        SCORM_API.debug("SCORM not initialized");
		return "false";
    }
	if(this.finished()) {
        this.error("123");
        SCORM_API.debug("SCORM already finished");
        return "false";
    }
    this.error("0");
    let result = "";
    try {
        let param_obj = new SCORM_Param(this, param);
        result = param_obj.get();
        SCORM_API.debug(result, param);
    } catch(error) {
        this.error(error);
    }
    SCORM_API.debug("Get - end");
	return result;
}

SCORM_API.prototype.set = function(param, value) {
    SCORM_API.debug("Set " + param + "=" + value + " - start");
    if(!this.initialized()) {
        this.error("132");
        SCORM_API.debug("SCORM not initialized");
		return "false";
    }
	if(this.finished()) {
        this.error("133");
        SCORM_API.debug("SCORM already finished");
        return "false";
    }
    this.error("0");
    let result = "false";
    try {
        let param_obj = new SCORM_Param(this, param);
        param_obj.set(value);
        result = "true";
    } catch(error) {
        if(isNaN(error)) {
            SCORM_API.debug(error);
        } else {
            this.error(error);
        }
    }
    SCORM_API.debug("Set - end");
	return result;
}

function SCORM_API_12(player) {
    SCORM_API.call(this, player);
    let r = RESTClient.get("./datamodel/1.2.json");
    this.datamodel(r.response);
    this.typesHandler(new SCORM_TypesHandler_12());
    window.API = this;
}
SCORM_API_12.prototype = Object.create(SCORM_API.prototype);
SCORM_API_12.version = function() { return "1.2"; };
SCORM_API_12.prototype.LMSInitialize     = function(arg)             { return this.initialize(arg); }
SCORM_API_12.prototype.LMSFinish         = function(arg)             { return this.finish(arg); }
SCORM_API_12.prototype.LMSCommit         = function(arg)             { return this.commit(arg); }
SCORM_API_12.prototype.LMSGetValue       = function(param)           { return this.get(param); }
SCORM_API_12.prototype.LMSSetValue       = function(param, value)    { return this.set(param, value); }
SCORM_API_12.prototype.LMSGetLastError   = function()                { return this.error(); }
SCORM_API_12.prototype.LMSGetErrorString = function(code)            { return SCORM_API.errors(code); }
SCORM_API_12.prototype.LMSGetDiagnostic  = function(code)            { return SCORM_API.errors(code); } // TODO
SCORM_API_12.prototype.constructor = SCORM_API_12;

function SCORM_API_13(player) {
    SCORM_API.call(this, player);
    let r = RESTClient.get("./datamodel/1.3.json");
    this.datamodel(r.response);
    this.typesHandler(new SCORM_TypesHandler_13());
    window.API_1484_11 = this;
}
SCORM_API_13.prototype = Object.create(SCORM_API.prototype);
SCORM_API_13.version = function() { return "1.3"; };
SCORM_API_13.prototype.Initialize        = function(arg)             { return this.initialize(arg); }
SCORM_API_13.prototype.Terminate         = function(arg)             { return this.finish(arg); }
SCORM_API_13.prototype.Commit            = function(arg)             { return this.commit(arg); }
SCORM_API_13.prototype.GetValue          = function(param)           { return this.get(param); }
SCORM_API_13.prototype.SetValue          = function(param, value)    { return this.set(param, value); }
SCORM_API_13.prototype.GetLastError      = function()                { return this.error(); }
SCORM_API_13.prototype.GetErrorString    = function(code)            { return SCORM_API.errors(code); }
SCORM_API_13.prototype.GetDiagnostic     = function(code)            { return SCORM_API.errors(code); } // TODO
SCORM_API_13.prototype.constructor = SCORM_API_13;
