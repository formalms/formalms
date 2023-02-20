<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version  $Id: lib.urlmanager.php 899 2007-01-09 16:28:59Z giovanni $
 */
class UrlManager
{
    public $use_mod_rewrite = false;
    public $mod_rewrite_title = null;
    public $mod_rewrite_url_items = [];
    public $mod_rewrite_url_pattern = null;

    public $std_query = null;
    public $query_items = [];
    public $query_map = false;
    public $ignore_items = [];

    public $std_base_url = 'index.php';
    public $temp_std_base_url = false;

    public $_otherModRewriteParamLoaded = false;

    /**
     * UrlManager constructor.
     */
    public function __construct($url = false)
    {
        if ($url !== false) {
            $url_piece = explode('?', $url);
            $this->setBaseUrl($url_piece[0]);

            if (isset($url_piece[1])) {
                $this->setStdQuery($url_piece[1]);
            }
        }
    }

    public function &getInstance($param = false)
    {
        if ($param == false) {
            Log::add('used default urlamanager');

            if (!isset($GLOBALS['url_manager']['default'])) {
                $GLOBALS['url_manager']['default'] = new UrlManager();
            }

            return $GLOBALS['url_manager']['default'];
        }
        if (!isset($GLOBALS['url_manager'][$param])) {
            $GLOBALS['url_manager'][$param] = new UrlManager();
        }

        return $GLOBALS['url_manager'][$param];
    }

    public function setBaseUrl($url)
    {
        $this->std_base_url = $url;
    }

    /**
     * Set a temporary base url that will be used only
     * once as the getBaseUrl is called.
     */
    public function setTempBaseUrl($url)
    {
        $this->temp_std_base_url = $url;
    }

    public function getBaseUrl()
    {
        if (empty($this->temp_std_base_url)) {
            $res = $this->std_base_url;
        } else {
            $res = $this->temp_std_base_url;
            $this->temp_std_base_url = false; // btw, this makes it temporary!
        }

        return $res;
    }

    public function setQueryItems($items)
    {
        $ignore = $this->getIgnoreItems();
        if ((is_array($ignore)) && (count($ignore) > 0)) {
            foreach ($ignore as $item_key) {
                if (isset($items[$item_key])) {
                    unset($items[$item_key]);
                }
            }
        }

        if ($this->getQueryMap() !== false) {
            $items = $this->applyQueryMap($items);
        }

        $this->query_items = $items;
    }

    public function getQueryItems()
    {
        return (array) $this->query_items;
    }

    public function setStdQuery($query)
    {
        $res = [];

        if (is_array($query)) {
            $res = $this->explodeQueryItems($query);
        } elseif (is_string($query)) {
            $items = explode('&', trim($query, '&'));
            $res = $this->explodeQueryItems($items);
        }

        if ($this->getQueryMap() !== false) {
            $res = $this->applyQueryMap($res);
        }

        $this->std_query = $res;
    }

    public function getStdQuery($res_type = 'processed')
    {
        switch ($res_type) {
            case 'processed':
                return (array) $this->std_query;
             break;
            case 'array':
                return $this->implodeQueryItems($this->std_query);
             break;
            case 'plain':
                return implode('&', $this->implodeQueryItems($this->std_query));
             break;
        }
    }

    public function addToStdQuery($query)
    {
        $res = [];

        $current = $this->getStdQuery();
        $this->setStdQuery($query);
        $new = $this->getStdQuery();

        $res = array_merge($current, $new);

        $this->std_query = $res;
    }

    public function updateStdQuery($key, $value)
    {
        $res = [];

        $res = $this->getStdQuery();
        $map = $this->getQueryMap();

        if (isset($map[$key])) {
            $key = $map[$key];
        }

        $res[$key] = $value;

        $this->std_query = $res;
    }

    public function setQueryMap($array)
    {
        $this->query_map = $array;
    }

