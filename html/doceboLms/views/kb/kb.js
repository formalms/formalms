
var cur_folder_name ='';

// --- Folder / folder change related methods --------------------------

function get_id_by_item_id(value, prefix) {
	var pos = prefix.length;
	return value.substr(pos);
}

YAHOO.util.Event.onDOMReady(function () {

	if (YAHOO.util.Dom.get("filter_text").value != '') {
		KbManagement.filter_text =YAHOO.util.Dom.get("filter_text").value;
	}

	YAHOO.util.Event.delegate("folder_box", "click", function(e, matchedEl, container) {
		YAHOO.util.Event.preventDefault(e);
		selectKbFolderFromEl(matchedEl);
	}, "a" );
	YAHOO.util.Event.delegate("left_categories", "click", function(e, matchedEl, container) {
		YAHOO.util.Event.preventDefault(e);
		selectKbFolderFromEl(matchedEl);
	}, "a" );
	YAHOO.util.Event.delegate("kb_folder_nav", "click", function(e, matchedEl, container) {
		YAHOO.util.Event.preventDefault(e);
		selectKbFolderFromEl(matchedEl);
	}, "a" );

	YAHOO.util.Event.delegate("kb_tag_cloud", "click", function(e, matchedEl, container) {
		YAHOO.util.Event.preventDefault(e);
		yuiLogMsg("Clicked item: " + matchedEl.innerHTML);

		var tag ='"' + matchedEl.innerHTML + '"';
		var search_txt =YAHOO.util.Dom.get("filter_text").value;
		if (search_txt.indexOf(tag) > -1) {
			var pos =search_txt.indexOf(tag);
			var len =tag.length;
			var p1 =search_txt.substr(0, pos-(pos > 0 ? 1 : 0));
			var p2 =search_txt.substr(search_txt.indexOf(tag)+len+(pos+len < search_txt.length && pos == 0 ? 1 : 0));
			search_txt =p1+p2;
		} else if (search_txt != '') {
			search_txt+=' '+tag;
		} else {
			search_txt =tag;
		}
		YAHOO.util.Dom.get("filter_text").value =search_txt;
		applyKbSearchFilter();
	}, "a" );

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("course_filter").value = "-1";
		YAHOO.util.Dom.get("filter_text").value ='';
		applyKbSearchFilter();
	});
	
	YAHOO.util.Event.addListener("quick_search", "submit", function(e) {
		YAHOO.util.Event.preventDefault(e);
		applyKbSearchFilter();
	});

	YAHOO.util.Event.addListener("course_filter", "change", function(e) {
		KbManagement.course_filter =YAHOO.util.Dom.get("course_filter").value;
		DataTable_kb_table.refresh();
	});
	
	selectKbFolder(0);
});

function selectKbFolderFromEl(matchedEl) {
	yuiLogMsg("Clicked item: " + matchedEl.id);

	var folder_id =get_id_by_item_id(matchedEl.id, 'folder_');
	cur_folder_name =(folder_id > 0 ? matchedEl.innerHTML : kb_lang['all_folders']);
	selectKbFolder(folder_id);
}

function selectKbFolder(folder_id) {
	sUrl = ajax_url_select_folder + folder_id;

	var callback = {
		success: function(o) {
			var data =YAHOO.lang.JSON.parse(o.responseText);
			YAHOO.util.Dom.get("kb_folder_nav").innerHTML = data['breadcrumbs'];
			YAHOO.util.Dom.get("kb_folder_box_ul").innerHTML = data['folder_box'];
			var disp = (data['folder_box'] == '' ? 'hidden' : 'visible');
			YAHOO.util.Dom.setStyle("folder_box", 'visibility', disp);
			KbManagement.selected_node = o.argument.folder_id;
			updateSearchLabel();
			DataTable_kb_table.refresh();
		},
		failure:function(o) {
			yuiLogMsg('Error ('+sUrl+')');
		},
		argument: {
			"folder_id":folder_id
		}
	};
	YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
}

function showFolderNumbers(show) {
	YAHOO.util.StyleSheet('kb_folder').unset('.kb_folder_tot','display');
	if (show) {
		YAHOO.util.StyleSheet('kb_folder').set('.kb_folder_tot', {
			visibility: 'visible'
		});
	} else {
		YAHOO.util.StyleSheet('kb_folder').set('.kb_folder_tot', {
			visibility: 'hidden'
		});
	}
}

// --- Search related ----------------------------------------------------------
function applyKbSearchFilter() {

	var search_txt =YAHOO.util.Dom.get("filter_text").value;
	KbManagement.filter_text =search_txt;
	DataTable_kb_table.refresh();

	var searching =(search_txt != '' ? true : false);
	showFolderNumbers(!searching);
	showSearchLabel(searching, search_txt);
}

function updateSearchLabel() {
	var search_txt =YAHOO.util.Dom.get("filter_text").value;
	var searching =(search_txt != '' ? true : false);
	showSearchLabel(searching, search_txt);
}

function showSearchLabel(searching, txt) {

	if (cur_folder_name == '') {
		cur_folder_name =kb_lang['all_folders'];
	}
}

// --- Datatable related -------------------------------------------------------
YAHOO.namespace("KbManagement");

var KbManagement = {
	selected_node: 0,
	filter_text :"",
	course_filter :-1,

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;

		oState = oState || {
			pagination: null,
			sortedBy: null
		};

		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

		return  "&results="	+ results +
			"&startIndex=" + startIndex +
			"&sort=" + sort +
			"&dir=" + dir+
			"&folder_id="+KbManagement.selected_node+
			"&filter_text="+KbManagement.filter_text+
			"&course_filter="+KbManagement.course_filter;
	}
}

function frm_edit(elLiner, oRecord, oColumn, oData) {
	var r_name = oRecord.getData("r_name");
	elLiner.innerHTML = '<a id="frm_edit_'+oRecord.getData("res_id")+'" class="ico-sprite subs_mod" '
		+' href="index.php?r=alms/kb/edit&amp;id='+oRecord.getData("res_id")+'" title="'+kb_lang._MOD+':'+r_name+'">'
		+'<span>'+kb_lang._MOD+'</span></a>'	;
}

function fv_switch(elLiner, oRecord, oColumn, oData) {
	var r_name = oRecord.getData("r_name");
	var title = (oData>0 ? xx : xx);
	elLiner.innerHTML = '<a id="fv_switch_'+oRecord.getData("res_id")+'" class="ico-sprite subs_'+(oData>0 ? 'actv' : 'noac')+'" '
		+' href="ajax.adm_server.php?r=alms/kb/fvSwitch&id='+oRecord.getData("res_id")+'&is_active='+(oData)+'" '
		+' onclick="javascript:svSwitch(this); return false;" title="'+title+':'+r_name+'">'
		+'<span>'+title+'</span></a>'	;
}

function frm_play(elLiner, oRecord, oColumn, oData) {
	var r_name = oRecord.getData("r_name");
	var extra ='';
	if (oRecord.getData("r_type") == 'scoitem' || oRecord.getData("r_type") == 'scorm') {
		extra =' rel="lightbox"';
	}
	elLiner.innerHTML = '<a'+extra+' id="frm_play_'+oRecord.getData("res_id")+'" class="ico-sprite subs_play" '
	+' href="index.php?r=kb/play&amp;id='+oRecord.getData("res_id")+'" title="'+kb_lang['title_play']+': '+r_name+'">'
	+'<span>'+kb_lang['title_play']+'</span></a>'	;
}