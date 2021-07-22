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

/**
 * Simple Selector
 * [selects: anonymous and/or registered users or manual selection]
 *
 * @package admin-core
 * @subpackage user
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 * @version  $Id: lib.simplesel.php 446 2006-06-17 07:23:27Z fabio $
 */

require_once(_base_.'/lib/lib.userselector.php');

class SimpleSelector {

	var $lang=NULL;
	var $use_multi_sel=FALSE;
	var $perm_list=array();

	var $mdir=FALSE;

	var $main_url="";
	var $back_url="";

	var $saved_data=NULL;

	function SimpleSelector($use_multi_sel, & $lang) {

		$this->lang=& $lang;
		$this->use_multi_sel=$use_multi_sel;

		$this->mdir=new UserSelector();
		$this->mdir->show_orgchart_selector=FALSE;

		$this->mdir->use_multi_sel=$use_multi_sel;

		if (!$use_multi_sel) {
			$perm=array();
			$perm["view"]["img"]=getPathImage()."standard/view.png";
			$perm["view"]["alt"]=$lang->def("_VIEW");

			$this->setPermList($perm);
		}
	}


	function onManualSelector() {
		return (bool)(isset($_POST["manualselector"])) || (isset($_GET["manual"]));
	}


	function getOp() {
		$res="main";

		$op=importVar("sel_op");
		$manual=$this->onManualSelector();

		if ($manual)
			$res=(isset($_GET["stayon"]) ? "manual" : "manual_init");
		
		if (isset($_POST["orgchartselector"]))
			$res = 'orgchartselector';
		
		if (isset($_POST['okselector_org']))
			return 'save_org';
		
		if ((isset($_POST["okselector"])) && (!$manual))
			$res="save";
		else if ((isset($_POST["okselector"])) && ($manual))
			$res="save_manual";
		else if ((isset($_POST["cancelselector"])) && (!$manual))
			Util::jump_to($this->getLink("back", "", true));
		else if ((isset($_POST["cancelselector"])) && ($manual))
			Util::jump_to($this->getLink("main", "", true));

		return $res;
	}


	function setPermList($perm_list) {
		$this->perm_list=$perm_list;
	}

	function getPermList() {
		return $this->perm_list;
	}

	function setLinks($main_url, $back_url) {
		$this->main_url=$main_url;
		$this->back_url=$back_url;
	}


	function getLink($type, $extra="", $no_code=false) {

		$res="";

		switch ($type) {
			case "main": {
				$res=$this->main_url;
			} break;
			case "back": {
				$res=$this->back_url;
			} break;
		}

		if ((!empty($res)) && (!empty($extra)))  {
			if (preg_match("/\\?/", $res)) {
				$res.="&amp;".$extra;
			}
			else {
				$res.="?".$extra;
			}
		}

		if ($no_code)
			$res=str_replace("&amp;", "&", $res);

		return $res;
	}


	function setSavedData($data) {

		if ($this->use_multi_sel) {
			$this->mdir->sel_extend->grabSelectedItems($data);
			$this->mdir->sel_extend->setDatabaseItems($data);

			$this->saved_data=$data;
		}
		else {

			if ($this->onManualSelector()) {
				$this->saved_data=$data;
			}
			else {
				$this->saved_data=array();
				$this->saved_data["view"]=array_flip($data);
			}

		}

	}

	function getSavedData() {
		return $this->saved_data;
	}
	
	function orgchartSelector()
	{
		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_.'/lib/lib.form.php');
		
		$res = '';
		
		$lang =& DoceboLanguage::createInstance('simplesel', 'framework');
		
		$tab = new Table(100, $lang->def("_ORGCHART_TITLE"), $lang->def("_ORGCHART_SUMMARY"));
		$form = new Form();
		
		$head_type = array('');
		$head = array($lang->def("_ORGCHART_FOLDER_NAME"));
		
