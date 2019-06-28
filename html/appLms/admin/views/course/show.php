<?php echo getTitleArea(Lang::t('_COURSE', 'course')); ?>
<div class="std_block">
<?php



//Categories tree
$languages = array(
    '_ROOT' => $root_name,
    '_NEW_FOLDER_NAME' => Lang::t('_NEW_CATEGORY', 'course'),
    '_MOD' => Lang::t('_MOD', 'course'),
    '_AREYOUSURE' => Lang::t('_AREYOUSURE', 'standard'),
    '_NAME' => Lang::t('_NAME', 'standardt'),
    '_MOD' => Lang::t('_MOD', 'standard'),
    '_DEL' => Lang::t('_DEL', 'standard'),
    '_MOVE' => Lang::t('_MOVE', 'standard'),
    '_SAVE' => Lang::t('_SAVE', 'standard'),
    '_CONFIRM' => Lang::t('_CONFIRM', 'standard'),
    '_UNDO' => Lang::t('_UNDO', 'standard'),
    '_ADD' => Lang::t('_ADD', 'standard'),
    '_YES'=> Lang::t('_YES', 'standard'),
    '_NO' => Lang::t('_NO', 'standard'),
    '_INHERIT' => Lang::t('_ORG_CHART_INHERIT', 'organization_chart'),
    '_NEW_FOLDER' => Lang::t('_NEW_FOLDER', 'organization_chart'),
    '_DEL' => Lang::t('_DEL', 'standard'),
    '_AJAX_FAILURE' => Lang::t('_CONNECTION_ERROR', 'standard')
);


      //** CR : LR TABLE OF COURSE , RESPONSIVE **
      $modifica = $languages['_MOD'];
      $cancella = $languages['_DEL'];
      $nome = $languages['_NAME'];
      
      
     $info_course ='<style>
              @media
        only screen and (max-width: 870px),
        (min-device-width: 870px) and (max-device-width: 1024px)  {            

                    #yuievtautoid-0 td:nth-of-type(1):before { content: "'.Lang::t('_DIRECTORY_GROUPID', 'admin_directory').'"; }
                    #yuievtautoid-0 td:nth-of-type(1):before { content: "'.Lang::t('_CODE', 'cart').'"; }
                    #yuievtautoid-0 td:nth-of-type(2):before { content: "'.$nome.'"; }
                    #yuievtautoid-0 td:nth-of-type(3):before { content: "'.Lang::t('_TYPE', 'standard').'"; }
                    #yuievtautoid-0 td:nth-of-type(4):before { content: "'.Lang::t('_STUDENTS', 'coursereport').'"; }
                    #yuievtautoid-0 td:nth-of-type(5):before { content: "'.Lang::t('_WAITING', 'standard').'"; }
                    #yuievtautoid-0 td:nth-of-type(6):before { content: "'.Lang::t('_INSCR', 'report').'"; }
                    #yuievtautoid-0 td:nth-of-type(7):before { content: "'.Lang::t('_CLASSROOM_EDITION', 'course').'"; }
                    #yuievtautoid-0 td:nth-of-type(8):before { content: "'.Lang::t('_CERTIFICATE_ASSIGN', 'certificate').'"; }
                    #yuievtautoid-0 td:nth-of-type(9):before { content: "'.Lang::t('_MYCOMPETENCES', 'menu_over').'"; }
                    #yuievtautoid-0 td:nth-of-type(10):before { content: "'.Lang::t('_ASSIGN_MENU', 'course').'"; } 
                    #yuievtautoid-0 td:nth-of-type(11):before { content: "'.Lang::t('_MAKE_A_COPY', 'standard').'"; } 
                    #yuievtautoid-0 td:nth-of-type(12):before { content: "'.$modifica.'"; } 
                    #yuievtautoid-0 td:nth-of-type(13):before { content: "'.$cancella.'"; } 
                    }        
                    </style>
                ';   
    
     echo  $info_course;
    //*********************** 

                      





$_tree_params = array(
	'id' => 'category_tree',
	'ajaxUrl' => 'ajax.adm_server.php?r='.$base_link_course.'/gettreedata',
	'treeClass' => 'CourseFolderTree',
	'treeFile' => Get::rel_path('lms').'/admin/views/course/coursefoldertree.js',
	'languages' => $languages,
	'initialSelectedNode' => $initial_selected_node,
	'dragDrop' => true
);

