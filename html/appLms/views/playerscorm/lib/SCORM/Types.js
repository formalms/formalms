function SCORM_TypesHandler() { }

SCORM_TypesHandler.log = function(log, label) {
    if(label !== null && typeof label !== "undefined") {
        console.log("SCORM_TypesHandler", "-", label + ":", log);
    } else {
        console.log("SCORM_TypesHandler", "-", log);
    }
}

SCORM_TypesHandler.debug = function(debug, label) {
    if(label !== null && typeof label !== "undefined") {
        console.debug("SCORM_TypesHandler", "-", label + ":", debug);
    } else {
        console.debug("SCORM_TypesHandler", "-", debug);
    }
}

SCORM_TypesHandler.prototype.validate = function(value, type, subtype) {
    var method = "validate_" + type;
    try {
        return this[method](value, subtype);
    } catch(e) {
        var debug = method += '(' + value + ', ' + subtype + ')';
        SCORM_TypesHandler.debug(debug, 'validate error');
    }
}

SCORM_TypesHandler.prototype.checkInteger = function(value, signed) {
    if(signed) {
        return /^(\+|-)\d+$/.test(value);
    } else {
        return /^\d+$/.test(value);
    }
}
SCORM_TypesHandler.prototype.checkDecimal = function(value) {
    return /^-?(\d)+(\.)?(\d)*$/.test(value);
}
SCORM_TypesHandler.prototype.checkReal10_7 = function(value, subtype) {
    var re = new RegExp("^-?(\\d)+(\\.)?(\\d)*$");
	
	if( !re.test( value ) )
		return false;
	
	real = parseFloat(value.replace(/,/,'.'));
	switch( subtype ) { 
		case "0..1":
			if( real >= 0 && real <= 1 )
				return true;
			else
				return false;
			break;
		case "0..*":
			if( real >= 0 )
				return true;
			else
				return false;
			break;
		case "-1..1":
			if( real >= -1 && real <= 1 )
				return true;
			else
				return false;
			break;
		default:
			return true;
	}
}
SCORM_TypesHandler.prototype.checkIdentifier1 = function(value) {
    return /^\S+$/.test(value);
}
SCORM_TypesHandler.prototype.checkIdentifier2 = function(value, maxLength) {
	var re = new RegExp("^[^\-_.!~*'()]{0," + maxLength + "}$"); 
	return re.test(value);
}
SCORM_TypesHandler.prototype.convertInt = function(value) {
    return this.checkInteger(value, true) ? parseInt(value) : 0;
}
SCORM_TypesHandler.prototype.dateValid = function(Y, M, D) {
	var CD = new Date();
	if( Y<100 || Y>9999 ) { 
		D = 0;
	} else { 
		CD.setFullYear(Y, M-1, D);
	}
	return (( (CD.getMonth()+1) == M ) && ( CD.getDate() == D ));
}
SCORM_TypesHandler.prototype.timeValid = function(H, M, S) {
	var CT = new Date();
	CT.setHours(H);
	CT.setMinutes(M);
	CT.setSeconds(S);
	
	return (( CT.getMinutes() == M ) && ( CT.getSeconds() == S ));
}
SCORM_TypesHandler.prototype.checkTime1 = function(value) {
    return /^\d\d:\d\d:\d\d(($)|(\.\d{1,2}$))/.test(value);
}
SCORM_TypesHandler.prototype.checkTime2 = function(value) {
    var re = new RegExp( "^(\\d{4})(-(\\d{2})(-(\\d{2})(T(\\d{2})(:(\\d{2})(:(\\d{2})(\\.(\\d{1,2})(Z|(([+\\-])(\\d\\d):(\\d\\d)))?)?)?)?)?)?)?$" );
	//'			  		  0       1 2      3 4      5 6      7 8      9 0      1  2        3  45      6      7           
	
	var elems = re.exec( value );
	if( elems == null ) 
		return false;
	
	var st = new Object();
	
	st.m_year = this.convertInt(elems[1]);
	st.m_month = this.convertInt(elems[3]);
	st.m_day = this.convertInt(elems[5]);
	
	if( !this.dateValid( st.m_year, st.m_month, st.m_day ) ) 
		return false;
		
	st.m_hour = this.convertInt(elems[7]);
	st.m_minute = this.convertInt(elems[9]);
	st.m_second = this.convertInt(elems[11]);
	if( !this.timeValid( st.m_hour, st.m_minute, st.m_second ) )
		return false;
		
	st.m_cent = this.convertInt(elems[13]);
	
	if( elems[14] == "Z" || elems[16] == "" ) {
		st.m_tz_hour = 0;
		st.m_tz_minute = 0;
	} else {
		st.m_tz_hour = this.convertInt(elems[17]);
		st.m_tz_minute = this.convertInt(elems[18]);
		if( elems[16] == "-" ) {
			st.m_tz_hour = st.m_tz_hour*-1;
			st.m_tz_minute = st.m_tz_minute*-1;
		}
	}
	
	if( !this.timeValid( st.m_tz_hour, st.m_tz_minute, 0 ) )
		return false;
	
	return true;
}
SCORM_TypesHandler.prototype.checkTimespan = function(value) {
    return /^\d{2,4}:\d\d:\d\d(($)|(\.\d{1,2}$))/.test(value);
}
SCORM_TypesHandler.prototype.checkInterval = function(value) {
    var re = new RegExp("^P((\\d*)Y)?((\\d*)M)?((\\d*)D)?(T((\\d*)H)?((\\d*)M)?((\\d*)(\\.(\\d{1,2}))?S)?)?$");
	//   			       01       23       45       6 78       90       12    3  4

	var elems = re.exec( value );
	if( elems == null ) {
		return false;
	}
	
	var st = new Object(); 	
	st.m_year = this.convertInt(elems[2]);
	st.m_month = this.convertInt(elems[4]);
	st.m_day = this.convertInt(elems[6]);
	
	st.m_hour = this.convertInt(elems[9]);
	st.m_minute = this.convertInt(elems[11]);
	st.m_second = this.convertInt(elems[13]);
	
	st.m_cent = this.convertInt(elems[15]);

	var min = elems[9] + elems[11] + elems[13] + elems[15];
	//if there a T but there is no time, refuse the string
	if(value.indexOf("T") >= 0 && min.length == 0) return false;

	return true;
}
SCORM_TypesHandler.prototype.checkLanguage = function(value) {
    return /^\w{1,3}(-\w{1,8})?$/.test(value);
}
SCORM_TypesHandler.prototype.checkValueInList = function(value, list) {
    return list.indexOf(value) > -1;
}
SCORM_TypesHandler.prototype.checkMax = function(value, max) {
    return parseFloat(value) <= max;
}
SCORM_TypesHandler.prototype.checkMin = function(value, min) {
    return parseFloat(value) >= min;
}
SCORM_TypesHandler.prototype.checkMaxLength = function(value, max) {
    return value.toString().length <= max;
}
SCORM_TypesHandler.prototype.checkMinLength = function(value, min) {
    return value.toString().length >= min;
}


