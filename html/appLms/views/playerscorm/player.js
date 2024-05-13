document.addEventListener("DOMContentLoaded", function() {
    try {
        let player = new Player();
        player.getSCORM();
        player.findFirstSCO();
        player.draw();
        player.play();
    } catch(e) {
        console.error(e);
    }
});

function Player() {
    this.baseURL = '../..';
    this.errors = {}
    this.DOM = {
        body:           document.body,
        SCORMtitle:     document.getElementById("scorm-title"),
        SCOtitle:       document.getElementById("sco-title"),
        closeButton:    document.getElementById("close"),
        errors:         document.getElementById("errors"),
        TOC:            document.getElementById("toc"),
        TOCToggle:      document.getElementById("toc-toggle"),
        TOCContent:     document.getElementById("toc-content"),
        SCORM:          document.getElementById("scorm")
    };
    this.DOM.TOCToggle.addEventListener("click", function(e) {
        this.DOM.TOC.classList.toggle("hidden");
    }.bind(this));
    this.DOM.closeButton.addEventListener("click", function(e) {
        this.close();
    }.bind(this));
    window.addEventListener("beforeunload", function(e) {
        this.unloading = true;
    }.bind(this));
    this.getParameters();
    this.keepAlive(1 * 60 * 1000); // 1 minute.
}

Player.prototype.makeHeaders = function() {
	let headers = {};
	
	if (this.parameters.token) {
		headers = {"Authorization": "bearer " + this.parameters.token}
	}
	return headers;
}

Player.prototype.printError = function(message) {
    if(this.errors[message]) {
        this.errors[message]++;
    } else {
        this.errors[message] = 1;
    }
    this.DOM.errors.innerHTML = "";
    
    Object.keys(this.errors).forEach(function(error) {
        if(this.errors[error] > 1) {
            error += " (" + this.errors[error] + ")"
        }
        let error_p     = document.createElement("p");
        let error_text  = document.createTextNode(error);
        error_p.appendChild(error_text);
        this.DOM.errors.appendChild(error_p);
    }.bind(this));
}

Player.prototype.printServerCommunicationError = function() {
    this.printError(i18n.translate("server-communication-error"));
}

Player.prototype.findFirstSCO = function() {
    let autoplay = this.SCORM().config("autoplay");
    let firstSCO = null;
    if(this.parameters.sco) {
        firstSCO = this.SCORM().SCOById(this.parameters.sco);
    } else {    
        firstSCO = this.getNext(0, autoplay) || this.getNext(0, false);
    }
    this.SCO(firstSCO);
}

Player.prototype.getNext = function(start, autoplay) {
    let SCOes = this.SCORM().SCOes();
    for(let i = start; i < SCOes.length; i++) {
        let SCO = SCOes[i];
        if(SCO.resource()) {
            if(SCO.prerequisites().length == 0 || SCO.checkPrerequisites()) {
                if(autoplay) {
                    if(SCO.tracking("completionStatus") !== "completed" || SCO.tracking("successStatus") === "failed") {
                        return SCO;
                    }
                } else {
                    return SCO;
                }
            }
        }
    }
    return null;
};

Player.prototype.play = function() {
    this.APIHandler = SCORM_API.factory(this);

    this.DOM.SCORMtitle.textContent = this.SCORM().title();
    this.DOM.SCOtitle.textContent = this.SCO().title();
    document.title = this.SCORM().title();
    document.getElementsByTagName("meta")["description"].content = this.SCORM().description();
    document.getElementsByTagName("meta")["author"].content = this.SCORM().author();
	
	let src = getSiloAndCustomer() + this.SCORM().path() + "/" + this.SCO().resource() + this.SCO().parameters();
    this.DOM.SCORM.addEventListener("load", (this.loaded = this.loaded.bind(this)));
    this.DOM.SCORM.setAttribute("src", src);

    console.debug("Source", src);
    console.log("Playing SCORM");
}

Player.prototype.continue = function() {
    this.getSCORM();
    let index = this.SCORM().SCOIndex(this.SCO().id().toString());
    let nextSCO = this.getNext(index, true);
    if (nextSCO && this.SCO().id() !== nextSCO.id()) {
        window.location.href = window.location.href.replace(window.location.search, buildQuery({
            scorm: this.parameters.scorm,
            sco: nextSCO.id(),
            tipoEntita: this.parameters.tipoEntita,
            codEntita: this.parameters.codEntita,
            token: this.parameters.token,
            preview: this.parameters.preview
        }));
    } else {
        this.close();
    }
}

Player.prototype.loaded = function() {
    this.DOM.SCORM.removeEventListener("load", this.loaded);
    window.addEventListener("unload", (this.forceFinish = this.forceFinish.bind(this)));
    this.APIHandler.launch();
    this.APIHandler.listen("initialized", function() {
        this.refreshTOC = false;
    }.bind(this));
    this.APIHandler.listen("setting", function(args) {
        if(["cmi.core.lesson_status", "cmi.completion_status", "cmi.success_status"].includes(args.param)) {
            if(args.value != this.APIHandler.value(args.param)) {
                this.refreshTOC = true;
            }
        }
    }.bind(this));
    this.APIHandler.listen("committed", function() {
        if(this.refreshTOC) {
            this.refreshTOC = false;
            this.getSCORM();
            this.draw();
        }
    }.bind(this));
    this.APIHandler.listen("finished", function() {
        if (!this.unloading) {
            this.continue();
        }
    }.bind(this));

    this.unloading = false;
}