if ($permissions['add_category']) {
	$rel_title = Lang::t('_NEW_CATEGORY', 'course');
	$rel_action = '<a class="ico-wt-sprite subs_add" id="category_tree_add_folder_button" href="ajax.adm_server.php?r=adm/course/addfolder&id='.$initial_selected_node.'" '
		.' title="'.$rel_title.'"><span>'.$rel_title.'</span></a>';
	$_tree_params['rel_action'] = $rel_action;
	$_tree_params['addFolderButton'] = 'add_folder_button';
}

$this->widget('tree', $_tree_params);

echo	'<div class="quick_search_form">'
		.'<div class="common_options">'
		.Form::getInputCheckbox('classroom', 'classroom', '1', ($filter['classroom'] ? true : false), '')
			.' <label class="label_normal" for="classroom">'.Lang::t('_CLASSROOM', 'admin_directory').'</label>'
			.'&nbsp;&nbsp;&nbsp;&nbsp;'
		.Form::getInputCheckbox('descendants', 'descendants', '1', ($filter['descendants'] ? true : false), '')
			.' <label class="label_normal" for="descendants">'.Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory').'</label>'
			.'&nbsp;&nbsp;&nbsp;&nbsp;'
		.Form::getInputCheckbox('waiting', 'waiting', '1', ($filter['waiting'] ? true : false), '')
			.' <label class="label_normal" for="waiting">'.Lang::t('_WAITING_USERS', 'organization_chart').'</label>'
		.'</div>'
		.'<div>'
		.Form::openForm('course_filters', 'index.php?r='.$base_link_course.'/show')
		.Form::getInputTextfield( "search_t", "text", "text", $filter['text'], '', 255, '' ) //TO DO: value from $_SESSION
		.Form::getButton( "c_filter_set", "c_filter_set", Lang::t('_SEARCH', 'standard'), "search_b")
		.Form::getButton( "c_filter_reset", "c_filter_reset", Lang::t('_RESET', 'standard'), "reset_b")
		.Form::closeForm()
		.'</div>'
		.'</div>';

$columns_arr = array(
	array('key' => 'code', 'label' => Lang::t('_CODE', 'course'), 'sortable' => true),
	array('key' => 'name', 'label' => Lang::t('_NAME', 'course'), 'sortable' => true),
	array('key' => 'type', 'label' => Lang::t('_TYPE', 'course'), 'className' => 'min-cell'),
	array('key' => 'students', 'label' => Lang::t('_STUDENTS', 'coursereport'), 'className' => 'img-cell1')
);

if ($permissions['moderate'])//if(checkPerm('moderate', true, 'course', 'lms'))
	$columns_arr[] = array('key' => 'wait', 'label' => Lang::t('_WAITING', 'course'), 'className' => 'img-cell1');

if ($permissions['subscribe'])//if(checkPerm('subscribe', true, 'course', 'lms'))
	$columns_arr[] = array('key' => 'user', 'label' =>  Get::sprite('subs_users', Lang::t('_USER_STATUS_SUBS', 'course') ), 'className' => 'img-cell1');

if ($permissions['view'])
	$columns_arr[] = array('key' => 'edition', 'label' =>  Get::sprite('subs_date', Lang::t('_CLASSROOM_EDITION', 'course') ), 'className' => 'img-cel1l');

$perm_assign = checkPerm('assign', true, 'certificate', 'lms');
$perm_release = checkPerm('release', true, 'certificate', 'lms');

