

<div id="test_container">
<script type="text/javascript">var tree_categorize =true;</script>

<?php
$title_arr=array();
$title_arr[$back_url] = Lang::t('_CONTENT_LIBRARY', 'kb');//$data['original_name'];
$title_arr[] = Lang::t('_CATEGORIZE', 'kb').': '.$data['original_name'];
echo getTitleArea($title_arr);
?>


<div class="std_block">

<?php echo Form::openForm('add_res', $form_url, false, false, 'multipart/form-data'); ?>

<div class="panel_left_small">
<?php
/**
 * Tree
 */
 
$root_node_actions ='';
 
$languages = array(
	'_ROOT' => Get::sett('title_kb_tree', Lang::t('_ALL_CATEGORIES', 'kb') ),
	'_YES' => Lang::t('_CONFIRM', 'kb'),
	'_NO' => Lang::t('_UNDO', 'kb'),
	'_LOADING' => Lang::t('_LOADING', 'standard'),
	'_NEW_FOLDER_NAME' => Lang::t('_ORGCHART_ADDNODE', 'kb'),
	'_AREYOUSURE'=> Lang::t('_AREYOUSURE', 'kb'),
	'_NAME' => Lang::t('_NAME', 'standard'),
	'_MOD' => Lang::t('_MOD', 'standard'),
	'_DEL' => Lang::t('_DEL', 'standard')
);


$this->widget('tree', array(
	'id' => 'kbcategorizetree',
	'ajaxUrl' => 'ajax.adm_server.php?r=alms/kb/gettreedata&show_actions=0&from_widget=1',
	'treeClass' => 'KbFolderTree',
	'treeFile' => Get::rel_path('lms').'/admin/views/kb/kbfoldertree.js',
	'languages' => $languages,
	'initialSelectedNode' => (int)$selected_node,
	'rootActions' => array(),
	'show' => 'tree',
	'useCheckboxes' => 'true',
	'initialSelectorData' => $c_folders,
	'hiddenSelection' => 'h_selected_folders',
));

?>
</div>


<div class="panel_right_big">
<?php

$all_languages = Docebo::langManager()->getAllLangCode();
$all_languages_id =array_flip($all_languages);
$sel_lang =$all_languages_id[$data["r_lang"]];

if ($data['r_type'] == 'scorm') {
	echo '<div class="align-right">';
	echo '<a href="#" id="subcategorize_switch" class="ico-wt-sprite subs_del"><span>'.
		Lang::t('_CATEGORIZE_OBJECT_ITEMS', 'kb').'</span></a>';
	echo "</div>\n";

	$body =Form::openForm('add_res', $form_url)
		.Form::getHidden('subcategorize_switch', 'subcategorize_switch', '1')
		.Form::getHidden('org_categorize_switch_subcat', 'org_categorize_switch_subcat', '1');
	if (!empty($form_extra_hidden)) {
		foreach($form_extra_hidden as $field_id=>$val) {
			$body.=Form::getHidden($field_id, $field_id, $val);
		}
	}
	$body.=Form::closeForm();
	$body.=Lang::t('_YOU_WILL_LOSE_PREVIOUS_CATEGORIZATION', 'kb');

	$this->widget('dialog', array(
		'id' => 'subcategorize_switch_dialog',
		'dynamicContent' => false,
		'dynamicAjaxUrl' => false,
		'directSubmit'=>true,
		'header' => Lang::t('_AREYOUSURE', 'kb'),
		'body' => $body,
		'callback' => 'function() { this.destroy(); }',
		'callEvents' => array(
			array('caller' => 'subcategorize_switch', 'event' => 'click')
		)
	));
}

echo Form::openElementSpace()
	.Form::getLineBox(Lang::t('_RESOURCE_ORIGINAL_NAME', 'kb'), $data['original_name'])
	.Form::getTextfield(Lang::t('_NAME', 'kb'), 'r_name', 'r_name', 255, Get::req('r_name', DOTY_MIXED, $data['r_name']) )

	.Form::getDropDown(Lang::t('_LANGUAGE', 'kb'), 'r_lang', 'r_lang', $all_languages, $sel_lang)

	.Form::getLineBox(Lang::t('_TYPE', 'kb'), $data['r_type'])
	.Form::getLineBox(Lang::t('_ENVIRONMENT', 'kb'), $data['r_env'])	

	.Form::getCheckbox(Lang::t('_VISIBLE_BY_EVERYONE', 'kb'), 'force_visible', 'force_visible', 1, $data['force_visible'])
	.Form::getCheckbox(Lang::t('_IS_MOBILE', 'kb'), 'is_mobile', 'is_mobile', 1, $data['is_mobile'])

	.Form::getTextarea(Lang::t('_DESCRIPTION', 'kb'), 'r_desc', 'r_desc', Get::req('r_desc', DOTY_MIXED, $data['r_desc']) );

?>

<div class="form_line_l">
	<p><label for="input_add_tag" class="floating"><?php echo Lang::t('_ADD_TAGS', 'kb'); ?></label></p>
	<div class="form_autocomplete_container">
	<input type="text" alt="<?php echo Lang::t('_ADD_TAGS', 'kb'); ?>" maxlength="255" value="" name="input_add_tag" id="input_add_tag" class="textfield">
	<a href="" id="link_add_tag"><img alt="Add" src="<?php echo getPathImage(); ?>standard/add.png" class="valing-middle"></a>
	<div id="box_autocomplete"></div>
	</div>