function SCORM_TypesHandler_12() {
    SCORM_TypesHandler.call(this);
    var _this = this;
    this.validate_CMIBlank = function(value) {
        return _this.checkValueInList(value, ['']);
    }
    this.validate_CMIBoolean = function(value) {
        return _this.checkValueInList(value, ['true', 'false']);
    }
    this.validate_CMIDecimal = function(value) {
        return _this.checkDecimal(value);
    }
    this.validate_CMIDecimal100 = function(value) {
        return _this.checkDecimal(value) && _this.checkMax(value, 100);
    }
    this.validate_CMIFeedback = function(value) {
        return _this.checkMaxLength(value, 255);
    }
    this.validate_CMIIdentifier = function(value) {
        return _this.checkIdentifier1(value);
    }
    this.validate_CMIInteger = function(value) {
        return _this.checkInteger(value, false) && _this.checkMax(value, 65535);
    }
    this.validate_CMISInteger = function(value) {
        return _this.checkInteger(value, true) && _this.checkMax(value, 32767) && _this.checkMin(value, -32767);
    }
    this.validate_CMIString = function(value, maxLength) {
        return _this.checkMaxLength(value, maxLength);
    }
    this.validate_CMIString255 = function(value) {
        return _this.checkMaxLength(value, 255);
    }
    this.validate_CMIString4096 = function(value) {
        return _this.checkMaxLength(value, 4096);
    }
    this.validate_CMITime = function(value) {
        return _this.checkTime1(value);
    }
    this.validate_CMITimespan = function(value) {
        return _this.checkTimespan(value);
    }
    this.validate_CMIVocabulary = function(value, vocabulary) {
        switch(vocabulary) {
            case 'Mode':            return _this.checkValueInList(value, ["normal", "review", "browse"]);
            case 'Status':          return _this.checkValueInList(value, ["passed", "completed", "failed", "incomplete", "browsed"]); // "not attempted" should only be set by the LMS 
            case 'Exit':            return _this.checkValueInList(value, ["time-out", "suspend", "logout", ""]);
            case 'Credit':          return _this.checkValueInList(value, ["credit", "no-credit"]);
            case 'Entry':           return _this.checkValueInList(value, ["ab-initio", "resume", ""]);
            case 'Interaction':     return _this.checkValueInList(value, ["true-false", "choice", "fill-in", "matching", "performance", "likert", "sequencing", "numeric"]);
            case 'Result':          return _this.checkDecimal(value) || _this.checkValueInList(value, ["correct", "wrong", "unanticipated", "neutral"]);
            case 'TimeLimitAction': return _this.checkValueInList(value, ["exit,message", "exit,no message", "continue,message", "continue,no message"]);
            default: return false;
        }
    }
}

