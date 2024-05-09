function SCO(SCORM, SCO, parent) {
    this.SCORM = SCORM;
    this._id            = SCO.id;
    this._title         = SCO.title;
    this._resource      = SCO.resource;
    this._prerequisites = SCO.prerequisites;
    this._parent        = parent;
    this._tracking = {
        status: SCO.tracking.status || "not-attempted"
    }
    this.children([]);
}

SCO.prototype.id = function() {
    return this._id;
}

SCO.prototype.title = function() {
    return this._title;
}

SCO.prototype.resource = function() {
    return this._resource;
}

SCO.prototype.prerequisites = function() {
    return this.prerequisites;
}

SCO.prototype.parent = function() {
    return this._parent;
}

SCO.prototype.children = function(SCOes) {
    if(SCOes !== undefined) {
        this._children = SCOes;
    }
    return this._children;
}

SCO.prototype.tracking = function(key) {
    if(key) {
        return this._tracking[key];
    } else {
        return this._tracking;
    }
}

SCO.prototype.checkPrerequisites = function() {
    let satisfied = true;
    for(let i = 0; i < this._prerequisites; i++) {
        let status = this.SCORM.flatSCOes(this._prerequisites[i]).tracking("status");
        if(status !== "completed" && status !== "passed") {
            satisfied = false;
            break;
        }
    }
    return satisfied;
}
