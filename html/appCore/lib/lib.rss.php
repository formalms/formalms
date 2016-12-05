<?php

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
 * @package admin-library
 * @subpackage feed
 * @category Feed Aggregator / Generator classes
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 */

if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');
if(!defined("IN_FORMA")) define("IN_FORMA", true);


class FeedReader {
	/** FeedReader manager object */
	var $frManager=NULL;

	var $lang=NULL;

	var $query_rss = array();
	var $url_append = array();

	/**
	 * FeedReader constructor
	 * @param string $pfm_prefix the prefix for the tables names
	 *			if not given global $prefix variable is used
	 * @param resource $dbconn the connection to the database
	 * 			if not given last connection will be used.
	 */
	function FeedReader($pfm_prefix=FALSE, $dbconn=NULL) {

		$this->frManager=new FeedReaderManager($pfm_prefix, $dbconn);
		$this->lang =& DoceboLanguage::createInstance('feedmanager', "framework");

	}

	function resetUrlQueryRss() {

		$this->query_rss = array();
	}

	function setUrlQueryRss($string) {

		$this->query_rss[] = $string;
	}

	function setAppendToUrl($feed_id, $txt) {
		$this->url_append[$feed_id] =$txt;
	}

	function getAppendToUrl($feed_id) {
		if (isset($this->url_append[$feed_id])) {
			return $this->url_append[$feed_id];
		}
		else {
			return "";
		}
	}

	function readFeed($feed_id, $force_refresh=FALSE, $ignore_expired=FALSE) {

		require_once(_base_."/addons/feeds/read/lastRSS.php");
		$rss=new lastRSS();
		$rss->cache_dir="";

		$feed_info=$this->frManager->getFeedInfo($feed_id);

		$last_update_ts=$GLOBALS["regset"]->databaseToTimestamp($feed_info["last_update"]);

		$expired=FALSE;
		if ((time()-$feed_info["refresh_time"]*60 > $last_update_ts) && (!$ignore_expired))
			$expired=TRUE;

		//echo $last_update_ts." --- ".(time()-$feed_info["refresh_time"]*60);

		$ok =FALSE;
		if ((empty($feed_info["content"])) || ($expired) || ($force_refresh)) {

			$feed_url = $feed_info["url"];
			if(!empty($this->query_rss)) {

				while(list(, $param) = each($this->query_rss)) {

					$param_name = explode('=', $param);

					if(strpos($feed_url, $param_name[0]) === false) {
						$feed_url .= ( strpos($feed_url, '?') === false ? '?' : '&' ).$param;
					}
				}
				reset($this->query_rss);

			}

			$image="";
			$rss_array = $rss->get($feed_url.$this->getAppendToUrl($feed_id));
			if (isset($rss_array["image_url"])) {
				$image = $rss_array["image_url"];
			}

			if (!empty($rss_array)) {
				$content=urlencode(Util::serialize($rss_array));
				if (!empty($content)) {
					$this->frManager->setFeedContent($feed_id, $content, $image);
					$ok =TRUE;
				}
			}
		}

		if ($ok) {
			return $rss_array;
		}
		else {
			return Util::unserialize(urldecode($feed_info["content"]));
		}

	}


	function readCustomRss($feed_url) {

		require_once(_base_."/addons/feeds/read/lastRSS.php");

		$rss = new lastRSS();
		$rss->cache_dir = "";

		$rss_array = $rss->get($feed_url);
		if(isset($rss_array["image_url"])) $image = $rss_array["image_url"];
		else $image = "";

		return $rss_array;
	}


	function cleanEntry($entry) {
		$res="";

		if (preg_match("/^<!\\[CDATA\\[/i", $entry)) {
			$res=preg_replace("/<!\\[CDATA\\[(.*?)\\]\\]>/si", "\$1", $entry);
		} else {
			if (function_exists("html_entity_decode")) {
				$res = html_entity_decode($entry, ENT_NOQUOTES);
			}
			else {
				$res = $entry;
			}
		}
		if(!function_exists('mb_detect_encoding')) return $res;
		if(mb_detect_encoding($res) != 'UTF-8') $res = utf8_encode($res);
		return $res;
	}

}


class FeedReaderManager {
	/** db connection */
	var $dbconn;
	/** prefix for the database */
	var $prefix;

