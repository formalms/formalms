<?php Get::title(Lang::t('_ENROLLRULES', 'enrollrules')); ?>
<div class="std_block">
<script type="text/javascript">
var RulesActiveFormatter = function(elLiner, oRecord, oColumn, oData) {
	var id = 'rule_change_'+oRecord.getData("id_rule");
	try{
		if(oData.indexOf('1') == -1) elLiner.innerHTML = '<a id="'+id+'" href="ajax.adm_server.php?r=alms/enrollrules/activate&id_rule='+oRecord.getData("id_rule")+'" class="ico-sprite subs_noac" title="<?php echo Lang::t('_ACTIVATE', 'enrollrules'); ?>"><span><?php echo Lang::t('_ACTIVATE', 'enrollrules'); ?></span></a>';
		else elLiner.innerHTML = '<a id="'+id+'" href="ajax.adm_server.php?r=alms/enrollrules/activate&id_rule='+oRecord.getData("id_rule")+'" class="ico-sprite subs_actv" title="<?php echo Lang::t('_DEACTIVATE', 'enrollrules'); ?>"><span><?php echo Lang::t('_DEACTIVATE', 'enrollrules'); ?></span></a>';
	}catch(e) {}
}
function change_rules_status(e) {
	YAHOO.util.Event.preventDefault(e);
	YAHOO.util.Connect.asyncRequest("POST", this.href, {
		success:function(o) {
			if( YAHOO.util.Dom.hasClass(o.argument[0], 'subs_noac') ) {
				YAHOO.util.Dom.replaceClass(o.argument[0], 'subs_noac', 'subs_actv');
			} else {
				YAHOO.util.Dom.replaceClass(o.argument[0], 'subs_actv', 'subs_noac');
			}
		},
		failure:function(o) {},
		argument: [this]
	});
}
function mod_rule(e) {
	YAHOO.util.Event.preventDefault(e);
	CreateDialog("user_rules_mod", {
		modal: true,
		close: true,
		visible: false,
		fixedcenter: true,
		constraintoviewport: true,
		draggable: true,
		hideaftersubmit: false,
		isDynamic: true,
		ajaxUrl: this.href,
		confirmOnly: false,
		directSubmit: false,
		callback: function() {
			this.destroy();
			DataTable_enrollrules.refresh();
		}
	}).call(this, e);
}
</script>
<?php
$this->widget('table', array(
	'id'			=> 'enrollrules',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/enrollrules/get',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'title',
	'dir'			=> 'asc',
	'columns'		=> array(
		array('key' => 'lang_code', 'label' => Lang::t('_LANGUAGE', 'enrollrules'), 'sortable' => true, 'className' => 'img-cell'),
		array('key' => 'title', 'label' => Lang::t('_TITLE', 'enrollrules'), 'sortable' => true),
		array('key' => 'rule_type_text', 'label' => Lang::t('_TYPE', 'enrollrules')),
		array('key' => 'rule_active', 'label' => '<span class="ico-sprite subs_actv"><span>'.Lang::t('_ACTIVE', 'enrollrules').'</span></span>', 'formatter'=>'RulesActiveFormatter', 'className' => 'img-cell'),
		array('key' => 'mod_elem', 'label' => '<span class="ico-sprite subs_elem"><span>'.Lang::t('_MANAGE', 'enrollrules').'</span></span>', 'className' => 'img-cell'),
		array('key' => 'mod', 'label' => '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'enrollrules').'</span></span>', 'className' => 'img-cell'),
		array('key' => 'del', 'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'enrollrules').'</span></span>', 'formatter'=>'doceboDelete', 'className' => 'img-cell')
	),
	'fields' => array('id_rule', 'title', 'lang_code', 'rule_type', 'rule_type_text', 'creation_date', 'rule_active', 'mod_elem', 'mod', 'del'),
	'show' => 'table',
	'delDisplayField' => 'title',
	'events' => array(
		'beforeRenderEvent' => 'function() {
			var rlist = YAHOO.util.Selector.query("a[id^=rule_change_]");
			for (var i=0; i<rlist.length; i++) YAHOO.util.Event.purgeElement(rlist[i]);

			var mlist = YAHOO.util.Selector.query("a[id^=mod_rules_]");
			for (var i=0; i<mlist.length; i++) YAHOO.util.Event.purgeElement(mlist[i]);
		}',
		'postRenderEvent' => 'function() {
			var rlist = YAHOO.util.Selector.query("a[id^=rule_change_]");
			YAHOO.util.Event.addListener(rlist, "click", change_rules_status);

			var mlist = YAHOO.util.Selector.query("a[id^=mod_rules_]");
			YAHOO.util.Event.addListener(mlist, "click", mod_rule);
		}'
	),
	'rel_actions'	=> array(
		'<a id="enrollrules_add_over" class="ico-wt-sprite subs_add" href="index.php?r=alms/enrollrules/add"><span>'.Lang::t('_ADD', 'enrollrules').'</span></a>'
		.'<a class="ico-wt-sprite subs_elem" href="index.php?r=alms/enrollrules/showlog"><span>'.Lang::t('_SHOW_LOGS', 'enrollrules').'</span></a>'
		,
		'<a id="enrollrules_add_below" class="ico-wt-sprite subs_add" href="index.php?r=alms/enrollrules/add"><span>'.Lang::t('_ADD', 'enrollrules').'</span></a>'
		.'<a class="ico-wt-sprite subs_elem" href="index.php?r=alms/enrollrules/showlog"><span>'.Lang::t('_SHOW_LOGS', 'enrollrules').'</span></a>'
	),
));

$this->widget('dialog', array(
	'id' => 'add_rules_dialog',
	'dynamicContent' => true,
	'ajaxUrl' => '"ajax.adm_server.php?r=alms/enrollrules/add"',
	'dynamicAjaxUrl' => true,
	'callback' => 'function() {
		this.destroy();
		DataTable_enrollrules.refresh();
	}',
	'callEvents' => array(
		array('caller' => 'enrollrules_add_over', 'event' => 'click'),
		array('caller' => 'enrollrules_add_below', 'event' => 'click')
	)
));
?>
</div>