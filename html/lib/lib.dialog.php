<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

function addDialogLibraries() {
	YuiLib::load('container,selector');

	//add js file for courses
	Util::get_js(Get::rel_path('base').'/lib/lib.dialog.js', true, true);
}



/*
	params:
	- $formId : id of the form which contains the delete elements;
	- $dialogFormAction: action of the form created inside the dialogbox, who will submit the delete action;
	- $elementsFilter: string for yui Selector, select the delete inputs and then append events to them;
	- $title: string, the title of the dialogbox which will be displayed in the caption;
	- $okButton: string, the text of the submit button;
	- $cancelbutton: string, the text of the undo button;
	- $composeBody: string, a JS script with a function which will return the content of the dialogbox, in form of string;
	- $idFilter: string, a filter used to extract the numeric id of the data to delete from the id of the delete input element;
	- $idParamName: name of the input parameter in the submit form which will contain the ID of the data to delete;
	- $confirmParamName: name of the input parameter in the submit form which will contain the confirmation of the delete action;
	- $other: other optional parameters (not yet used)
*/
function setupFormDialogBox(
	$formId,
	$dialogFormAction,
	$elementsFilter,
	$title,
	$okButton,
	$cancelButton,
	$composeBody,
	$idFilter,
	$idParamName,
	$confirmParamName,
	$other = array() )
{
	addDialogLibraries();

	$params = '{'.
		"\t".'formId: "'.$formId.'", '."\n".
		"\t".'dialogFormAction: "'.$dialogFormAction.'", '."\n".
		"\t".'elementsFilter: "'.$elementsFilter.'", '."\n".
		"\t".'title: "'.$title.'", '."\n".
		"\t".'okButton: "'.$okButton.'", '."\n".
		"\t".'cancelButton: "'.$cancelButton.'", '."\n".
		"\t".'composeBody: '.$composeBody.', '."\n".
		"\t".'idFilter: "'.$idFilter.'", '."\n".
		"\t".'idParamName: "'.$idParamName.'", '."\n".
		"\t".'confirmParamName: "'.$confirmParamName.'", '."\n".
		"\t".'authentication: "'.Util::getSignature().'" '."\n";
	$temp=array();
	foreach ($other as $key=>$val) {
		if ($key!='' && !is_int($key)) {
			$temp[] = $key.': '.(is_string($val) ? '"'.$val.'"' : $val);
		}
	}
	if (count($temp)>0) $params .= implode(', '."\n", $temp);
	$params .= '}';

	cout('<script type="text/javascript">'."\n"
		.'YAHOO.util.Event.onDOMReady(initDialogForm, '.$params.', true);'
		."\n".'</script>', 'scripts');
}


/*
	params:
	- $elementsFilter: string for yui Selector, select the delete inputs and then append events to them;
	- $title: string, the title of the dialogbox which will be displayed in the caption;
	- $okButton: string, the text of the submit button;
	- $cancelbutton: string, the text of the undo button;
	- $composeBody: string, a JS script with a function who will return the content of the dialogbox, in form of string;
*/
function setupHrefDialogBox(
	$elementsFilter,
	$title = false,
	$okButton = false,
	$cancelButton = false,
	$composeBody = false )
{
	addDialogLibraries();

	if($title == false) $title = Lang::t('_AREYOUSURE');
	if($okButton == false) $okButton = Lang::t('_CONFIRM');
	if($cancelButton == false)	$cancelButton = Lang::t('_UNDO');
	if($composeBody == false) $composeBody = "
		function (o) {
			if((o.title).match(':')) return (o.title).replace(/:/, ':<b>') + '<b>'
			return o.title;
		 } ";

	$params = '{'.
		'elementsFilter: "'.$elementsFilter.'", '."\n".
		'title: "'.$title.'", '."\n".
		'okButton: "'.$okButton.'", '."\n".
		'cancelButton: "'.$cancelButton.'", '."\n".
		'composeBody: '.$composeBody.', '."\n".
		'authentication: "'.Util::getSignature().'" '."\n".
	'}';

	$script = 'YAHOO.util.Event.onDOMReady(initDialogHref, '.$params.', true);';

	cout('<script type="text/javascript">'.$script.'</script>', 'page_head');
}




function setupSimpleFormDialogBox(
  $formId,
  $elementsFilter,
  $composeBody=false)
{
  if($composeBody == false) $composeBody = "
		function (o) {
			if((o.title).match(':')) return (o.title).replace(/:/, ':<b>') + '<b>'
			return o.title;
		 } ";

  $params = '{'.
    'formId: "'.$formId.'", '."\n".
		'elementsFilter: "'.$elementsFilter.'", '."\n".
		'title: "'. Lang::t('_AREYOUSURE').'", '."\n".
		'okButton: "'. Lang::t('_CONFIRM').'", '."\n".
		'cancelButton: "'. Lang::t('_UNDO').'", '."\n".
		'composeBody: '.$composeBody.', '."\n".
		'authentication: "'.Util::getSignature().'"'."\n".
	'}';

	$script = 'YAHOO.util.Event.onDOMReady(initDialogFormSimple, '.$params.', true);';

	cout('<script type="text/javascript">'.$script.'</script>', 'page_head');
}


//------------------------------------------------------------------------------

/*
 * Initializer for Dialog widget (Docebo 4.0).
 * It loads the js file for dialogs and the standard language constants
 */
function initDialogs() {
	require_once(_base_.'/lib/lib.json.php');
	Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true, true);
	$json = new Services_JSON();
	$script = 'YAHOO.dialogConstants.setProperties({'
		.'CONFIRM: '.$json->encode(Lang::t('_CONFIRM', 'standard')).', '
		.'UNDO: '.$json->encode(Lang::t('_UNDO', 'standard')).', '
		.'CLOSE: '.$json->encode(Lang::t('_CLOSE', 'standard')).', '
		.'LOADING: '.$json->encode(Lang::t('_LOADING', 'standard')).', '
		.'ERROR: '.$json->encode(Lang::t('_OPERATION_FAILURE', 'standard')).', '
		.'SERVER_ERROR: '.$json->encode(Lang::t('_CONNECTION_ERROR', 'standard')).', '
		.'loadingIcon: '.$json->encode(Get::tmpl_path().'images/standard/loadbar.gif').', '
		.'smallLoadingIcon: '.$json->encode(Get::tmpl_path().'images/standard/loading_circle.gif').', '
		.'authentication: "'.Util::getSignature().'" '
		.'});'."\n";
	cout('<script type="text/javascript">'.$script.'</script>', 'scripts');
}

?>