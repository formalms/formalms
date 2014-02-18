

var handle_competences =false;
var handle_courses =false;
var sel_item_id =0;
var sel_item_type ='';


YAHOO.util.Event.onDOMReady(function() {

	drawCourseList();
	drawCompetenceList();
	

	YAHOO.util.Event.addListener("link_add_competence", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		addCompetence();
	});

	YAHOO.util.Event.addListener("link_add_course", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		addCourse();
	});

	YAHOO.util.Event.addListener("main_form", "submit", function(e) {
		if (handle_competences) {
			YAHOO.util.Event.preventDefault(e);
			addCompetence();
		}
		if (handle_courses) {
			YAHOO.util.Event.preventDefault(e);
			addCourse();
		}
		YAHOO.util.Dom.get("competences_list").value =YAHOO.lang.JSON.stringify(competence_arr);
		YAHOO.util.Dom.get("courses_list").value =YAHOO.lang.JSON.stringify(course_arr);
	});

	YAHOO.util.Event.on("input_add_competence", "focus", function(e) {
		handle_competences =true;
	});

	YAHOO.util.Event.on("input_add_competence", "blur", function(e) {
		handle_competences =false;
	});

	YAHOO.util.Event.on("input_add_course", "focus", function(e) {
		handle_courses =true;
	});

	YAHOO.util.Event.on("input_add_course", "blur", function(e) {
		handle_courses =false;
	});
	
});


function addCompetence() {
	new_value =YAHOO.util.Dom.get("input_add_competence").value;
	if(new_value != '' && sel_item_id) {
		YAHOO.util.Dom.get("input_add_competence").value ='';
		competence_arr[sel_item_id]={'id': sel_item_id, 'type': sel_item_type, 'title': new_value};
		sel_item_id = null;
	}
	drawCompetenceList();
}


function addCourse() {
	new_value =YAHOO.util.Dom.get("input_add_course").value;
	if(new_value != '' && sel_item_id) {
		YAHOO.util.Dom.get("input_add_course").value ='';
		course_arr[sel_item_id]={'id': sel_item_id, 'title': new_value};
		sel_item_id = null;
	}
	drawCourseList();
}


// ----------- box autocomplete --------------------------------

YAHOO.util.Event.onDOMReady(function() {

	CompetenceAC = function() {
		// Use a LocalDataSource
		var oDS = new YAHOO.util.XHRDataSource(competence_ac_url);
		oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
		oDS.responseSchema = {
				resultsList : "competences",
				fields: ["name", "id_competence", "name_highlight", "type", "typology"]
		};

		// Instantiate the AutoComplete
		var oAC = new YAHOO.widget.AutoComplete("input_add_competence", "box_autocomplete_competence", oDS);
		oAC.generateRequest = function(sQuery) {return "&results=20&query=" + sQuery;};
		//oAC.delimChar = [","];
		oAC.resultTypeList = false;
		oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
			return oResultData.name_highlight;
		};
		oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
			sel_item_id =oArgs[2].id_competence;
			sel_item_type =oArgs[2].type;
		});
		oAC.prehighlightClassName = "yui-ac-prehighlight";
		oAC.useShadow = true;
	}();

	CourseAC = function() {
		// Use a LocalDataSource
		var oDS = new YAHOO.util.XHRDataSource(course_ac_url);
		oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
		oDS.responseSchema = {
				resultsList : "courses",
				fields: ["cname", "id_course", "code", "name", "code_highlight", "name_highlight"]
			};

		// Instantiate the AutoComplete
		var oAC = new YAHOO.widget.AutoComplete("input_add_course", "box_autocomplete_course", oDS);
		oAC.generateRequest = function(sQuery) {return "&results=20&query=" + sQuery;};
		//oAC.delimChar = [","];
		oAC.resultTypeList = false;
		oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
			return (oResultData.code_highlight != "" ? '['+oResultData.code_highlight+'] ' : '')+oResultData.name_highlight;
		};
		oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
			sel_item_id =oArgs[2].id_course;
		});
		oAC.prehighlightClassName = "yui-ac-prehighlight";
		oAC.useShadow = true;
	}();

});