	var $localized_strings=array();

	/**
	 * FeedReaderManager constructor
	 * @param resource $dbconn the connection to the database
	 * 			if not given last connection will be used.
	 */
	function FeedReaderManager( $dbconn = NULL ) {
		//$this->prefix=$GLOBALS["prefix_cms"];
		$this->dbConn=$dbconn;
	}


	/**
	 **/
	function _getFeedsTable() {
		return $this->prefix."_feed_cache";
	}


	function _executeQuery( $query ) {
		if( $this->dbconn === NULL )
			$rs = sql_query( $query );
		else
			$rs = sql_query( $query, $this->dbconn );
		return $rs;
	}


	function _executeInsert( $query ) {
		if( $this->dbconn === NULL ) {
			if( !sql_query( $query ) )
				return FALSE;
		} else {
			if( !sql_query( $query, $this->dbconn ) )
				return FALSE;
		}
		if( $this->dbconn === NULL )
			return sql_insert_id();
		else
			return sql_insert_id($this->dbconn);
	}



	/**
	 */
	function getAllFeeds($ini, $vis_item) {

		$feed_list=array();
		$feed_list["feed_arr"]=array();

		$fields ="t1.feed_id, t1.title, t1.url, t1.image, t1.content, t1.active, ";
		$fields.="t1.refresh_time, t1.last_update, t1.show_on_platform, t1.ord";
		$qtxt ="
		SELECT ".$fields."
		FROM ".$this->_getFeedsTable()." as t1
		WHERE zone = 'public'";
		$qtxt.="ORDER BY t1.ord ";
		$q=$this->_executeQuery($qtxt);

		if ($q)
			$feed_list["feed_tot"]=sql_num_rows($q);
		else
			$feed_list["feed_tot"]=0;

		$qtxt.="LIMIT ".$ini.",".$vis_item;
		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_array($q)) {
				$feed_list["feed_arr"][$i]=$row;

				$i++;
			}
		}

		return $feed_list;
	}


	/**
	 */
	function getFeedRequestData($startIndex, $results, $sort, $dir, $filter) {
		$field_convert = array(
			"title" => 'title',
			"url" => 'url',
			"refresh_time" => 'refresh_time'
		);
		$sort_data = $field_convert[$sort];

		$feed_list = array();
		$feed_list["feed_arr"] = array();

		$fields = "t1.feed_id, t1.title, t1.url, t1.content, t1.active, t1.refresh_time, t1.last_update, t1.show_on_platform ";

		$q_head1 = "SELECT ".$fields." ";
		$q_head2 = "SELECT COUNT(*) ";
		$q_body = "FROM ".$this->_getFeedsTable()." as t1 WHERE zone = 'public' ";

		$q = $this->_executeQuery($q_head2.$q_body);
		if ($q) list($row_count) = sql_fetch_row($q);
		else $rowcount = 0;
		$feed_list["feed_tot"] = $row_count;

		$q_order = "ORDER BY t1.".$sort_data." ";
		$q_limit ="LIMIT ".$startIndex.",".$results;
		$q = $this->_executeQuery($q_head1.$q_body.$q_order.$q_limit);

		if (($q) && (sql_num_rows($q) > 0))
			while ($row = sql_fetch_array($q)) $feed_list["feed_arr"][]=$row;

		return $feed_list;
	}


	/**
	 * @return array
	 */
	function getFeedListArray() {
		$feed_list=array();

		$qtxt ="SELECT t1.feed_id, t1.title FROM ".$this->_getFeedsTable()." as t1 ";
		$qtxt .= " WHERE zone = 'public' ";
		$qtxt .= " ORDER BY t1.title ASC ";
		$q = $this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0))
			while ($row = sql_fetch_array($q))
				$feed_list[$row["feed_id"]]=$row["title"];

		return $feed_list;
	}

	function getFeedListByZone($zone) {

		$feed_list = array();
		$find_feed = "
		SELECT t1.feed_id, t1.title
		FROM ".$this->_getFeedsTable()." as t1
		WHERE t1.zone = '".$zone."'";
		if(!$re_feed = $this->_executeQuery($find_feed)) return $feed_list;

		while($row = sql_fetch_array($re_feed)) {

			$feed_list[$row["feed_id"]] = $row["title"];
		}

		return $feed_list;
	}

	/**
	 * @return array
	 */
	function getFeedInfo($feed_id) {

		if (isset($this->feed_info[$feed_id]))
			return $this->feed_info[$feed_id];
		else {

			$res=array();

			$qtxt="SELECT * FROM ".$this->_getFeedsTable()." WHERE feed_id='".$feed_id."'";
			$q=$this->_executeQuery($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {
				$row=sql_fetch_array($q);
				$res=$row;
			}

			$this->feed_info[$feed_id]=$res;
			return $res;
		}
	}


	/**
	 */
	function saveFeed($data) {

		$id = (isset($data["feed_id"]) && $data["feed_id"]!=false ? (int)$data["feed_id"] : false);
		$title = substr($data["title"], 0, 255);
		$url = substr($data["url"], 0, 255);
		$old_url = $data["old_url"];
		$refresh_time = (int)$data["refresh_time"];
		//$show_on_platform = implode(",", $data["show_on_platform"]);

		if ($id===false) {
			$field_list = "title, url, active, refresh_time";
			$field_val = "'".$title."', '".$url."', '1', '".$refresh_time."'";
			$qtxt = "INSERT INTO ".$this->_getFeedsTable()." (".$field_list.") VALUES(".$field_val.")";
			$id = $this->_executeInsert($qtxt);
			return $id;
		}	else if ($id > 0) {
			$url_changed = (($old_url != $url) ? TRUE : FALSE);
			$qtxt = "UPDATE ".$this->_getFeedsTable()." SET title='".$title."', url='".$url."', ";
			if ($url_changed)	$qtxt .= "image='', content='', last_update='0', ";
			$qtxt .= "refresh_time='".$refresh_time."' ";
			$qtxt .= "WHERE feed_id='".$id."'";

			$q = $this->_executeQuery($qtxt);

			if ($url_changed) {
				$feed_reader = new FeedReader();
				$feed_reader->readFeed($id);
				unset($feed_reader);
			}

			return ($q ? true : false);
		}

		return false;
	}


	/**
	 */
	function setFeedContent($feed_id, $content, $image="") {
		$qtxt ="UPDATE ".$this->_getFeedsTable()." SET content='".$content."', last_update=NOW() ";
		if (!empty($image))
			$qtxt.=", image='".$image."' ";
		$qtxt.="WHERE feed_id='".$feed_id."'";

		$this->_executeQuery($qtxt);
	}


	/**
	 */
	function getLastOrd($table) {
		$qtxt="SELECT ord FROM ".$table." ORDER BY ord DESC";
		$q=$this->_executeQuery($qtxt);

		$res=0;

		if (($q) && (sql_num_rows($q) > 0)) {
			$row=sql_fetch_array($q);
			$res=$row["ord"];
		}

		return $res;
	}


	function deleteFeed($feed_id) {
		$qtxt ="DELETE FROM ".$this->_getFeedsTable()." WHERE feed_id='".(int)$feed_id."'";
		$q = $this->_executeQuery($qtxt);
		return ($q ? true : false);
	}

}