		$url = $this->getLink("main");
		
		$res .= $form->openForm("orgchart_selector", $url.'&amp;orgchart=1');
		
		foreach ($this->getPermList() as $key=>$val) {
			$head_type[]="image";
			$img ="<img src=\"".$val["img"]."\" alt=\"".$val["alt"]."\" ";
			$img.="title=\"".$val["alt"]."\" />";
			$head[]=$img;
		}
		
		$saved_data=$this->getSavedData();
		
		$tab->setColsStyle($head_type);
		$tab->addHead($head);
		
		$query = "SELECT t.idOrg, t.lev, o.translation" .
				" FROM ".$GLOBALS['prefix_fw']."_org_chart_tree AS t" .
				" JOIN ".$GLOBALS['prefix_fw']."_org_chart AS o ON o.id_dir = t.idOrg" .
				" WHERE o.lang_code = '".getLanguage()."'" .
				" ORDER BY t.path ASC";
		
		$result = sql_query($query);
		
		while (list($id_org, $level, $translation) = sql_fetch_row($result))
		{
			$cont = array();
			
			list($idst) = sql_fetch_row(sql_query("SELECT idst FROM ".$GLOBALS['prefix_fw']."_group WHERE groupid = '".('/oc_'.$id_org)."'")); 
			
			$cont_temp = '';
			
			if (($level - 1))
				for ($i = 0; $i < ($level-1); $i++)
					$cont_temp .= '<img src="'.getPathImage('lms').'blank.png'.'" />';
			
			$cont[] = $cont_temp.$translation;
			
			foreach ($this->getPermList() as $key=>$val)
			{
				$chk=false;
				if ((isset($saved_data[$key])) && (is_array($saved_data[$key]))) {
					if (in_array($idst, array_keys($saved_data[$key]))) {
						$chk=true;
					}
				}
				$check_box =$form->getLabel( $key."_".$idst."_", $lang->def("_VIEW")." ".$translation, "access-only");
				$check_box.=$form->getInputCheckbox($key."_".$idst."_", $key."[".$idst."]", 1, $chk, NULL);
				$cont[]=$check_box;
			}
			
			$tab->addBody($cont);
		}
		
		$res .= $tab->getTable();
		
		$res.=$form->getHidden("saved_data", "saved_data", urlencode(Util::serialize($saved_data)));
		
		$res.=$form->openButtonSpace();
		$res.=$form->getButton('okselector_org', 'okselector_org', $lang->def('_CONFIRM'));
		$res.=$form->getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'));
		$res.=$form->closeButtonSpace();
		$res.=$form->closeForm();
		