// -----------------------------------------------------------------------------


function drawCourseList() {
	var res ='';
	var list ='';
	var arr_id=[];

	for(i in course_arr) {
		list+='<li>';
		list+='<div style="margin-left: 1em; display:-moz-inline-box;display:inline-block;">';
		list+='<a id="del_course_'+course_arr[i]['id']+'" class="ico-wt-sprite subs_del" href="" title="'+lang['remove_item']+'"><span></span></a></div>';
		list+=course_arr[i]['title']+'</li>';
		arr_id.push(course_arr[i]['id']);
	}

	if (list != '') {
		res+='<ul class="link_list">'+list+'</ul>';
	}

	YAHOO.util.Dom.get("course_box").innerHTML =res;
	attachCourseEvents(arr_id);
}


function drawCompetenceList() {
	var res ='';
	var list ='';
	var arr_id=[];
	var with_score_id=[];

	for(i in competence_arr) {
		list+='<li>';
		list+='<div style="margin-left: 1em; display:-moz-inline-box;display:inline-block;">';
		list+='<a id="del_competence_'+competence_arr[i]['id']+'" class="ico-wt-sprite subs_del" href="#" title="'+lang['remove_item']+'"><span></span></a></div>';
		list+='<div style="text-align: right; margin-right: 1em; display:-moz-inline-box;display:inline-block; width: 60px;">';
		if (competence_arr[i]['type'] == 'score') {
			var score =(competence_arr[i]['score'] != null ? competence_arr[i]['score'] : 0);
			list+='<input id="competence_score_'+competence_arr[i]['id']+'" type="text" size="2" style="width: 85%;" value="'+score+'" />';
			with_score_id.push(competence_arr[i]['id']);
		}
		else {
			list+='<input type="checkbox" disabled="disabled" checked="checked" value="1" />';
		}
		list+='</div>';
		list+=competence_arr[i]['title'];		
		list+='</li>';
		arr_id.push(competence_arr[i]['id']);
	}

	if (list != '') {
		res+='<ul class="link_list">'+list+'</ul>';
	}

	YAHOO.util.Dom.get("competence_box").innerHTML =res;
	attachCompetenceEvents(arr_id, with_score_id);
}


function rem_from_obj(id, arr) {
	var new_arr ={};
	for(i in arr) {
		if (i != id) {
			new_arr[i]=arr[i];
		}
	}
	return new_arr;
}


function attachCourseEvents(arr_id) {
	var nodes = YAHOO.util.Selector.query('a[id^=del_course_]');
	YAHOO.util.Event.purgeElement(nodes);
	for (var i=0; i<arr_id.length; i++) {
			YAHOO.util.Event.on('del_course_'+arr_id[i], "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				var item_id =this.id.substr('del_course_'.length);
				delete course_arr[item_id];
				drawCourseList();
		});
	}
}


function attachCompetenceEvents(arr_id, with_score_id) {
	var nodes = YAHOO.util.Selector.query('a[id^=del_competence_]');
	YAHOO.util.Event.purgeElement(nodes);
	for (var i=0; i<arr_id.length; i++) {
			YAHOO.util.Event.on('del_competence_'+arr_id[i], "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				var item_id =this.id.substr('del_competence_'.length);
				delete competence_arr[item_id];
				drawCompetenceList();
		});
	}
	nodes = YAHOO.util.Selector.query('a[id^=competence_score_]');
	YAHOO.util.Event.purgeElement(nodes);
	for (i=0; i<with_score_id.length; i++) {
			YAHOO.util.Event.on('competence_score_'+arr_id[i], "blur", function(e) {
				YAHOO.util.Event.preventDefault(e);
				var item_id =this.id.substr('competence_score_'.length);
				var score =YAHOO.util.Dom.get('competence_score_'+item_id).value;
				competence_arr[item_id]['score']=score;
		});
	}
}


// -----------------------------------------------------------------------------
