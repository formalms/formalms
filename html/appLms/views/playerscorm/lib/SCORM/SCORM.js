function SCORM(player) {
    this.player = player;

    let SCORM = this.player.requestSCORM();

    let defaultConfig = {
        autoplay: false,
        autoToc: true
    };

    this._id                = SCORM.id;
    this._version           = SCORM.version;
    this._path              = SCORM.path;
    this._title             = SCORM.title;
    this._description       = SCORM.description;
    this._author            = SCORM.author;
    this._title             = SCORM.title;
    this._config            = Object.assign(defaultConfig, SCORM.config);
    this._SCOesId           = {};
    this._SCOesIdentifier   = {};
    this._SCOesTree         = this.walkSCOes(SCORM.scoes, null);
}

SCORM.prototype.walkSCOes = function(SCOes, parent) {
    let _SCOes = [];
    for(let i = 0; i < SCOes.length; i++) {
        let _SCO = new SCO(this, SCOes[i], parent);
        _SCO.children(this.walkSCOes(SCOes[i].children, _SCO.id()));
        _SCOes.push(_SCO);
        this._SCOesId[_SCO.id()] = _SCO;
        this._SCOesIdentifier[_SCO.identifier()] = _SCO;
    }
    return _SCOes;
}

SCORM.prototype.id = function() {
    return this._id;
}

SCORM.prototype.version = function() {
    return this._version;
}

SCORM.prototype.path = function() {
    return this._path;
}

SCORM.prototype.title = function() {
    return this._title;
}

SCORM.prototype.description = function() {
    return this._description;
}

SCORM.prototype.author = function() {
    return this._author;
}

SCORM.prototype.SCOesTree = function() {
    return this._SCOesTree;
}

SCORM.prototype.SCOes = function() {
    return Object.values(this._SCOesId);
}

SCORM.prototype.SCOById = function(id) {
    return this._SCOesId[id];
}

SCORM.prototype.SCOByIdentifier = function(identifier) {
    return this._SCOesIdentifier[identifier];
}

SCORM.prototype.SCOIndex = function(id) {
    return Object.keys(this._SCOesId).indexOf(id);
}

SCORM.prototype.countSCOes = function() {
    return Object.keys(this._SCOesId).length;
}

SCORM.prototype.config = function(key) {
    if(key) {
        return this._config[key];
    } else {
        return this._config;
    }
}