    public function applyQueryMap($query)
    {
        //$query_items=$this->explodeQueryItems($query);
        $query_items = $query;

        foreach ($this->getQueryMap() as $key => $val) {
            if (array_key_exists($val, $query_items)) {
                $this->printError('You are trying to map a key to another one that already exists: ' . $key . ' -&gt; ' . $val);
            }

            if (isset($query_items[$key])) {
                $query_items[$val] = $query_items[$key];
                unset($query_items[$key]);
            }
        }
        ksort($query_items);
        //$res=$this->implodeQueryItems($query_items);
        $res = $query_items;

        return $res;
    }

    public function disableQueryMap()
    {
        $this->query_map = false;
    }

    public function getQueryMap()
    {
        return $this->query_map;
    }

    public function setIgnoreItems($array)
    {
        $this->ignore_items = $array;
    }

    /**
     * Used only on query items; not on standard items cause it won't make sense.
     */
    public function getIgnoreItems()
    {
        return $this->ignore_items;
    }

    public function setUseModRewrite($use)
    {
        $this->use_mod_rewrite = $use;
    }

    public function getUseModRewrite()
    {
        return (bool) $this->use_mod_rewrite;
    }

    public function setModRewriteTitle($txt)
    {
        //require_once(_base_.'/lib/lib.utils.php');
        $this->mod_rewrite_title = getCleanTitle($txt);
    }

    public function getModRewriteTitle()
    {
        return $this->mod_rewrite_title;
    }

    public function setModRewriteUrlItems($items)
    {
        $this->mod_rewrite_url_items = $items;
    }

    public function getModRewriteUrlItems()
    {
        return (array) $this->mod_rewrite_url_items;
    }

    public function setModRewriteUrlPattern($pattern)
    {
        $this->mod_rewrite_url_pattern = $pattern;
    }

    public function getModRewriteUrlPattern()
    {
        return $this->mod_rewrite_url_pattern;
    }

    public function getUrl($query = false, $use_html_code = true)
    {
        $res = '';

        if ($use_html_code) {
            $amp = '&amp;';
        } else {
            $amp = '&';
        }

        if (is_array($query)) {
            $this->setQueryItems($this->explodeQueryItems($query));
        } elseif (is_string($query)) {
            $items = explode('&', trim($query, '&'));
            $this->setQueryItems($this->explodeQueryItems($items));
        } else {
            $this->setQueryItems([]);
        }

        if (!$this->getUseModRewrite()) { // mod_rewrite OFF
            $res .= $this->getBaseUrl();
            $url_query = '';

            $query_items = array_merge($this->getStdQuery(), $this->getQueryItems());
            $query_items = $this->implodeQueryItems($query_items);

            $url_query .= implode($amp, $query_items);

            if (!empty($url_query)) {
                $res .= '?' . $url_query;
            }
        } else {  // mod_rewrite ON
            $res = $this->getModRewriteUrl();
        }

        return $res;
    }

    public function explodeQueryItems($query)
    {
        $other_items = [];
        foreach ($query as $val) {
            $current_item = explode('=', $val);
            if (count($current_item) > 1) {
                $other_items[$current_item[0]] = $current_item[1];
            }
        }

        return $other_items;
    }

    public function implodeQueryItems($query_items)
    {
        $query = [];
        foreach ($query_items as $key => $val) {
            $query[] = $key . '=' . $val;
        }

        return $query;
    }