class FeedReaderAdmin {
	/** FeedReader manager object */
	var $frManager=NULL;

	var $lang=NULL;

	/**
	 * FeedReaderAdmin constructor
	 * @param string $pfm_prefix the prefix for the tables names
	 *			if not given global $prefix variable is used
	 * @param resource $dbconn the connection to the database
	 * 			if not given last connection will be used.
	 */
	function FeedReaderAdmin($pfm_prefix=FALSE, $dbconn=NULL) {

		$this->frManager=new FeedReaderManager($pfm_prefix, $dbconn);
		$this->lang =& DoceboLanguage::createInstance('feedmanager', "framework");

	}


	function backUi(& $out, $url=FALSE) {

		if ($url === FALSE)
			$url="index.php?modname=feedreader&amp;op=feedreader";

		$out->add(getBackUi($url, $this->lang->def( '_BACK' )));
	}


	function drawAddEditDataForm($type, $data_id=0, $parent_id=0) {

	}


	function deleteDataForm($type, $data_id=0, $parent_id) {

		include_once(_base_."/lib/lib.form.php");
		include_once(_base_.'/lib/lib.upload.php');

		$out=& $GLOBALS['page'];

		$back_url="index.php?modname=feedreader&op=feedreader";
		if ($parent_id > 0)
			$back_url.="&parent=".$parent_id;

		if (isset($_POST["undo"])) {
			Util::jump_to($back_url);
		}
		else if (isset($_POST["conf_del"])) {

			$this->frManager->deleteData((int)$_POST["data_id"], $_POST["type"]);

			Util::jump_to($back_url);
		}
		else {

			$id=(int)importVar("id");
			$stored_val["data_txt"]=$this->frManager->getItemLangText($id);
			$data_txt=$stored_val["data_txt"][getLanguage()];

			$out->add(getTitleArea($this->lang->def("_BUGTRACKER"), "feedreader"));

			$out->add("<div class=\"std_block\">\n");

			$form=new Form();

			$url="index.php?modname=feedreader&amp;op=del&amp;type=".$type."&amp;id=".$id;
			if ($parent_id > 0)
				$url.="&amp;parent=".$parent_id;
			$out->add($form->openForm("feedreader_form", $url));

			$out->add($form->getHidden("data_id", "data_id", $data_id));
			$out->add($form->getHidden("parent_id", "parent_id", $parent_id));
			$out->add($form->getHidden("type", "type", $type));

			$out->add(getDeleteUi(
			$this->lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$this->lang->def('_TITLE').' :</span> '.$data_txt.'<br />',
				false,
				'conf_del',
				'undo'));

			$out->add($form->closeForm());
			$out->add("</div>\n");
		}
	}




