function SCO(SCORM, SCO, parent) {
    this.SCORM = SCORM;
    this._id            = SCO.id;
    this._identifier    = SCO.identifier;
    this._title         = SCO.title;
    this._resource      = SCO.resource;
    this._parameters    = SCO.parameters || "";
    this._visible       = SCO.visible;
    this._prerequisites = SCO.prerequisites || [];
    this._parent        = parent;
    this._tracking = SCO.tracking ? {
        completionStatus: SCO.tracking.completionStatus,
        successStatus: SCO.tracking.successStatus
    } : {
        completionStatus: "not attempted",
        successStatus: "unknown"
    }
    this.children([]);
}

SCO.prototype.id = function() {
    return this._id;
}

SCO.prototype.identifier = function() {
    return this._identifier;
}

SCO.prototype.title = function() {
    return this._title;
}

SCO.prototype.resource = function() {
    return this._resource;
}

SCO.prototype.parameters = function() {
    return this._parameters;
}

SCO.prototype.visible = function() {
    return this._visible;
}

SCO.prototype.prerequisites = function() {
    return this._prerequisites;
}

SCO.prototype.parent = function() {
    return this._parent;
}

SCO.prototype.children = function(SCOes) {
    if(SCOes !== null && typeof SCOes !== "undefined") {
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
    var stack = [];

    var prerequisites = this._prerequisites;
    var _this = this;

    // Expand the amp entities.
    prerequisites = prerequisites.replace(/&amp;/gi, '&');
    // Find all my parsable tokens.
    prerequisites = prerequisites.replace(/(&|\||\(|\)|\~)/gi, '\t$1\t');
    // Expand operators.
    prerequisites = prerequisites.replace(/&/gi, '&&');
    prerequisites = prerequisites.replace(/\|/gi, '||');
    // Now - grab all the tokens.
    var elements = prerequisites.trim().split('\t');

    // Process each token to build an expression to be evaluated.
    elements.forEach(function(element) {
        element = element.trim();
        if (!element) {
            return;
        }
        if (!element.match(/^(&&|\|\||\(|\))$/gi)) {
            // Create each individual expression.
            // Search for ~ = <> X*{} .

            // Sets like 3*{S34, S36, S37, S39}.
            var re = /^(\d+)\*\{(.+)\}$/;
            // Other symbols.
            var reOther = /^(.+)(\=|\<\>)(.+)$/;

            if (re.test(element)) {
                var matches = element.match(re);
                var repeat = matches[1];
                var set = matches[2].split(',');
                var count = 0;
                set.forEach(function(setelement) {
                    setelement = setelement.trim();
                    if (_this.SCORM.SCOByIdentifier(setelement).tracking('completionStatus') == 'completed'
                        || _this.SCORM.SCOByIdentifier(setelement).tracking('successStatus') == 'passed') {
                        count++;
                    }
                });
                if (count >= repeat) {
                    element = 'true';
                } else {
                    element = 'false';
                }
            } else if (element == '~') {
                // Not maps ~.
                element = '!';
            } else if (reOther.test(element)) {
                // Other symbols = | <> .
                var matches = element.match(reOther);
                element = matches[1].trim();
                value = matches[3].trim().replace(/(\'|\")/gi, '');
                if (matches[2] == '<>') {
                    oper = '!=';
                } else {
                    oper = '==';
                }
                element = '(\'' + _this.SCORM.SCOByIdentifier(element).tracking('completionStatus') + '\' ' + oper + ' \'' + value + '\' || \''
                 + _this.SCORM.SCOByIdentifier(element).tracking('successStatus') + '\' ' + oper + ' \'' + value + '\')';
            } else {
                // Everything else must be an element defined like S45 ...
                if (_this.SCORM.SCOByIdentifier(element).tracking('completionStatus') == 'completed'
                    || _this.SCORM.SCOByIdentifier(element).tracking('successStatus') == 'passed') {
                    element = 'true';
                } else {
                    element = 'false';
                }
            }

        }
        stack.push(' ' + element + ' ');
    });

    return eval(stack.join('') + ';');
};
