document.addEventListener("DOMContentLoaded", function() {
    try {
        let player = new Player();
        player.getSCORM();
        player.findFirstSCO();
        player.draw();
        player.play();
    } catch(e) {
        // Errors should be already handled.
        // Error throwing used to stop flow execution.
    }
});

function Player() {
    this.DOM = {
        body:       document.body,
        SCORMtitle: document.getElementById("scorm-title"),
        SCOtitle:   document.getElementById("sco-title"),
        errors:     document.getElementById("errors"),
        TOC:        document.getElementById("toc"),
        TOCToggle:  document.getElementById("toc-toggle"),
        TOCContent: document.getElementById("toc-content"),
        SCORM:      document.getElementById("scorm")
    };
    let _this = this;
    this.DOM.TOCToggle.addEventListener("click", function(e) {
        _this.DOM.TOC.classList.toggle("hidden");
    });
    document.addEventListener("SCORM_API.finished", function(e) {
        _this.close();
    });
    this.getParameters();
    this.keepAlive(1 * 60 * 1000); // 1 minute.
}

Player.prototype.printError = function(error) {
    let error_p     = document.createElement("p");
    let error_text  = document.createTextNode(error);
    error_p.appendChild(error_text);
    this.DOM.errors.appendChild(error_p);
}

Player.prototype.findFirstSCO = function() {
    let firstSCO = null;
    if(this.parameters.sco) {
        firstSCO = this.SCORM().flatSCOes(this.parameters.sco);
    } else {
        let i = 0;
        let SCOes = Object.values(this.SCORM().flatSCOes());
        let autoplay = this.SCORM().config("autoplay");
        let find = function(_autoplay) {
            do {
                let SCO = SCOes[i];
                let status = SCO.tracking("status");
                let resource = SCO.resource();
                if(resource) {
                    if(_autoplay) {
                        if(status !== "completed" && status !== "passed") {
                            firstSCO = SCO;
                        }
                    } else {
                        firstSCO = SCO;
                    }
                }
                i++;
            } while(!firstSCO && i < SCOes.length);
            return firstSCO;
        }
        firstSCO = find(autoplay);
        if(!firstSCO && autoplay) {
            firstSCO = find(false);
        }
    }
    this.SCO(firstSCO);
}

Player.prototype.play = function() {
    this.APIHandler = new SCORM_API.factory(this);
    this.DOM.SCORMtitle.textContent = this.SCORM().title();
    this.DOM.SCOtitle.textContent = this.SCO().title();
    document.title = this.SCORM().title();
    //document.getElementsByTagName('meta')["description"].content = this.SCORM().description();
    //document.getElementsByTagName('meta')["author"].content = this.SCORM().author();
    this.DOM.SCORM.setAttribute("src", this.SCO().resource());
    this.DOM.SCORM.addEventListener('load', this.loaded.bind(this));    
}

Player.prototype.loaded = function() {
    this.DOM.SCORM.contentWindow.addEventListener('unload', this.forceFinish.bind(this));
}

Player.prototype.forceFinish = function() {
    let doc = this.DOM.SCORM.contentDocument || this.DOM.SCORM.contentWindow.document;
    let docElement = doc.documentElement;
    doc.removeChild(docElement);
    if(!this.APIHandler.initialized()) {
        this.APIHandler.initialize("");
    }
    if(!this.APIHandler.finished()) {
        this.APIHandler.commit("");
        this.APIHandler.finish("");
    }
    doc.appendChild(docElement);
}

Player.prototype.close = function() {
    if(this.SCORM().countSCOes() <= 1) {
        console.log("Close player");
        parent.closePlayer();
    }
}

Player.prototype.keepAlive = function(timeout) {
    let keepRequest = function() {
        RESTClient.head("../../ajax.server.php?r=lms/playerscorm/keep?authentic_request="+ this.parameters.authentic_request);
        window.setTimeout(keepRequest, timeout);
    };
    window.setTimeout(keepRequest, timeout);
}

Player.prototype.SCORM = function(SCORM) {
    if(SCORM !== undefined) {
        this._SCORM = SCORM;
        console.debug("SCORM:", this._SCORM);
    }
    return this._SCORM;
}