Player.prototype.forceFinish = function() {
    window.removeEventListener("unload", this.forceFinish);
    let doc = this.DOM.SCORM.contentDocument || this.DOM.SCORM.contentWindow.document;
    let docElement = doc.documentElement;
    doc.removeChild(docElement);
    this.APIHandler.close();
    doc.appendChild(docElement);
}

Player.prototype.close = function() {
    console.log("Close player");
    if(parent.closePlayer !== undefined) {
        parent.closePlayer();
    } else {
        window.close();
    }
}

Player.prototype.keepAlive = function(interval) {
    const headers = this.makeHeaders();
    let keepRequest = function() {
        try {
            RESTClient.post("../../ajax.server.php?r=lms/playerscorm/keep&authentic_request="+ this.parameters.authentic_request, null, headers, false);
        } catch(e) {
            this.printServerCommunicationError();
        }
        window.setTimeout(keepRequest.bind(this), interval);
    };
    window.setTimeout(keepRequest.bind(this), interval);
}

Player.prototype.SCORM = function(SCORM) {
    if(SCORM !== undefined) {
        this._SCORM = SCORM;
        console.debug("SCORM:", this._SCORM);
        console.log("SCORM " + this.SCORM().id() + ":", this.SCORM().title());
    }
    return this._SCORM;
}

Player.prototype.SCO = function(SCO) {
    if(SCO !== undefined) {
        this._SCO = SCO;
        console.debug("SCO:", this._SCO);
        console.log("SCO " + this.SCO().id() + ":", this.SCO().title());
    }
    return this._SCO;
}

Player.prototype.getParameters = function() {
    this.parameters = parseQuery(window.location.search);
    if(!this.parameters.scorm) {
        this.printError(i18n.translate("missing-scorm-parameter"));
        throw Error("Missing \"scorm\" parameter");
    }    
    console.log("Parameters:", "scorm " + this.parameters.scorm, "-", "sco " + this.parameters.sco);
}

Player.prototype.getSCORM = function() {
    this.SCORM(new SCORM(this));
}

Player.prototype.requestSCORM = function() {
    let rSCORM;
    let params = "scormId=" + this.parameters.scorm;
    if(this.parameters.tipoEntita && this.parameters.codEntita) {
        params += "&tipoEntita=" + this.parameters.tipoEntita;
        params += "&codEntita=" + this.parameters.codEntita;
    }
    params += "&preview=" + this.parameters.preview;
    params += "&authentic_request=" + this.parameters.authentic_request;
    
    const headers = this.makeHeaders();

    let printLoadingSCORMError = function() {
        this.printError(i18n.translate("scorm-loading-error"));
    }.bind(this);
    
    try {
    	//FIXME;
        rSCORM = RESTClient.get("../../ajax.server.php?r=lms/playerscorm/scorm&" + params, headers, false);
    } catch(e) {
        this.printServerCommunicationError();
        printLoadingSCORMError();
        throw e;
    }
    if(rSCORM.status !== 200) {
        printLoadingSCORMError();
        throw Error("Error in SCORM request");
    }
    if(rSCORM.response.success === false) {
        printLoadingSCORMError();
        throw Error(rSCORM.response.errors);
    }
    let SCORM = rSCORM.response.result.scorm;
    let rConfig;
    try {
        rConfig = RESTClient.get("../../ajax.server.php?r=lms/playerscorm/config&" + params, headers, false);
    } catch(e) {
        this.printServerCommunicationError();
        printLoadingSCORMError();
        throw e;
    }
    if(rConfig.status !== 200) {
        printLoadingSCORMError();
        throw Error("Error in config request");
    }
    if(rConfig.response.success === false) {
        printLoadingSCORMError();
        throw Error(rConfig.response.errors);
    }
    SCORM.config = rConfig.response.result;
    return SCORM;
}

Player.prototype.requestInitialize = function() {
    let r;
    let params = "scoId=" + this.SCO().id();
    if(this.parameters.tipoEntita && this.parameters.codEntita) {
        params += "&tipoEntita=" + this.parameters.tipoEntita;
        params += "&codEntita=" + this.parameters.codEntita;
    }
    params += "&preview=" + this.parameters.preview;
    params += "&authentic_request=" + this.parameters.authentic_request;

    const headers = this.makeHeaders();
    
    try {
        r = RESTClient.get("../../ajax.server.php?r=lms/playerscorm/initialize&" + params, headers, this.unloading ? true : false);
    } catch(e) {
        this.printServerCommunicationError();
        throw e;
    }
    if(r !== null) {
        if(r.status !== 200) {
            throw Error("Error during track initialize request");
        }
        if(r.response.success === false) {
            throw Error(r.response.errors);
        }
        return r.response.result.track;
    } else {
        return null;
    }
}