	function getRefreshTimeArr(& $lang) {

		$refresh_time_arr=array();

		$refresh_time_arr["10"]=$lang->def("_10_MINS");
		$refresh_time_arr["15"]=$lang->def("_15_MINS");
		$refresh_time_arr["30"]=$lang->def("_30_MINS");
		$refresh_time_arr["45"]=$lang->def("_45_MINS");
		$refresh_time_arr["60"]=$lang->def("_1_HOUR");
		$refresh_time_arr["120"]=$lang->def("_2_HOURS");
		$refresh_time_arr["240"]=$lang->def("_4_HOURS");
		$refresh_time_arr["360"]=$lang->def("_6_HOURS");
		$refresh_time_arr["480"]=$lang->def("_8_HOURS");
		$refresh_time_arr["720"]=$lang->def("_12_HOURS");
		$refresh_time_arr["1440"]=$lang->def("_1_DAY");
		$refresh_time_arr["2880"]=$lang->def("_2_DAYS");
		$refresh_time_arr["10080"]=$lang->def("_1_WEEK");

		return $refresh_time_arr;
	}

}



// ----------------------------------------------------------------------------



class FeedGenerator {

	/** db connection */
	var $dbconn;
	/** prefix for the database */
	var $prefix;

	var $platform=NULL;

	var $lang=NULL;
	var $feed_lang=NULL;
	var $allow_debug=TRUE;

	var $feed_info=NULL;

	var $max_feed_items=10;

	/**
	 * FeedReader constructor
	 * @param string $pfm_prefix the prefix for the tables names
	 *			if not given global $prefix variable is used
	 * @param resource $dbconn the connection to the database
	 * 			if not given last connection will be used.
	 */
	function FeedGenerator($key1=FALSE, $key2=NULL, $platform=FALSE, $pfm_prefix=FALSE, $dbconn=NULL) {

		//$this->prefix=$GLOBALS["prefix_cms"];
		$this->dbConn=$dbconn;

		$this->lang =& DoceboLanguage::createInstance('feedmanager', "framework");
		$this->feed_lang=getLanguage();

		$this->key1=$key1;
		$this->key2=$key2;
		$this->platform=($platform !== FALSE ? $platform : Get::cur_plat());

	}


	/**
	 **/
	function _getMainTable() {
		return $this->prefix."_feed_out";
	}


	function _executeQuery( $query ) {
		if( $this->dbconn === NULL )
			$rs = sql_query( $query );
		else
			$rs = sql_query( $query, $this->dbconn );
		return $rs;
	}