Player.prototype.SCO = function(SCO) {
    if(SCO !== undefined) {
        this._SCO = SCO;
        console.debug("SCO:", this._SCO);
    }
    return this._SCO;
}

Player.prototype.getParameters = function() {
    let url_params = new URLSearchParams(window.location.search);
    this.parameters = {
        authentic_request:  url_params.get("authentic_request"),
        scorm:  url_params.get("scorm"),
        sco:    url_params.get("sco")
    };    
    if(this.parameters.scorm === null) {
        this.printError("SCORM player can not be initialized: \"scorm\" paramater required.");
        throw Error();
    }
    console.log("Parameters:", "scorm " + this.parameters.scorm, "-", "sco " + this.parameters.sco);
}

Player.prototype.getSCORM = function() {
    this.SCORM(new SCORM(this));
    console.log("Executing SCORM " + this.SCORM().id() + ":", this.SCORM().title());
}

Player.prototype.requestSCORM = function() {
    let r = RESTClient.get("../../ajax.server.php?r=lms/playerscorm/scorm&id=" + this.parameters.scorm + "&authentic_request="+ this.parameters.authentic_request);
    if(r.status !== 200) {
        switch(r.status) {
            case 204:
                this.printError("SCORM player can not be initialized: SCORM " + this.parameters.scorm + " not found.");
                throw Error();
            default:
                this.printError("SCORM player can not be initialized: unknown error.");
                throw Error();
        }
    }
    return r.response;
}

Player.prototype.requestInitialize = function() {
    let r = RESTClient.get("../../ajax.server.php?r=lms/playerscorm/initialize&id=" + this.SCO().id() + "&authentic_request="+ this.parameters.authentic_request);
    if(r.status !== 200) {
        throw Error();
    }
    return r.response;
}

Player.prototype.requestCommit = function(data) {
    let r = RESTClient.post("../../ajax.server.php?r=lms/playerscorm/commit&id=" + this.SCO().id() + "&authentic_request="+ this.parameters.authentic_request, data);
    if(r.status !== 200) {
        throw Error();
    }
    return r.response;
}

Player.prototype.requestFinish = function(data) {
    let r = RESTClient.post("../../ajax.server.php?r=lms/playerscorm/finish&id=" + this.SCO().id() + "&authentic_request="+ this.parameters.authentic_request, data);
    if(r.status !== 200) {
        throw Error();
    }
    return r.response;
}
Player.prototype.draw = function() {
    console.log("Drawing TOC");
    while(this.DOM.TOCContent.firstChild) {
        this.DOM.TOCContent.removeChild(this.DOM.TOCContent.firstChild);
    }
    this.DOM.body.classList.remove("signlesco");
    this.DOM.body.classList.remove("multisco");
    this.DOM.TOCContent.appendChild(this.buildTOC(this.SCORM().SCOes(), 0));
    if(this.SCORM().countSCOes() > 1) {
        console.debug("MultiSCO", "(" + this.SCORM().countSCOes() + ")");
        this.DOM.body.classList.add("multisco");
    } else {
        console.debug("SingleSCO");
        this.DOM.body.classList.add("singlesco");
    }
}

Player.prototype.buildTOC = function(SCOes, level) {
    let ul = document.createElement("ul");
    for(let i = 0; i < SCOes.length; i++) {
        let SCO = SCOes[i];
        let text = document.createTextNode(SCO.title());
        let button = document.createElement("button");
        let h = document.createElement("h" + (level + 1));
        let li = document.createElement("li");
        let _this = this;
        button.appendChild(text);
        h.appendChild(button);
        if(SCO.resource()) {
            button.addEventListener("click", function(e) {
                _this.getSCORM();
                _this.SCO(SCO);
                _this.draw();
                _this.play();
            });
        }
        li.appendChild(h);
        if(SCO.children().length > 0) {
            li.classList.add("has-children");
            li.appendChild(this.buildTOC(SCO.children(), level + 1));
            button.addEventListener("click", function() {
                li.classList.toggle("collapsed");
            });
        }
        li.classList.add("status-" + SCO.tracking("status"));
        li.classList.add("prerequisites-" + (SCO.checkPrerequisites() ? "satisfied" : "unsatisfied"));
        if(SCO.id() === this.SCO().id()) {
            li.classList.add("current");
        }
        ul.appendChild(li);
    }
    return ul;
}
