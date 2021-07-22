<?php
echo getTitleArea(array(
	'index.php?r=alms/communication/show' => Lang::t('_COMMUNICATIONS', 'communication'),
	Lang::t('_ADD', 'communication')
));
?>
<div class="std_block">
<?php
echo Form::openForm('add_comm', 'index.php?r=alms/communication/insert', false, false, 'multipart/form-data')

	.Form::getHidden('title', 'title', 255, Get::req('title', DOTY_MIXED, $data['title']) )

	.Form::openElementSpace()
	.Form::getTextfield(Lang::t('_TITLE', 'communication'), 'title', 'title', 255, Get::req('title', DOTY_MIXED, $data['title']) )
	.Form::getDatefield(Lang::t('_DATE', 'communication'), 'publish_date', 'publish_date', Get::req('publish_date', DOTY_MIXED, $data['publish_date']) )
	.Form::getRadioSet(Lang::t('_TYPE', 'communication'), 'type_of', 'type_of', array(
		Lang::t('_NONE', 'communication') => 'none',
		Lang::t('_LONAME_item', 'storage') => 'file',
		Lang::t('_LONAME_scormorg', 'storage') => 'scorm',
	), Get::req('type_of', DOTY_STRING, $data['type_of']) )
	.Form::getTextarea(Lang::t('_DESCRIPTION', 'communication'), 'description', 'description', Get::req('description', DOTY_MIXED, $data['description']) )

	.'<div class="quick_search_form qsf_left">'
	.'<div class="form_line_l">'
	.'<label class="label_effect" for="set_course">'.Lang::t('_COURSE', 'course').'</label>&nbsp;'
	.Form::getInputTextfield('search_t', 'set_course', 'set_course', '', '', 255, '')
	.'<div id="set_course_container"></div>'
	.'</div>'
	.'</div>'

	.Form::getHidden('id_course', 'id_course', $data['id_course'])
	.Form::getHidden('id_category', 'id_category', $data['id_category'])

	.Form::closeElementSpace()

	.Form::openButtonSpace()
	.Form::getButton('save', 'save', Lang::t('_SAVE', 'communication') )
	.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'communication') )
	.Form::closeButtonSpace()

	.Form::closeForm();
?>
</div>
<script type="text/javascript">
//courses autocomplete
YAHOO.util.Event.onDOMReady(function() {
	var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?mn=course&plf=lms&op=course_autocomplete');
	oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
	oDS.responseSchema = {
		resultsList : "courses",
		fields: ["cname", "id_course", "code", "name", "code_highlight", "name_highlight", "has_editions", "editions", "has_classrooms", "classrooms"]
	};

	var oAC = new YAHOO.widget.AutoComplete("set_course", "set_course_container", oDS);
	oAC.forceSelection = true;
	oAC.useShadow = true;
	oAC.resultTypeList = false;
	oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
		return (oResultData.code_highlight != "" ? '['+oResultData.code_highlight+'] ' : '')+oResultData.name_highlight;
	};
	oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
		var D = YAHOO.util.Dom;
		D.get('id_course').value = oArgs[2].id_course;
	});
	oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

	YAHOO.util.Event.addListener('set_course', "keypress", function(e) {
		switch (YAHOO.util.Event.getCharCode(e)) {
			case 13: {
					this.submit();
				}break;
		}
	}, this, true);
});
</script>