	function _executeInsert( $query ) {
		if( $this->dbconn === NULL ) {
			if( !sql_query( $query ) )
				return FALSE;
		} else {
			if( !sql_query( $query, $this->dbconn ) )
				return FALSE;
		}
		if( $this->dbconn === NULL )
			return sql_insert_id();
		else
			return sql_insert_id($this->dbconn);
	}


	function setLanguage($feed_lang) {
		$this->feed_lang=substr($feed_lang, 0, 50);
	}


	function getLanguage() {
		return $this->feed_lang;
	}


	function setAllowDebug($val) {
		$this->allow_debug=(bool)$val;
	}


	function getAllowDebug() {
		return (bool)$this->allow_debug;
	}


	function getRegenerateFlag() {

		$info=$this->getGeneratedFeedInfo();

		if (is_array($info))
			return (int)$info["regenerate"];
		else
			return 1;
	}


	function setRegenerateFlag($val=1, $custom_where=FALSE) {

		if (($custom_where !== FALSE) && (!empty($custom_where)))
			$where=$custom_where;
		else
			$where=$this->getKeyQuery();

		$qtxt ="UPDATE ".$this->_getMainTable()." SET regenerate='".(int)$val."' ";
		$qtxt.="WHERE ".$where;

		return $this->_executeQuery($qtxt);
	}


	function getMaxFeedItems() {
		return (int)$this->max_feed_items;
	}


	function setMaxFeedItems($val) {
		$this->max_feed_items=(int)$val;
	}


	function getKeyQuery($tab_pfx="") {
		$res="";

		if (!empty($tab_pfx))
			$tab_pfx=$tab_pfx.".";

		if (empty($this->key1))
			return 0;

		$res.=$tab_pfx."key1='".$this->key1."' AND ";

		if ($this->key2 !== NULL) {
			$res.=$tab_pfx."key2='".(int)$this->key2."' AND ";
		}

		$res.=$tab_pfx."platform='".$this->platform."'";

		return $res;
	}


	function loadGeneratedFeedInfo($feed_id=FALSE, $alias=FALSE) {
		$res=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getMainTable()." ";
		$qtxt.="WHERE ";

		if ($feed_id !== FALSE) {
			$qtxt.="feed_id='".(int)$feed_id."'";
		}
		else if ($alias !== FALSE) {
			$qtxt.="alias='".substr($alias, 0, 100)."' AND ";
			$qtxt.="language='".$this->getLanguage()."'";
		}
		else {
			$qtxt.=$this->getKeyQuery();
		}


		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$res=sql_fetch_array($q);

			if (($feed_id !== FALSE) || ($alias !== FALSE)) {
				$this->key1=$res["key1"];
				$this->key2=$res["key2"];
				$this->platform=$res["platform"];
				if ($feed_id !== FALSE)
					$this->setLanguage($res["language"]);
			}
		}
		else
			$res=FALSE;