if ($perm_assign) {
	$columns_arr[] = array('key' => 'certificate', 'label' => Get::sprite('subs_pdf', Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course')), 'className' => 'img-cell1');
}

if ($permissions['view_cert'] && $perm_release) {
	$columns_arr[] = array('key' => 'certreleased', 'label' => Get::sprite('subs_print', Lang::t('_CERTIFICATE_RELEASE', 'course')), 'className' => 'img-cell1');
}

if ($permissions['mod']) {
	$columns_arr[] = array('key' => 'competences', 'label' => Get::sprite('subs_competence', Lang::t('_COMPETENCES', 'course')), 'className' => 'img-cell1');
	$columns_arr[] = array('key' => 'menu', 'label' => Get::sprite('subs_menu', Lang::t('_ASSIGN_MENU', 'course')), 'className' => 'img-cell1');
}

if ($permissions['add'])
	$columns_arr[] = array('key' => 'dup', 'label' => Get::sprite('subs_dup', Lang::t('_MAKE_A_COPY', 'course')), 'className' => 'img-cell1', 'formatter' => 'dup');

if ($permissions['mod'])
	$columns_arr[] = array('key' => 'mod', 'label' => Get::sprite('subs_mod', Lang::t('_MOD', 'course')), 'className' => 'img-cell1');

if ($permissions['del'] && !Get::cfg('demo_mode'))
	$columns_arr[] = array('key' => 'del', 'label' => Get::sprite('subs_del', Lang::t('_DEL', 'course')), 'formatter'=>'doceboDelete', 'className' => 'img-cell1');

$_table_params = array(
	'id'			=> 'course_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r='.$base_link_course.'/getcourselist',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'columns'		=> $columns_arr,
	'fields' => array('id', 'code', 'name', 'type', 'type_id', 'students', 'wait', 'user', 'edition', 'certificate', 'certreleased', 'competences', 'menu', 'dup', 'mod', 'del'),
	'show' => 'table',
	'delDisplayField' => 'name',
	'generateRequest' => 'Courses.requestBuilder'
);

$_table_params['rel_actions'] = '';

if ($permissions['add']) {
	$_table_params['rel_actions'] .= '<a class="ico-wt-sprite subs_add" href="index.php?r='.$base_link_course.'/newcourse"><span>'.Lang::t('_NEW', 'course').'</span></a>';
}

if($permissions['subscribe']) {
	$_table_params['rel_actions'] .= ' <a class="ico-wt-sprite subs_users" href="index.php?r='.$base_link_subscription.'/multiplesubscription"><span>'.Lang::t('_MULTIPLE_SUBSCRIPTION', 'course').'</span></a>'
		.((int)$unsubscribe_requests > 0
			? '<a class="ico-wt-sprite subs_users" href="index.php?r='.$base_link_subscription.'/unsubscriberequests">'
				.'<span>'.Lang::t('_UNSUBSCRIBE_REQUESTS', 'course').' ('.(int)$unsubscribe_requests.')</span></a>'
			: '');
}

$this->widget('table', $_table_params);






?>
</div>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function(){
		var classroom = YAHOO.util.Dom.get('classroom');
		var descendants = YAHOO.util.Dom.get('descendants');
		var waiting = YAHOO.util.Dom.get('waiting');
		var button_sub = YAHOO.util.Dom.get('c_filter_set');
		var button_res = YAHOO.util.Dom.get('c_filter_reset');
		var form = YAHOO.util.Dom.get('course_filters');

		YAHOO.util.Event.addListener(classroom, 'change', filterEvent);
		YAHOO.util.Event.addListener(descendants, 'change', filterEvent);
		YAHOO.util.Event.addListener(waiting, 'change', filterEvent);
		YAHOO.util.Event.addListener(button_sub, 'click', filterEvent);
		YAHOO.util.Event.addListener(button_res, 'click', resetEvent);
		YAHOO.util.Event.addListener(form, 'submit', filterEvent);
	});

	function filterEvent(e)
	{
		YAHOO.util.Event.preventDefault(e);

		var classroom = YAHOO.util.Dom.get('classroom');
		var descendants = YAHOO.util.Dom.get('descendants');
		var waiting = YAHOO.util.Dom.get('waiting');
		var text = YAHOO.util.Dom.get('text');

		var postdata =	'waiting=' + waiting.checked
						+ '&descendants=' + descendants.checked
						+ '&classroom=' + classroom.checked;

		if(text.value !== '')
			postdata += '&text=' + text.value;

		YAHOO.util.Connect.asyncRequest("POST", "ajax.adm_server.php?r=<?php echo $base_link_course; ?>/filterevent&", {
			success: function(o) {
				DataTable_course_table.refresh();
			},
			failure: function() {
				DataTable_course_table.refresh();
			}
		}, postdata);
	}

	function resetEvent(e)
	{
		var classroom = YAHOO.util.Dom.get('classroom');
		var descendants = YAHOO.util.Dom.get('descendants');
		var waiting = YAHOO.util.Dom.get('waiting');
		var text = YAHOO.util.Dom.get('text');

		text.value = '';
		waiting.checked = false;
		descendants.checked = false;
		classroom.checked = false;

		YAHOO.util.Connect.asyncRequest("POST", "ajax.adm_server.php?r=<?php echo $base_link_course; ?>/resetevent&", {
			success: function(o) {
				DataTable_course_table.refresh();
			},
			failure: function() {
				DataTable_course_table.refresh();
			}
		});
	}

var Courses = {

	selectedFolder: <?php echo (int)$initial_selected_node; ?>,

	requestBuilder: function(oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		var output = "&results=" + results
			+ "&startIndex=" + startIndex
			+ "&sort=" + sort
			+ "&dir=" + dir
			+	"&node_id=" + Courses.selectedFolder;
		return output;
	}
}

</script>