function SCORM(player) {
    this.player = player;

    let SCORM = this.player.requestSCORM();

    this._id        = SCORM.id;
    this._version   = SCORM.version;
    this._title     = SCORM.title;
    this._config    = SCORM.config || {};
    this._flatSCOes = {};
    this._SCOes     = this.walkSCOes(SCORM.scoes, null);
}

SCORM.prototype.walkSCOes = function(SCOes, parent) {
    let _SCOes = [];
    for(let i = 0; i < SCOes.length; i++) {
        let _SCO = new SCO(this, SCOes[i], parent);
        _SCO.children(this.walkSCOes(SCOes[i].children, _SCO.id()));
        _SCOes.push(_SCO);
        this._flatSCOes[_SCO.id()] = _SCO;
    }
    return _SCOes;
}

SCORM.prototype.id = function() {
    return this._id;
}

SCORM.prototype.version = function() {
    return this._version;
}

SCORM.prototype.title = function() {
    return this._title;
}

SCORM.prototype.SCOes = function() {
    return this._SCOes;
}

SCORM.prototype.flatSCOes = function(id) {
    if(id !== undefined) {
        return this._flatSCOes[id];
    } else {
        return this._flatSCOes;
    }
}

SCORM.prototype.countSCOes = function() {
    return Object.keys(this.flatSCOes()).length;
}

SCORM.prototype.config = function(key) {
    if(key) {
        return this._config[key];
    } else {
        return this._config;
    }
}