		return $res;
	}


	function getGeneratedFeedInfo($feed_id=FALSE, $alias=FALSE) {

		if (!isset($this->feed_info)) {
			$info=$this->loadGeneratedFeedInfo($feed_id, $alias);
			$this->feed_info=$info;
		}

		return $this->feed_info;
	}


	function generateFeed($title, $data_arr, $feed_lang=FALSE, $force=FALSE, $url=FALSE) {
		$qtxt="";
		$res=FALSE;

		if ($feed_lang !== FALSE)
			$this->setLanguage($feed_lang);


		$info=$this->getGeneratedFeedInfo();

		if (($title === FALSE) && ($info !== FALSE))
			$feed_title=$info["title"];
		else
			$feed_title=$title;

		if (empty($feed_title))
			$feed_title=preg_replace("/www\\./i", "", $_SERVER["HTTP_HOST"]);


		if ((!$force) && (!$this->getRegenerateFlag())) {
			$feed_id=$info["feed_id"];
			$alias=$info["alias"];
			$this->addFeedToMeta($feed_title, $feed_id, $alias);
			return FALSE;
		}

		$content=urlencode($this->createFeedCode($feed_title, $data_arr, $url));

		if ($info === FALSE) { // Feed doesn't exists yet..

			$field_list ="key1, ";
			$field_list.=($this->key2 !== NULL ? "key2, " : "");
			$field_list.="platform, language, title, content, last_update, regenerate";
			$field_val ="'".$this->key1."', ";
			$field_val.=($this->key2 !== NULL ? "'".(int)$this->key2."', " : "");
			$field_val.="'".$this->platform."', '".$this->getLanguage()."', ";
			$field_val.="'".$title."', '".$content."', NOW(), 0";

			$qtxt="INSERT INTO ".$this->_getMainTable()." (".$field_list.") VALUES(".$field_val.")";
			$feed_id=$this->_executeInsert($qtxt);
			$alias=FALSE;
			$res=$feed_id;
		}
		else if (is_array($info)) {

			$qtxt.="UPDATE ".$this->_getMainTable()." SET ";
			if ($title !== FALSE)
				$qtxt.="title='".$title."', ";

			$qtxt.="content='".$content."', last_update=NOW(), regenerate='1' ";
			$qtxt.="WHERE feed_id='".$info["feed_id"]."' LIMIT 1";
			$this->_executeQuery($qtxt);

			$feed_id=$info["feed_id"];
			$alias=$info["alias"];
			$res=$feed_id;
		}

		$this->addFeedToMeta($feed_title, $feed_id, $alias);
		return $res;
	}


	function createFeedCode($title, $data_arr, $url=FALSE) {
		require_once(_base_."/addons/feeds/write/rss_generator.inc.php");

		if ($url === FALSE)
			$url=$GLOBALS[$this->platform]["url"];

		$feed=new rssGenerator_channel();
		$feed->title=$title;
		$feed->link=$url;
		$feed->description=" ";
//		$feed->language = 'en-us';
		$feed->generator="FormaLms ".$GLOBALS["framework"]["core_version"];
//		$feed->managingEditor = 'editor@mysite.com';
//		$feed->webMaster='webmaster@mysite.com';


		$i=0;
		reset($data_arr);
		while (($data=each($data_arr)) && ($i < $this->getMaxFeedItems())) {

			$item=new rssGenerator_item();
			$item->title='<![CDATA['.$data["value"]["title"].']]>';
			$item->description='<![CDATA['.$data["value"]["description"].']]>';
			$item->link=$data["value"]["url"];
			$item->pubDate=$data["value"]["date"];;
			$feed->items[]=$item;

		}


		$feed_code = new rssGenerator_rss();
		$feed_code->encoding = 'UTF-8';
		$feed_code->version = '2.0';

		$code=$feed_code->createFeed($feed);
		// $code=utf8_encode($code);

		return $code;
	}


	function deleteFeed($feed_id, $last_update=FALSE) {
		// TODO
/*
- funzione che elimina il feed partendo dalla chiave oppure, se specificato,
  i feed che non son stati aggiornati dopo la X data;
*/
	}


	function addFeedToMeta($title, $feed_id, $alias, $url=FALSE) {

		if (!isset($GLOBALS["feed_in_page"]))
			$GLOBALS["feed_in_page"]=array();
		else if (in_array($feed_id, $GLOBALS["feed_in_page"]))
			return "";

		if ($url === FALSE)
			$url=$GLOBALS[$this->platform]["url"];

		if (!empty($alias)) {
			$res ="<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".$title."\" ";
			$res.="href=\"".$url."feed.php?alias=".$alias;
			$res.="&amp;lang=".$this->getLanguage()."\" />\n";
		}
		else {
			$res ="<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".$title."\" ";
			$res.="href=\"".$url."feed.php?feed_id=".(int)$feed_id."\" />\n";
		}

		$GLOBALS["page"]->add($res, "page_head");
		$GLOBALS["feed_in_page"][]=$feed_id;
	}


	/**
	 * Usually called from feed.php
	 */
	function writeFeed() {

		$feed_id=FALSE;
		$alias=FALSE;

		if ((isset($_GET["feed_id"])) && ((int)$_GET["feed_id"] > 0)) {
			$feed_id=(int)$_GET["feed_id"];
		}
		else if ((isset($_GET["alias"])) && (!empty($_GET["alias"]))) {

			$alias=$_GET["alias"];
			if ((isset($_GET["lang"])) && (!empty($_GET["lang"])))
				$this->setLanguage($_GET["lang"]);

		}
		else
			return "";


		$info=$this->getGeneratedFeedInfo($feed_id, $alias);

		$content=urldecode($info["content"]);

		return $content;
	}


}



?>