Player.prototype.requestCommit = function(data) {
    let r;
    let params = "scoId=" + this.SCO().id();
    if(this.parameters.tipoEntita && this.parameters.codEntita) {
        params += "&tipoEntita=" + this.parameters.tipoEntita;
        params += "&codEntita=" + this.parameters.codEntita;
    }
    params += "&preview=" + this.parameters.preview;
    params += "&authentic_request=" + this.parameters.authentic_request;

    const headers = this.makeHeaders();
    
    try {
        r = RESTClient.post("../../ajax.server.php?r=lms/playerscorm/commit&" + params, data, headers, this.unloading ? true : false);
    } catch(e) {
        this.printServerCommunicationError();
        throw e;
    }
    if(r !== null) {
        if(r.status !== 200) {
            throw Error("Error during track commit request");
        }
        if(r.response.success === false) {
            throw Error(r.response.errors);
        }
        return r.response.result;
    } else {
        return null;
    }
}

Player.prototype.requestFinish = function(data) {
    let r;
    let params = "scoId=" + this.SCO().id();
    if(this.parameters.tipoEntita && this.parameters.codEntita) {
        params += "&tipoEntita=" + this.parameters.tipoEntita;
        params += "&codEntita=" + this.parameters.codEntita;
    }
    params += "&preview=" + this.parameters.preview;
    params += "&authentic_request=" + this.parameters.authentic_request;
    
    const headers = this.makeHeaders();
    
    try {
        r = RESTClient.post("../../ajax.server.php?r=lms/playerscorm/finish&" + params, data, headers, this.unloading ? true : false);
    } catch(e) {
        this.printServerCommunicationError();
        throw e;
    }
    if(r !== null) {
        if(r.status !== 200) {
            throw Error("Error during track finish request");
        }
        if(r.response.success === false) {
            throw Error(r.response.errors);
        }
        return r.response.result;
    } else {
        return null;
    }
}

Player.prototype.requestPull = function(data) {
    let r;
    let params = "scoId=" + this.SCO().id();
    if(this.parameters.tipoEntita && this.parameters.codEntita) {
        params += "&tipoEntita=" + this.parameters.tipoEntita;
        params += "&codEntita=" + this.parameters.codEntita;
    }
    params += "&preview=" + this.parameters.preview;
    params += "&authentic_request=" + this.parameters.authentic_request;
    
    const headers = this.makeHeaders();
    
    try {
        r = RESTClient.get("../../ajax.server.php?r=lms/playerscorm/pull&" + params, headers, true);
    } catch(e) {
        this.printServerCommunicationError();
        throw e;
    }
}

Player.prototype.draw = function() {
    while(this.DOM.TOCContent.firstChild) {
        this.DOM.TOCContent.removeChild(this.DOM.TOCContent.firstChild);
    }
    this.DOM.body.classList.remove("signlesco");
    this.DOM.body.classList.remove("multisco");
    this.DOM.TOCContent.appendChild(this.buildTOC(this.SCORM().SCOesTree(), 0));
    if (this.SCORM().config("openToc")) {
        this.DOM.TOC.classList.remove("hidden");
    } else {
        this.DOM.TOC.classList.add("hidden");
    }
    if(this.SCORM().countSCOes() > 1) {
        this.DOM.body.classList.add("multisco");
    } else {
        this.DOM.body.classList.add("singlesco");
    }
}

Player.prototype.buildTOC = function(SCOes, level) {
    let ul = document.createElement("ul");
    for(let i = 0; i < SCOes.length; i++) {
        let SCO = SCOes[i];
        if(!SCO.visible()) {
            continue;
        }
        let text = document.createTextNode(SCO.title());
        let button = document.createElement("a");
        let h = document.createElement("h" + (level + 1));
        let li = document.createElement("li");
        button.appendChild(text);
        h.appendChild(button);
        if(SCO.resource()) {
            button.setAttribute("href", window.location.href.replace(window.location.search, buildQuery({
                scorm: this.parameters.scorm,
                sco: SCO.id(),
                tipoEntita: this.parameters.tipoEntita,
                codEntita: this.parameters.codEntita,
                token: this.parameters.token,
                preview: this.parameters.preview
            })));
        }
        li.appendChild(h);
        if(SCO.children().length > 0) {
            li.classList.add("has-children");
            li.appendChild(this.buildTOC(SCO.children(), level + 1));
            button.addEventListener("click", function() {
                li.classList.toggle("collapsed");
            });
        }
        li.classList.add("completion-status-" + SCO.tracking("completionStatus").replace(" ", "-"));
        li.classList.add("success-status-" + SCO.tracking("successStatus").replace(" ", "-"));
        if(SCO.prerequisites().length > 0) {
            let ok = SCO.checkPrerequisites();
            li.setAttribute("title", "Prerequisites " + (ok ? "satisfied" : "unsatisfied"));
            li.classList.add("prerequisites-" + (ok ? "satisfied" : "unsatisfied"));
        }
        if(SCO.id() === this.SCO().id()) {
            li.classList.add("current");
        }
        ul.appendChild(li);
    }
    return ul;
}