    public function getModRewriteUrl()
    {
        $res = '';

        $pattern = $this->getModRewriteUrlPattern();
        $items = $this->getModRewriteUrlItems();
        $other_items_list = array_merge($this->getStdQuery(), $this->getQueryItems());
        $title = $this->getModRewriteTitle();

        //$other_items=$this->explodeQueryItems($other_items_list);
        $other_items = $other_items_list;

        $res = $pattern;

        if ((preg_match('/\\[T\\]/', $pattern)) && (empty($title))) {
            $this->printError('Your pattern requires a title but it has not been set.');
        } elseif ((preg_match('/\\[T\\]/', $pattern)) && (!empty($title))) {
            $res = preg_replace('/\\[T\\]/', $title, $res);
        }

        $req_param_count = substr_count($pattern, '[P]');

        if (count($items) < $req_param_count) {
            $this->printError('Your pattern requires more parameters. (' . count($items) . ' < ' . $req_param_count . ')');
        }

        foreach ($items as $item_key) {
            if (isset($other_items[$item_key])) {
                $replace = $other_items[$item_key];
                unset($other_items[$item_key]);
            } else {
                $replace = $item_key . '-NOT-SET';
            }

            $res = preg_replace('/\\[P\\]/', $replace, $res, 1);
        }

        if (preg_match('/\\/\\[O\\]\\//', $pattern)) { // Other parameters
            if (count($other_items) < 1) {
                $other_items_str = '/0/';
            } else {
                $other_items_str = $this->getOtherItemsStr($other_items);
            }
            $res = preg_replace('/\\/\\[O\\]\\//', $other_items_str, $res);
        }

        return $res;
    }

    public function getOtherItemsStr($items)
    {
        $from = ['-', '_'];
        $to = ['--', '__'];

        $mr_arr = [];
        foreach ($items as $key => $val) {
            $my_key = str_replace($from, $to, $key);
            $my_val = str_replace($from, $to, $val);

            if (!empty($my_val)) {
                $mr_arr[] = $my_key . '_' . $my_val;
            }
        }

        $res = '/' . rawurlencode(implode('-', $mr_arr)) . '/';

        return $res;
    }

    public function loadOtherModRewriteParamFromVar($mr_str)
    {
        if ($this->_otherModRewriteParamLoaded === true) {
            return true;
        } // Load parameters only once!

        if (($this->getUseModRewrite()) && (!empty($mr_str))) {
            $mr_str = rawurldecode($mr_str);

            $mr_arr = $this->getCleanMrArray($mr_str, '-');

            foreach ($mr_arr as $key => $val) {
                $val_arr = $this->getCleanMrArray($val, '_');

                $my_key = $val_arr[0];
                $my_val = $val_arr[1];

                if ((!isset($_GET[$my_key])) && (!empty($my_val))) {
                    $_GET[$my_key] = $my_val;
                    //-debug:-// echo("<br />"."\$_GET[".$my_key."]=".$my_val);
                }
            }
        }

        $this->_otherModRewriteParamLoaded = true;
    }

    public function getCleanMrArray($mr_str, $sep)
    {
        $mr_arr = [];

        if (preg_match('/[' . $sep . ']{2,2}/', $mr_str)) { // Optimized ;)
            $mr_str_map = preg_replace('/[' . $sep . ']{2,2}/', '  ', $mr_str);
            $mr_str_map_arr = preg_split('/' . $sep . '/', $mr_str_map, -1, PREG_SPLIT_OFFSET_CAPTURE);

            $mr_arr = $this->splitFromMap($mr_str, $mr_str_map_arr, $sep);
        } else {
            $mr_arr = explode($sep, $mr_str);
        }

        return $mr_arr;
    }

    public function splitFromMap($str, $arr, $sep)
    {
        $res = [];
        $_OFFSET = 1;

        $i = 0;
        while ($i < count($arr)) {
            if ($i + 1 < count($arr)) {
                $res[$i] = substr($str, $arr[$i][$_OFFSET], ($arr[$i + 1][$_OFFSET] - $arr[$i][$_OFFSET] - 1));
            } else {
                $res[$i] = substr($str, $arr[$i][$_OFFSET]);
            }

            $res[$i] = preg_replace('/[' . $sep . ']{2,2}/', $sep, $res[$i]);

            ++$i;
        }

        return $res;
    }

    public function printError($txt)
    {
        if ($GLOBALS['framework']['do_debug'] == 'on') {
            exit($txt);
        }
    }
}
