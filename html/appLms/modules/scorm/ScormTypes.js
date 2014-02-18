/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */
/*
 * @module ScormTypes.js
 * check for scorm data types
 * javascript SCORM API 1.2/1.3
 * @author Emanuele Sandri
 * @date	05/03/2008
 * @version $Id$
 */

function scormTypes_checkLongIdentifierType( value ) {
	return /^\S+$/.test(value);
}

function ConvertDbl( text ) { 
	if(text.replace === undefined) return text;
	return parseFloat(text.replace(/,/,'.'))
}

function scormTypes_checkCMIBlank( value ) {
	return (value == '');
}

function scormTypes_checkCMIIdentifier( value ) {
	return /^\S+$/.test(value);
}

function scormTypes_checkReal( specs, value ) {
	var arrSpecs = specs.split(',');
	var base = arrSpecs[0];
	var nDecimal = arrSpecs[1]; 
	
	var re = new RegExp('^(\\d)+(\\.)?(\\d){0,'+nDecimal+'}$');
	
	if (re.test(value)) {
		if(arrSpecs.length > 2) {
			var f = parseFloat(value);
			if( f >= arrSpecs[2] && f <= arrSpecs[3] ) 
				return true;
			else
				return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

function scormTypes_checkInteger( value ) {
	return /^\d+$/.test(value);
}

function scormTypes_checkCMIInteger( value ) {
	if( /^\d+$/.test(value) ) {
		var i = parseInt(value);
		if( i < 65536 ) 
			return true;
		else 
			return false
	} else
		return false;
}

function scormTypes_checkCMISInteger( value ) {
	if( /^(\+|-)\d+$/.test(value) ) {
		var i = parseInt(value);
		if( i > -32768 && i < 32768 ) 
			return true;
		else 
			return false
	} else
		return false;
}

function scormTypes_checkCMIDecimal( value ) {
	return /^-?(\d)+(\.)?(\d)*$/.test(value);
} 

function scormTypes_checkCMIDecimal100( value ) {
	if( /^(\d)+(\.)?(\d)*$/.test(value) ) {
		var f = parseFloat(value);
		if( f <= 100 ) 
			return true;
		else
			return false;
	} else
		return false;
}

function scormTypes_checkCMITime( value ) {
	return /^\d\d:\d\d:\d\d(($)|(\.\d{1,2}$))/.test( value );
} 

function scormTypes_checkCMITimespan( value ) {
	return /^\d{2,4}:\d\d:\d\d(($)|(\.\d{1,2}$))/.test(value);
}

function scormTypes_checkVocabulary12( vocabularyType, value ) {
	var entries = null;
	
	switch( vocabularyType ) {
		case "Boolean":
			entries = new Array("true", "false");
			break;
		case "Mode":
			entries = new Array("normal", "review", "browse");
			break;
		case "Status":
			entries = new Array("passed", "completed", "failed", "incomplete", "browsed", "not attempted", "ab-inito");
			break;
		case "Exit":
			entries = new Array("time-out","suspend","logout","");
			break;
		case "Credit":
			entries = new Array("credit","no-credit");
			break;
		case "Entry":
			entries = new Array("ab-initio","resume","");
			break;
		case "Interaction":
			entries = new Array("true-false","choice","fill-in","matching","performance","likert","sequencing","numeric");
			break;
		case "Result":
			if( scormTypes_checkCMIDecimal(value) )
				entries = new Array(value);
			else
				entries = new Array("correct","wrong","unanticipated","neutral");
			break;
		case "Time Limit Action":
			entries = Array("exit,message","exit,no message","continue,message","continue,no message");
			break;
	}

	if( entries !== null ) {
		for( var i = 0; i < entries.length; i++ ) {
			if( entries[i] == value ) 
				return true;
		}
	}
	return false;
}

function scormTypes_checkLen( maxlen, value ) { 
	return (value.length <= maxlen);
}

function scormTypes_characterstring( maxlen, value ) { 
	if( typeof(maxlen) == 'undefined' || maxlen === null || maxlen === '' ) 
		maxlen = 4000;
	return scormTypes_checkLen( maxlen, value);
}

function scormTypes_language_type( value ) {
	return /^\w{1,3}(-\w{1,8})?$/.test(value);
}

function scormTypes_localized_string_type( maxlen, value ) { 
	return scormTypes_checkLen( maxlen, value);
}

function scormTypes_itentifier_type( maxlen, value ) {
	var re = new RegExp("^[^\-_.!~*'()]{0," + maxlen + "}$"); 
	return re.test( value );
}

function convertInt( value ) {
	if( /^(\+|-)?\d+$/.test(value) ) {
		return parseInt(value);
	} else {
		return 0;
	}
}

function DateValid( Y, M, D ) {
	var CD = new Date();
	if( Y<100 || Y>9999 ) { 
		D = 0;
	} else { 
		CD.setFullYear(Y, M-1, D);
	}
	return (( (CD.getMonth()+1) == M ) && ( CD.getDate() == D ));
}

function TimeValid( H, M, S ) {
	var CT = new Date();
	CT.setHours(H);
	CT.setMinutes(M);
	CT.setSeconds(S);
	
	return (( CT.getMinutes() == M ) && ( CT.getSeconds() == S ));
}

function scormTypes_parseTimeinterval( value ) { 
	
	var re = new RegExp("^P((\\d*)Y)?((\\d*)M)?((\\d*)D)?(T((\\d*)H)?((\\d*)M)?((\\d*)(\\.(\\d{1,2}))?S)?)?$");
	//   			       01       23       45       6 78       90       12    3  4

	var elems = re.exec( value );
	if( elems == null ) {
		return null;
	}
	
	var st = new Object(); 	
	st.m_year = convertInt(elems[2]);
	st.m_month = convertInt(elems[4]);
	st.m_day = convertInt(elems[6]);
	
	st.m_hour = convertInt(elems[9]);
	st.m_minute = convertInt(elems[11]);
	st.m_second = convertInt(elems[13]);
	
	st.m_cent = convertInt(elems[15]);

	var min = elems[9] + elems[11] + elems[13] + elems[15];
	//if there a T but there is no time, refuse the string
	if(value.indexOf("T") >= 0 && min.length == 0) return null;

	return st;
}

function timeCInt( value, prefix, suffix ) { 
	if( value == 0 ) {
		return "";
	} else {
		return prefix + value + suffix;
	}
}

function scormTypes_timeinterval_toString( st ) {
	var strDate = '';
	var strTime = '';
	strDate = timeCInt( st.m_year, "", "Y" )
			+ timeCInt( st.m_month, "", "M" )
			+ timeCInt( st.m_year, "", "D" );
	strTime = timeCInt( st.m_hour, "", "H" )
			+ timeCInt( st.m_minute, "", "M" )
			+ timeCInt( st.m_second, "", timeCInt( st.m_cent, ".", "" ) + "S" );
	var strTot = "P";
	if( strDate == "" && strTime == "") {
		strTot += "T0H0M0S";
	} else {
		if( strDate != "" ) {
			strTot += strDate;
		}
		if( strTime != "" ) {
			strTot += "T" + strTime;
		}
	}
	return strTot;
}

function scormTypes_timeinterval_sum( st1, st2 ) {
	var stSum = new Object();
	
	stSum.m_year	= st1.m_year	+ st2.m_year;
	stSum.m_month	= st1.m_month	+ st2.m_month;
	stSum.m_day		= st1.m_day		+ st2.m_day;
	stSum.m_hour	= st1.m_hour	+ st2.m_hour;
	stSum.m_minute	= st1.m_minute	+ st2.m_minute;
	stSum.m_second	= st1.m_second	+ st2.m_second;
	stSum.m_cent	= st1.m_cent	+ st2.m_cent;
	
	return stSum;
}

function sumScormTimeintervalText( t1, t2 ) {
	var st1 = scormTypes_parseTimeinterval(t1);
	if( st1 == null ) {
		return '';
	}
	var st2 = scormTypes_parseTimeinterval(t2);
	if( st2 == null ) {
		return '';
	}
	var stSum = scormTypes_timeinterval_sum( st1, st2 );
	
	return scormTypes_timeinterval_toString(stSum);
}

function scormTypes_parseTime( value ) {

	var re = new RegExp( "^(\\d{4})(-(\\d{2})(-(\\d{2})(T(\\d{2})(:(\\d{2})(:(\\d{2})(\\.(\\d{1,2})(Z|(([+\\-])(\\d\\d):(\\d\\d)))?)?)?)?)?)?)?$" );
	//'			  		  0       1 2      3 4      5 6      7 8      9 0      1  2        3  45      6      7           
	
	var elems = re.exec( value );
	if( elems == null ) 
		return null;
	
	var st = new Object();
	
	st.m_year = convertInt(elems[1]);
	st.m_month = convertInt(elems[3]);
	st.m_day = convertInt(elems[5]);
	
	if( !DateValid( st.m_year, st.m_month, st.m_day ) ) 
		return null;
		
	st.m_hour = convertInt(elems[7]);
	st.m_minute = convertInt(elems[9]);
	st.m_second = convertInt(elems[11]);
	if( !TimeValid( st.m_hour, st.m_minute, st.m_second ) )
		return null;
		
	st.m_cent = convertInt(elems[13]);
	
	if( elems[14] == "Z" || elems[16] == "" ) {
		st.m_tz_hour = 0;
		st.m_tz_minute = 0;
	} else {
		st.m_tz_hour = convertInt(elems[17]);
		st.m_tz_minute = convertInt(elems[18]);
		if( elems[16] == "-" ) {
			st.m_tz_hour = st.m_tz_hour*-1;
			st.m_tz_minute = st.m_tz_minute*-1;
		}
	}
	
	if( !TimeValid( st.m_tz_hour, st.m_tz_minute, 0 ) )
		return null;
	
	return st;
}

function scormTypes_time( subType, value ) { 
	var st = scormTypes_parseTime(value);
	return !( st == null );
}

function scormTypes_timeinterval( subType, value ) { 
	var st = scormTypes_parseTimeinterval(value);
	return !( st == null );
}

function scormTypes_real10_7( subType, value ) {

	var re = new RegExp("^-?(\\d)+(\\.)?(\\d)*$");
	
	if( !re.test( value ) )
		return false;
	
	real = ConvertDbl(value);
	switch( subType ) { 
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

function scormTypes_checkVocabulary13( vocabularyType, value ) {
	var entries = null;
	switch (vocabularyType) {
		case "completion_status":
			entries = new Array("completed", "incomplete", "not attempted", "unknown" );
			break;
		case "credit":
			entries = new Array("credit","no-credit");
			break;
		case "entry":
			entries = new Array("ab-initio","resume","");
			break;
		case "exit":
			entries = new Array("time-out","suspend","logout","normal","");
			break;
		case "interaction_type":
			entries = new Array("true-false","choice","fill-in","long-fill-in", "matching","performance","sequencing","likert","numeric","other");
			break;			
		case "interaction_result":
			if( scormTypes_real10_7( "", value ) )
				entries = new Array(value);
			else
				entries = new Array("correct","incorrect","wrong","unanticipated","neutral");
			break;
		case "audio_captioning":
			entries = new Array("-1","0","1");
			break;
		case "mode":
			entries = new Array("normal", "review", "browse");
			break;
		case "success_status":
			entries = new Array("passed", "failed", "unknown");
			break;
		case "time_limit_action":
			entries = new Array("exit,message","exit,no message","continue,message","continue,no message");
			break;
	}

	result = false;
	if( entries != null ) {
		for( var i = 0; i < entries.length; i++ ) {
			if( entries[i] == value )
				return true;
		}
	}

	return result;
}


function checkScormType( scormType, scormSubType, value, scormVersion ) {
	switch( scormType ) {
		case "CMIBlank":
			return scormTypes_checkCMIBlank( value );
			break;
		case "CMIBoolean":
			return scormTypes_checkVocabulary( "Boolean", value );
			break;
		case "CMIDecimal":
			return scormTypes_checkCMIDecimal( value );
			break;
		case "CMIDecimal100":
			return scormTypes_checkCMIDecimal100( value );
			break;
		case "CMIFeedback":
			return scormTypes_checkLen( 255, value );
			break;
		case "CMIIdentifier":
			return scormTypes_checkCMIIdentifier( value );
			break;
		case "CMIInteger":
			return scormTypes_checkCMIInteger( value );
			break;
		case "CMISInteger":
			return scormTypes_checkCMISInteger( value );
			break;
		case "CMIString":
			return scormTypes_checkLen( scormSubType, value );
			break;
		case "CMIString255":
			return scormTypes_checkLen( 255, value );
			break;
		case "CMIString4096":
			return scormTypes_checkLen( 4096, value );
			break;
		case "CMITime":
			return scormTypes_checkCMITime( value );
			break;
		case "CMITimespan":
			return scormTypes_checkCMITimespan( value );
			break;
		case "CMIVocabulary":
			return scormTypes_checkVocabulary12( scormSubType, value);
			break;
		// ------- types for scorm 1.3
		case "state":
			return scormTypes_checkVocabulary13( scormSubType, value);
			break;
		case "characterstring":
			return scormTypes_characterstring( scormSubType, value );
			break;
		case "localized_string_type":
			return scormTypes_localized_string_type( scormSubType, value );
			break;
		case "scormTypes_language_type":
			return scormTypes_language_type( value );
			break;
		case "long_identifier_type":
			return scormTypes_itentifier_type( 4000, value );
			break;
		case "short_identifier_type":
			return scormTypes_itentifier_type( 250, value );
			break;
		case "time":
			return scormTypes_time( scormSubType, value );
			break;
		case "timeinterval":
			return scormTypes_timeinterval( scormSubType, value );
			break;
		case "real10_7":
			return scormTypes_real10_7( scormSubType, value );
			break;
		default:
			return false;
			break;
	}
}