		return $res;
	}
	
	function loadSimpleSelector($anonymous = true, $orgchart_button = false) {
		$res="";

		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_."/lib/lib.form.php");

		$lang =& DoceboLanguage::createInstance('simplesel', 'framework');
		$form=new Form();
		
		$acl_manger=Docebo::user()->getAclManager();
		$anonymous_idst=$acl_manger->getAnonymousId();

		$res.=getBackUi($this->getLink("back"), $lang->def("_BACK"));

		if ($this->hasManualSelection()) {
			$msg=$lang->def('_MSG_HASMANUAL_1')." \"".$lang->def('_MANUAL_SEL')."\" ".$lang->def('_MSG_HASMANUAL_2');
			$res.=getInfoUi($msg);
		}


		$url=$this->getLink("main");
		$res.=$form->openForm("simple_selector", $url);

		$vis_item=$GLOBALS["framework"]["visuItem"];
		$tab=new Table(2, $lang->def("_SIMPLESEL_TITLE"), $lang->def("_SIMPLESEL_TITLE"));

		$head_type=array('');
		$head=array($lang->def("_USERS"));

		foreach ($this->getPermList() as $key=>$val) {
			$head_type[]="image";
			$img ="<img src=\"".$val["img"]."\" alt=\"".$val["alt"]."\" ";
			$img.="title=\"".$val["alt"]."\" />";
			$head[]=$img;
		}

		$tab->setColsStyle($head_type);
		$tab->addHead($head);


		$users_list=$this->getSimpleUserList();

		$saved_data=$this->getSavedData();
		
		if (!$anonymous)
			unset($users_list[$anonymous_idst]);

		foreach ($users_list as $idst=>$label) {

			$rowcnt=array($label);

			foreach ($this->getPermList() as $key=>$val) {
				$chk=false;
				if ((isset($saved_data[$key])) && (is_array($saved_data[$key]))) {
					if (in_array($idst, array_keys($saved_data[$key]))) {
						$chk=true;
					}
				}
				$check_box =$form->getLabel( $key."_".$idst."_", $lang->def("_VIEW")." ".$label, "access-only");
				$check_box.=$form->getInputCheckbox($key."_".$idst."_", $key."[".$idst."]", 1, $chk, NULL);
				$rowcnt[]=$check_box;
			}

			$tab->addBody($rowcnt);
		}


		$res.=$tab->getTable();

		$res.=$form->getHidden("saved_data", "saved_data", urlencode(Util::serialize($saved_data)));

		$res.=$form->openButtonSpace();
		
		if ($orgchart_button)
			$res.=$form->getButton('orgchartselector', 'orgchartselector', $lang->def('_ORGCHART_SEL'), "transparent_aslink_button").' ';
		
		$res.=$form->getButton('manualselector', 'manualselector', $lang->def('_MANUAL_SEL'), "transparent_aslink_button");
		$res.=$form->closeButtonSpace();

		$res.=$form->openButtonSpace();
		$res.=$form->getButton('okselector', 'okselector', $lang->def('_CONFIRM'));
		$res.=$form->getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'));
		$res.=$form->closeButtonSpace();
		$res.=$form->closeForm();

		return $res;
	}


	function getSimpleUserList() {

		$acl_manger=Docebo::user()->getAclManager();
		$anonymous_idst=$acl_manger->getAnonymousId();
		$regusers_idst=$acl_manger->getGroupRegisteredId();

		$users_list=array();
		$users_list[$anonymous_idst]= Lang::t("_ANONYMOUS_USERS", "standard");
		$users_list[$regusers_idst]= Lang::t("_REGISTERED_USERS", "standard");

		return $users_list;
	}


	function initManualSelector() {

		if ($this->use_multi_sel) {
			$this->mdir->sel_extend->setExtraSel($this->getPermList());
			//if (isset($_POST[DIRECTORY_ID])) {
			if (isset($_GET["stayon"])) {
				$this->mdir->sel_extend->setPrintedItems($this->mdir->getPrintedItems($_POST));
				$this->mdir->sel_extend->grabSelectedItems($_POST);
			}
			else {
				$this->mdir->sel_extend->setPrintedItems($this->getSavedData());
				$this->mdir->sel_extend->setSelectedItems($this->getSavedData());
			}
		}
		else {
			if( !isset($_GET['stayon']) ) {
				$this->mdir->resetSelection($this->getSavedData());
			}
		}

	}


	function loadManualSelector($title) {

		$this->initManualSelector();

		$acl_manger=Docebo::user()->getAclManager();
		$regusers_idst=$acl_manger->getGroupRegisteredId();
		$this->mdir->setUserFilter("group", array($regusers_idst));

		$url=$this->getLink("main", "stayon=1&amp;manual=1");
		$this->mdir->loadSelector($url, $title, "", TRUE);
	}


	function getSaveInfo() {
		$res=false;


		if (isset($_GET["manual"])) { // saving from manual selector

			if ($this->use_multi_sel) {

				$this->initManualSelector();

				$res["selected"]=$this->mdir->sel_extend->getSelectedItems();
				$res["database"]=$this->mdir->sel_extend->getDatabaseItemsFromVar();

			}
			else {

				$selection=$this->mdir->getSelection($_POST);
				$unselected=$this->mdir->getUnselected($_POST);

				$res["selected"]["view"]=$selection;
				//$res["database"]["view"]=array_flip(array_diff($selection, $unselected));
				$res["database"]["view"]=array_flip(array_merge($selection, $unselected));

			}

		}
		else { // saving from simple selector

			$user_list=$this->getSimpleUserList();
			$saved_data=Util::unserialize(urldecode($_POST["saved_data"]));

			foreach ($this->getPermList() as $key=>$val) {
				$i=0;
				foreach ($user_list as $idst=>$label) {

					if ((isset($saved_data[$key])) && (in_array($idst, array_keys($saved_data[$key])))) {
						$res["database"][$key][$idst]=$i;
						$i++;
					}

					if ((isset($_POST[$key])) && (in_array($idst, array_keys($_POST[$key])))) {
						$res["selected"][$key][]=$idst;
					}
					else if (!isset($_POST[$key]))
						$res["selected"][$key]=array();

				}
			}
			//-debug-//
			/* echo "Selected: "; print_r($res["selected"]); echo "<br /><br />Database: "; print_r($res["database"]);
			echo "<br /><br /> POST : <br /><br />"; print_r($_POST); */
		}

		return $res;
	}
	
	function getSaveInfoOrg() {
		$res = false;
		
		$user_list = array();
		
		$query = "SELECT t.idOrg, o.translation" .
				" FROM ".$GLOBALS['prefix_fw']."_org_chart_tree AS t" .
				" JOIN ".$GLOBALS['prefix_fw']."_org_chart AS o ON o.id_dir = t.idOrg" .
				" WHERE o.lang_code = '".getLanguage()."'" .
				" ORDER BY t.path ASC";
		
		$result = sql_query($query);
		
		while (list($id_org, $translation) = sql_fetch_row($result))
		{
			list($idst) = sql_fetch_row(sql_query("SELECT idst FROM ".$GLOBALS['prefix_fw']."_group WHERE groupid = '".('/oc_'.$id_org)."'"));
			
			$user_list[$idst] = $translation;
		}
		
		$saved_data=Util::unserialize(urldecode($_POST["saved_data"]));

		foreach ($this->getPermList() as $key=>$val) {
			$i=0;
			foreach ($user_list as $idst=>$label) {

				if ((isset($saved_data[$key])) && (in_array($idst, array_keys($saved_data[$key])))) {
					$res["database"][$key][$idst]=$i;
					$i++;
				}

				if ((isset($_POST[$key])) && (in_array($idst, array_keys($_POST[$key])))) {
					$res["selected"][$key][]=$idst;
				}
				else if (!isset($_POST[$key]))
					$res["selected"][$key]=array();

			}
		}
		//-debug-//
		/* echo "Selected: "; print_r($res["selected"]); echo "<br /><br />Database: "; print_r($res["database"]);
		echo "<br /><br /> POST : <br /><br />"; print_r($_POST); */

		return $res;
	}
	
	function hasManualSelection() {
		// If this returns true but you expect it to return false try to comment the
		// setUserFilter function in the loadManualSelector function.
		$res=false;

		$user_list=array_keys($this->getSimpleUserList());
		$saved_data=$this->getSavedData();

		if ((is_array($saved_data)) && (count($saved_data) > 0)) {

			$i=0;
			$count=count($saved_data);
			while (($i<$count) && ($res == FALSE)) {
				$perm=current($saved_data);
				if ((is_array($perm)) && (count($perm) > 0)) {
					$perm_keys=array_keys($perm);
					while (($idst=current($perm_keys)) && ($res == FALSE)) {
						if (!in_array($idst, $user_list))
							$res=true;
						next($perm_keys);
					}
				}
				next($saved_data);
				$i++;
			}

		}

		return $res;
	}

}


?>