</div>
<p><?php echo Lang::t('_TAGS_TIPS', 'tags'); ?></p>

<div class="form_line_l"><p class="label_effect"><?php echo Lang::t('_TAGS', 'kb'); ?>:</p>
	<span id="res_current_tags"></span>
</div>

<?php

if (!empty($form_extra_hidden)) {
	foreach($form_extra_hidden as $field_id=>$val) {
		echo Form::getHidden($field_id, $field_id, $val);
	}
}

echo Form::getHidden('tag_list', 'tag_list', htmlentities($c_tags_json))

	.Form::getHidden('res_id', 'res_id', (int)$data['res_id'])
	.Form::getHidden('original_name', 'original_name', $data['original_name'])
	.Form::getHidden('r_item_id', 'r_item_id', (int)$data['r_item_id'])
	.Form::getHidden('r_type', 'r_type', $data['r_type'])
	.Form::getHidden('r_env', 'r_env', $data['r_env'])
	.Form::getHidden('r_env_parent_id', 'r_env_parent_id', $data['r_env_parent_id'])
	.Form::getHidden('r_param', 'r_param', $data['r_param'])
	.Form::getHidden('h_selected_folders', 'h_selected_folders', '')

	.Form::closeElementSpace()

	.Form::openButtonSpace()
	.Form::getButton('org_categorize_save', 'org_categorize_save', Lang::t('_SAVE', 'kb') )
	.Form::getButton('org_categorize_cancel', 'org_categorize_cancel', Lang::t('_UNDO', 'kb') )
	.Form::closeButtonSpace();
?>
</div>

<?php echo Form::closeForm(); ?>


<div class="nofloat"></div>

</div><!--- std_block --->

</div><!-- test_container -->


<script type="text/javascript">

var res_current_tags_arr =<?php echo(!empty($c_tags_json) ? $c_tags_json : '[]'); ?>;
handle_tags =false;

YAHOO.util.Event.onDOMReady(draw_tag_list);

function add_res_tag(new_tag) {
	var in_array =false;
	var new_tag_arr =[];
	var cur_tag ='';


	if (new_tag.search(',') > -1) {
		new_tag_arr =new_tag.split(',');
	}
	else {
		new_tag_arr.push(new_tag);
	}

	for(k in new_tag_arr) {
		in_array =false;
		cur_tag =new_tag_arr[k].replace(/^\s+|\s+$/g,""); // trim

		for(i in res_current_tags_arr) {
			if (res_current_tags_arr[i] == cur_tag) {
				in_array =true;
			}
		}

		if (!in_array && cur_tag != '') {
			res_current_tags_arr.push(cur_tag);
		}
	}

	draw_tag_list();
}

YAHOO.util.Event.addListener("add_res", "submit", function(e) {
	if (handle_tags) {
		YAHOO.util.Event.preventDefault(e);
		new_tag =YAHOO.util.Dom.get("input_add_tag").value;
		YAHOO.util.Dom.get("input_add_tag").value ='';
		add_res_tag(new_tag);
	}
});


YAHOO.util.Event.on("input_add_tag", "focusin", function(e) {
	handle_tags =true;
});


YAHOO.util.Event.on("input_add_tag", "focusout", function(e) {
	handle_tags =false;
});


YAHOO.util.Event.addListener("link_add_tag", "click", function(e) {
	YAHOO.util.Event.preventDefault(e);
	new_tag =YAHOO.util.Dom.get("input_add_tag").value;
	YAHOO.util.Dom.get("input_add_tag").value ='';
	add_res_tag(new_tag);
});


// ----------- Tag Add box autocomplete --------------------------------

YAHOO.namespace("my.Data");

YAHOO.my.Data.arrayTag = <?php echo(!empty($all_tags_json) ? $all_tags_json : '[]'); ?>;

YAHOO.my.BasicLocal = function() {
	// Use a LocalDataSource
	var oDS = new YAHOO.util.LocalDataSource(YAHOO.my.Data.arrayTag);

	// Instantiate the AutoComplete
	var oAC = new YAHOO.widget.AutoComplete("input_add_tag", "box_autocomplete", oDS);
	oAC.delimChar = [","];
	oAC.prehighlightClassName = "yui-ac-prehighlight";
	oAC.useShadow = true;

	return {
			oDS: oDS,
			oAC: oAC
	};
}();

// ---------------------------------------------------------------------


function draw_tag_list() {
	var res ='';
	var tag_name ='';

	var not_empty =false;
	for(var i in res_current_tags_arr) {
		tag_name =res_current_tags_arr[i];
		res+='<a href="#" onclick="javascript:remove_res_tag(\''+tag_name+'\');">';
		res+=tag_name+'</a>, ';
		not_empty =true;
	}
	if (not_empty) {
		res =res.substr(0, res.length-2);
	}
	YAHOO.util.Dom.get('tag_list').value =YAHOO.lang.JSON.stringify(res_current_tags_arr);
	YAHOO.util.Dom.get('res_current_tags').innerHTML =res;
}


function remove_res_tag(tag_name) {
	var new_arr=[];

	for(i in res_current_tags_arr) {
		if (res_current_tags_arr[i] != tag_name) {
			new_arr.push(res_current_tags_arr[i]);
		}
	}

	res_current_tags_arr =new_arr;

	draw_tag_list();
}


</script>