SCORM_TypesHandler_12.prototype = Object.create(SCORM_TypesHandler.prototype);
SCORM_TypesHandler_12.version = function() { return '1.2'; };
SCORM_TypesHandler_12.prototype.constructor = SCORM_TypesHandler_12;


function SCORM_TypesHandler_13() {
    SCORM_TypesHandler.call(this);
    var _this = this;
    this.validate_characterstring = function(value, maxLength) {
        if(!maxLength && maxLength !== 0) {
            maxLength = 4000;
        }
        return _this.checkMaxLength(value, maxLength);
    }
    this.validate_localized = function(value, maxLength) {
        return _this.checkMaxLength(value, maxLength);
    }
    this.validate_language = function(value) {
        return _this.checkLanguage(value);
    }
    this.validate_longidentifier = function(value) {
        return _this.checkIdentifier2(value, 4000);
    }
    this.validate_shortidentifier = function(value) {
        return _this.checkIdentifier2(value, 250);
    }
    this.validate_time = function(value) {
        return _this.checkTime2(value);
    }
    this.validate_timeinterval = function(value) {
        return _this.checkInterval(value);
    }
    this.validate_real10_7 = function(value, subtype) {
        return _this.checkReal10_7(value, subtype);
    }
    this.validate_state = function(value, vocabulary) {
        switch(vocabulary) {
            case 'completion_status':   return _this.checkValueInList(value, ["completed", "incomplete", "not attempted", "unknown"]);
            case 'credit':              return _this.checkValueInList(value, ["credit", "no-credit"]);
            case 'entry':               return _this.checkValueInList(value, ["ab-initio", "resume", ""]);
            case 'exit':                return _this.checkValueInList(value, ["time-out", "suspend", "logout", "normal", ""]);
            case 'interaction_type':    return _this.checkValueInList(value, ["true-false", "choice", "fill-in", "long-fill-in",  "matching", "performance", "sequencing", "likert", "numeric", "other"]);
            case 'interaction_result':  return _this.checkReal10_7(value) || _this.checkValueInList(value, ["correct", "incorrect", "wrong", "unanticipated", "neutral"]);
            case 'audio_captioning':    return _this.checkValueInList(value, ["-1", "0", "1"]);
            case 'mode':                return _this.checkValueInList(value, ["normal", "review", "browse"]);
            case 'success_status':      return _this.checkValueInList(value, ["passed", "failed", "unknown"]);
            case 'time_limit_action':   return _this.checkValueInList(value, ["exit,message", "exit,no message", "continue,message", "continue,no message"]);
            default: return false;
        }
    }
}

SCORM_TypesHandler_13.prototype = Object.create(SCORM_TypesHandler.prototype);
SCORM_TypesHandler_13.version = function() { return '1.3'; };
SCORM_TypesHandler_13.prototype.constructor = SCORM_TypesHandler_13;
