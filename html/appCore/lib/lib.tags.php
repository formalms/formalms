<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class Tags
{
    public $resource_type = 'mixed';

    public $tags_id;

    public $_tag_t = false;
    public $_tagrel_t = false;
    public $_resource_t = false;

    public $_private_tag_enabled = false;

    public $tags_founded = [];
    public array $private_tags_founded;
    public bool $_use_tag;
    public int $_id_course;

    public function __construct($resource_type, $viewer = false)
    {
        $this->resource_type = $resource_type;
        $this->_tag_t = $GLOBALS['prefix_fw'] . '_tag';
        $this->_tagrel_t = $GLOBALS['prefix_fw'] . '_tag_relation';
        $this->_resource_t = $GLOBALS['prefix_fw'] . '_tag_resource';
        $this->_id_course = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');

        $this->_use_tag = (FormaLms\lib\Get::sett('use_tag', 'off') == 'on');

        if ($viewer == false) {
            $viewer = getLogUserId();
        }

        $this->_private_tag_enabled = false;
        $courseLevel = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('levelCourse');
        if (!empty($courseLevel) && $courseLevel > 3) {
            $this->_private_tag_enabled = true;
        }

    }

    public function setupJs($tags_id, $private_tags = '')
    {
        if (!$this->_use_tag) {
            return '';
        }

        $lang = &DoceboLanguage::createInstance('tags', 'framework');

        $this->tags_id = $tags_id;
        YuiLib::load(['autocomplete' => 'autocomplete-min.js', 'selector' => 'selector-beta-min.js'],
            ['assets/skins/sam' => 'autocomplete.css']);
        Util::get_js(FormaLms\lib\Get::rel_path('adm') . '/lib/lib.tags.js', true, true);

        // setup some thing that we need in the tag editor
        $GLOBALS['page']->add('<script type="text/javascript">' . "\n"
        . 'var tag_params ={
			resource_type: "' . $this->resource_type . '", 
			addr: "' . $GLOBALS['where_framework_relative'] . '/ajax.adm_server.php",
			query_append: "file=tags",
			query:"' . $tags_id . '",
			private_query:"' . $private_tags . '",
			popular_tags: "' . addslashes(implode(', ', $this->getPopularTag())) . '",
			user_tags: "' . addslashes(implode(', ', $this->getUserPopularTag(getLogUserId()))) . '", 
			lang: { tags: "' . addslashes($lang->def('_TAGS')) . '",
				tips: "' . addslashes($lang->def('_TAGS_TIPS')) . '",
				popular_tags: "' . addslashes($lang->def('_POPULAR')) . '",
				user_tags: "' . addslashes($lang->def('_YOURS')) . '",
				save: "' . addslashes($lang->def('_SAVE')) . '",
				undo: "' . addslashes($lang->def('_UNDO')) . '",
				add_tags: "' . addslashes($lang->def('_ADD_TAGS')) . '",
				update_tags: "' . addslashes($lang->def('_MOD')) . '"
			}
		};'
        . "\n" . '</script>', 'scripts');
    }

    public function getAutoComplete($search)
    {
        if (!$this->_use_tag) {
            return '';
        }

        if (strlen($search) < 3) {
            return;
        }

        $query = 'SELECT id_tag, tag_name 
		FROM  ' . $this->_tag_t . " AS t
		WHERE tag_name LIKE '" . $search . "%' 
		ORDER BY tag_name";
        $re = sql_query($query);

        $tags_founded = [];
        while (list($id_tag, $tag_name, $occurences) = sql_fetch_row($re)) {
            $tags_founded[$id_tag] = $tag_name;
        }

        return $tags_founded;
    }

    public function showTags($id_resource, $base_id, $res_title, $res_sample_text, $res_permalink)
    {
        if (!$this->_use_tag) {
            return '';
        }

        $html = '';
        if (isset($this->tags_founded[$id_resource])) {
            $html .= '<p>'
                . '<a class="update_tags" href="#handler-' . $base_id . '_' . $id_resource . '" id="handler-' . $base_id . '_' . $id_resource . '">'
                . Lang::t('_MOD', 'tags', 'framework')
                . '</a> : '
                . '<span id="taglist-' . $base_id . '_' . $id_resource . '">';
            $first = true;
            foreach ($this->tags_founded[$id_resource] as $id_tag => $tag_i) {
                $html .= ($first ? $first = false : ', ')
                    . '<a href="index.php?modname=tags&amp;op=tags&amp;id_tag=' . $id_tag . '">'
                    . $tag_i[0] . ' (' . $tag_i[1] . ')'
                    . '</a>';
            }
            $html .= '</span>'
                . '</p>';
        } else {
            $html .= '<p>'
                . '<a class="update_tags" href="#handler-' . $base_id . '_' . $id_resource . '" id="handler-' . $base_id . '_' . $id_resource . '">'
                . Lang::t('_ADD_TAGS', 'tags', 'framework')
                . '</a> '
                . '<span id="taglist-' . $base_id . '_' . $id_resource . '"></span>'
                . '</p>';
        }
        if ($this->_private_tag_enabled) {
            if (isset($this->private_tags_founded[$id_resource])) {
                $html .= '<p>'
                    . '<a class="update_tags" href="#private-handler-' . $base_id . '_' . $id_resource . '" id="private-handler-' . $base_id . '_' . $id_resource . '">'
                    . Lang::t('_MOD', 'tags', 'framework') . ' (' . Lang::t('_LEVEL_6', 'levels', 'lms') . ') '
                    . '</a> : '
                    . '<span id="private-taglist-' . $base_id . '_' . $id_resource . '">';
                $first = true;
                foreach ($this->private_tags_founded[$id_resource] as $id_tag => $tag_i) {
                    $html .= ($first ? $first = false : ', ')
                        . '<a href="index.php?modname=tags&amp;op=tags&amp;id_tag=' . $id_tag . '">'
                        . $tag_i[0] . ' (' . $tag_i[1] . ')'
                        . '</a>';
                }
                $html .= '</span>'
                    . '</p>';
            } else {
                $html .= '<p>'
                    . '<a class="update_tags" href="#private-handler-' . $base_id . '_' . $id_resource . '" id="private-handler-' . $base_id . '_' . $id_resource . '">'
                    . Lang::t('_ADD_TAGS', 'tags', 'framework') . ' (' . Lang::t('_LEVEL_6', 'levels', 'lms') . ')'
                    . '</a> '
                    . '<span id="private-taglist-' . $base_id . '_' . $id_resource . '"></span>'
                    . '</p>';
            }
        }

        $html .= '<div id="restitle-' . $base_id . '_' . $id_resource . '" style="display:none;">' . $res_title . '</div>'
                . '<div id="samplet-' . $base_id . '_' . $id_resource . '" style="display:none;">' . $res_sample_text . '</div>'
                . '<div id="reslink-' . $base_id . '_' . $id_resource . '" style="display:none;">' . $res_permalink . '</div>';

        return $html;
    }

    public function updateTagResource($id_resource, $id_user, $str_tag, $title, $sample_text, $permalink, $is_private = false)
    {
        if (!$this->_use_tag) {
            return true;
        }

        // break tag list

        $tag_list = [];
        $tag_piece = explode(',', $str_tag);

        foreach ($tag_piece as $k => $v) {
            $v = trim($v);
            if ($v != '') {
                $tag_list[$k] = $v;
            }
        }

        // find id tag

        $tag = [];
        $founded_tag = [];
        $query = 'SELECT id_tag, tag_name
		FROM  ' . $this->_tag_t . " AS t
		WHERE tag_name IN ( '" . implode("', '", $tag_list) . "' )";
        $re = sql_query($query);
        while (list($id_tag, $tag_name) = sql_fetch_row($re)) {
            $tag[] = $id_tag;
            $founded_tag[] = $tag_name;
        }
        $tag_to_create = array_diff($tag_list, $founded_tag);

        // recovering user tag for the resource

        $user_prev_tag = [];
        $query = 'SELECT id_tag
		FROM  ' . $this->_tagrel_t . " 
		WHERE resource_type = '" . $this->resource_type . "'  
			AND id_resource = " . (int) $id_resource . ' 
			AND id_user = ' . (int) $id_user . ' 
			AND private = ' . ($is_private ? 1 : 0) . ' ';
        $re = sql_query($query);
        while (list($id_tag) = sql_fetch_row($re)) {
            $user_prev_tag[] = $id_tag;
        }
        // add or delete ?

        $to_associate = array_diff($tag, $user_prev_tag);
        $to_remove = array_diff($user_prev_tag, $tag);

        // add the non existing tag

        foreach ($tag_to_create as $tag_name) {
            $query = 'INSERT INTO ' . $this->_tag_t . ' '
            . ' ( id_tag, tag_name ) VALUES '
            . " ( NULL, '" . $tag_name . "' ) ";
            $re = sql_query($query);

            if ($re) {
                $to_associate[] = sql_insert_id();
            }
        }

        // add tags ------------------------------------------------------
        if (!empty($to_associate)) {
            $inserts = false;
            foreach ($to_associate as $id_tag) {
                $inserts[] = ' ( '
                    . ' ' . (int) $id_tag . ', ' . (int) $id_resource . ", '" . $this->resource_type . "', " . (int) $id_user . ', ' . (int) $this->_id_course . ', '
                        . ($is_private ? 1 : 0)
                    . ' ) ';
            }
            $query = 'INSERT INTO ' . $this->_tagrel_t . ' ( '
                . ' id_tag, id_resource, resource_type, id_user, id_course, private '
                . ' ) VALUES ' . implode(',', $inserts);
            sql_query($query);
        }
        if (!empty($to_remove)) {
            // remove tags
            $query = 'DELETE FROM ' . $this->_tagrel_t
                . " WHERE resource_type = '" . $this->resource_type . "'  
					AND id_resource = " . (int) $id_resource . ' 
					AND id_user = ' . (int) $id_user . ' 
					AND id_tag IN ( ' . implode(',', $to_remove) . ' ) 
					AND private = ' . ($is_private ? 1 : 0) . ' ';
            sql_query($query);
        }

        // resource shortcut exists
        $q_search = 'SELECT id_resource '
            . ' FROM ' . $this->_resource_t . ''
            . " WHERE resource_type = '" . $this->resource_type . "' "
            . '		AND id_resource = ' . (int) $id_resource . ' ';
        $re = sql_query($q_search);
        if (!sql_num_rows($re)) {
            $query = 'INSERT INTO ' . $this->_resource_t . ' '
                . '( id_resource, resource_type, title, sample_text, permalink )'
                . ' VALUES '
                . '( ' . (int) $id_resource . ", '" . $this->resource_type . "', '" . $title . "', '" . $sample_text . "', '" . $permalink . "' )";
            sql_query($query);
        }

        $this->loadResourcesTags([$id_resource], false, $is_private);
        $first = true;
        $html = '';
        if (!$is_private) {
            $found_arr = &$this->tags_founded[$id_resource];
        } else {
            $found_arr = &$this->private_tags_founded[$id_resource];
        }
        foreach ($found_arr as $id_tag => $tag_i) {
            $html .= ($first ? $first = false : ', ') . $tag_i[0] . ' (' . $tag_i[1] . ')';
        }

        return $html;
    }

    public function loadResourcesTags($arr_resources, $id_user = false, $return_private = false)
    {
        if (!$this->_use_tag) {
            return 0;
        }

        // if only one resource is passed as int transform in a array
        if (!is_array($arr_resources)) {
            $arr_resources = [$arr_resources];
        }
        if ($id_user == false) {
            $id_user = getLogUserId();
        }

        // find all the resource's tags with the occurences
        $query = 'SELECT t.id_tag, t.tag_name, rel.id_resource, rel.private, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . " AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE resource_type = '" . $this->resource_type . "' " .
            " AND id_resource IN ( '" . implode("', '", $arr_resources) . "' ) 
		GROUP BY rel.id_tag, rel.id_resource, private
		ORDER BY tag_name ";
        $re = sql_query($query);

        // save the tag in a class propiety
        $this->tags_founded = [];
        $this->private_tags_founded = [];
        while (list($id_tag, $tag, $id_resource, $private, $occurences) = sql_fetch_row($re)) {
            if ($private) {
                $this->private_tags_founded[$id_resource][$id_tag] = [$tag, $occurences];
            } else {
                $this->tags_founded[$id_resource][$id_tag] = [$tag, $occurences];
            }
        }

        // search for the user tags for the resource and highlight them
        $query = 'SELECT id_tag, id_resource 
		FROM ' . $this->_tagrel_t . " AS rel 
		WHERE resource_type = '" . $this->resource_type . "' 
			AND id_resource IN ( '" . implode("', '", $arr_resources) . "' ) 
			AND id_user = '" . $id_user . "'";
        $re = sql_query($query);
        while (list($id_tag, $id_resource) = sql_fetch_row($re)) {
            if (isset($this->tags_founded[$id_resource][$id_tag][0])) {
                $this->tags_founded[$id_resource][$id_tag][0] = '<b>' . $this->tags_founded[$id_resource][$id_tag][0] . '</b>';
            } elseif (isset($this->private_tags_founded[$id_resource][$id_tag][0])) {
                $this->private_tags_founded[$id_resource][$id_tag][0] = '<b>' . $this->private_tags_founded[$id_resource][$id_tag][0] . '</b>';
            }
        }

        return $return_private ? $this->private_tags_founded : $this->tags_founded;
    }

    public function getResourcesTags($arr_resources)
    {
        if (!$this->_use_tag) {
            return '';
        }

        if (!is_array($arr_resources)) {
            $arr_resources = [$arr_resources];
        }

        $query = 'SELECT t.tag_name, rel.id_resource, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . " AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE resource_type = '" . $this->resource_type . "' " .
            " AND id_resource IN ( '" . implode("', '", $arr_resources) . "' ) 
		GROUP BY rel.id_tag, rel.id_resource ";
        $re = sql_query($query);

        $tags_founded = [];
        while (list($tag, $id_resource, $occurences) = sql_fetch_row($re)) {
            $tags_founded[$id_resource] = [$tag];
        }

        return $tags_founded;
    }

    public function getResourcesOccurrenceTags($arr_resources)
    {
        if (!$this->_use_tag) {
            return [];
        }

        if (!is_array($arr_resources)) {
            $arr_resources = [$arr_resources];
        }

        $query = 'SELECT t.tag_name, rel.id_resource, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . " AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE resource_type = '" . $this->resource_type . "' " .
            " AND id_resource IN ( '" . implode("', '", $arr_resources) . "' ) 
		GROUP BY rel.id_tag, rel.id_resource ";
        $re = sql_query($query);

        $tags_founded = [];
        while (list($tag, $id_resource, $occurences) = sql_fetch_row($re)) {
            $tags_founded[$id_resource][$tag] = ['tag' => $tag, 'occurences' => $occurences];
        }

        return $tags_founded;
    }

    public function getResourceByTags($id_tags, $resource_type = false, $course_filter = false, $ini = false, $limit = false)
    {
        if (!$this->_use_tag) {
            return [];
        }

        $tags_founded = [];
        $arr_resources = [];

        $query = '
		SELECT COUNT(*)
		FROM ' . $this->_tagrel_t . " AS rel 
		WHERE rel.id_tag = '" . $id_tags . "'"
            . ($resource_type !== false ? " AND rel.resource_type = '" . $this->resource_type . "' " : '')
            . ($course_filter !== false ? ' AND rel.id_course IN ( ' . implode(',', $course_filter) . ' ) ' : '');
        list($tags_founded['count']) = sql_fetch_row(sql_query($query));

        $query = '
		SELECT res.id_resource, res.resource_type, rel.id_course, res.title,  res.sample_text, res.permalink, COUNT(*) as occurences
		FROM ' . $this->_tagrel_t . ' AS rel 
			JOIN ' . $this->_resource_t . " AS res
			ON ( rel.id_resource = res.id_resource AND rel.resource_type = res.resource_type )
		WHERE rel.id_tag = '" . $id_tags . "'"
            . ($resource_type !== false ? " AND res.resource_type = '" . $this->resource_type . "' " : '')
            . ($course_filter !== false ? ' AND rel.id_course IN ( ' . implode(',', $course_filter) . ' ) ' : '')
        . ' GROUP BY res.id_resource, res.resource_type '
        . ' ORDER BY occurences DESC, res.title ';
        if ($ini) {
            $query .= ' LIMIT ' . (int) $ini . ', ' . (int) $limit;
        }
        $re = sql_query($query);

        while (list($id_resource, $resource_type, $id_course, $title, $sample_text, $permalink, $occurences) = sql_fetch_row($re)) {
            $tags_founded['list'][$id_resource . '_' . $resource_type] = [
                                    'id_resource' => $id_resource,
                                    'resource_type' => $resource_type,
                                    'id_course' => $id_course,
                                    'title' => $title,
                                    'sample_text' => $sample_text,
                                    'permalink' => $permalink,
                                    'occurences' => $occurences,
                                    'related_tags' => [], ];

            $arr_resources[$resource_type][$id_resource] = $id_resource;
        }

        $query = 'SELECT t.id_tag, t.tag_name, rel.id_resource, rel.resource_type 
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . ' AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE 0 ';
        if (is_array($arr_resources)) {
            foreach ($arr_resources as $type => $id_list) {
                $query .= " OR ( rel.resource_type = '" . $type . "' AND rel.id_resource IN ( " . implode(',', $id_list) . ' ) ) ';
            }
        }
        $query .= ' GROUP BY rel.id_tag, rel.id_resource ';
        $re = sql_query($query);

        while (list($id_tag, $tag, $id_resource, $resource_type) = sql_fetch_row($re)) {
            $tags_founded['list'][$id_resource . '_' . $resource_type]['related_tags'][] = '<a href="index.php?modname=tags&amp;op=tags&amp;id_tag=' . $id_tag . '">'
                    . $tag
                    . '</a>';
        }

        return $tags_founded;
    }

    public function getPopularTag($limit = false)
    {
        if (!$this->_use_tag) {
            return [];
        }

        if (!$limit) {
            $limit = 5;
        }

        $query = 'SELECT t.tag_name, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . " AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE resource_type = '" . $this->resource_type . "' 
		GROUP BY rel.id_tag 
		ORDER BY occurences 
		LIMIT 0, " . $limit . '';
        $re = sql_query($query);

        $tags_founded = [];
        while (list($tag, $occurences) = sql_fetch_row($re)) {
            $tags_founded[] = $tag;
        }

        return $tags_founded;
    }

    public function getUserPopularTag($id_user, $limit = false)
    {
        if (!$this->_use_tag) {
            return [];
        }

        if (!$limit) {
            $limit = 5;
        }

        $query = 'SELECT t.tag_name, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . " AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE resource_type = '" . $this->resource_type . "' 
			AND id_user = " . (int) $id_user . '		
		GROUP BY rel.id_tag 
		ORDER BY occurences 
		LIMIT 0, ' . (int) $limit . '';
        $re = sql_query($query);

        $tags_founded = [];
        while (list($tag, $occurences) = sql_fetch_row($re)) {
            $tags_founded[] = $tag;
        }

        return $tags_founded;
    }

    public function getPlatformTagCloud($filter_course = false)
    {
        $query = 'SELECT t.id_tag, t.tag_name, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . ' AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE rel.private = 0 
		GROUP BY t.tag_name ';

        return $this->getTagCloud($query);
    }

    public function getCourseTagCloud()
    {
        $query = 'SELECT t.id_tag, t.tag_name, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . ' AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE rel.private = 0 
			AND id_course = ' . (int) $this->_id_course . ' 
		GROUP BY t.tag_name ';

        return $this->getTagCloud($query);
    }

    public function getUserTagCloud($id_user)
    {
        $query = 'SELECT t.id_tag, t.tag_name, COUNT(*) as occurences
		FROM  ' . $this->_tag_t . ' AS t
			JOIN ' . $this->_tagrel_t . ' AS rel 
			ON ( t.id_tag = rel.id_tag )
		WHERE id_user = ' . (int) $id_user . '
		GROUP BY t.tag_name ';

        return $this->getTagCloud($query);
    }

    public function getTagCloud($query)
    {
        if (!$this->_use_tag) {
            return '';
        }

        $re = sql_query($query);

        $total_occurrences = 0;
        $min = false;
        $max = false;
        $tags_founded = [];
        while (list($id_tag, $tag, $occurences) = sql_fetch_row($re)) {
            $tags_founded[$id_tag] = [$tag, $occurences];
            $total_occurrences += $occurences;

            if ($min > $occurences) {
                $min = $occurences;
            }
            if ($max < $occurences) {
                $max = $occurences;
            }
        }
        $section = $max - $min;
        if ($section == 0) {
            $section = 1;
        }

        $min_class_size = 1;
        $max_class_size = 7;

        $html = '<ul class="tag_cloud">';

        foreach ($tags_founded as $id_tag => $info) {
            $size = $min_class_size + ($info[1] - $min)
                * ($max_class_size - $min_class_size) / $section;

            $html .= '<li class="t' . round($size) . '">'
                . '<a href="index.php?modname=tags&amp;op=tags&amp;id_tag=' . $id_tag . '">' . $info[0] . '</a><span class="occurence"> (' . $info[1] . ')</span>'
                . '</li> ';
        }
        $html .= '</ul>';

        return $html;
    }

    public function deleteResource($arr_res, $resource_type)
    {
        if (!$this->_use_tag) {
            return true;
        }

        if (!is_array($arr_res)) {
            $arr_res = [$arr_res];
        }

        $re = false;
        $query = 'DELETE FROM ' . $this->_tagrel_t
            . " WHERE resource_type = '" . $resource_type . "'  
				AND id_resource IN ( '" . implode("', '", $arr_res) . "' )";
        if (!sql_query($query)) {
            return false;
        }

        $query = 'DELETE FROM ' . $this->_resource_t
            . " WHERE resource_type = '" . $resource_type . "'  
				AND id_resource IN ( '" . implode("', '", $arr_res) . "' )";
        if (!sql_query($query)) {
            return false;
        }

        return $re ? true : false;
    }
}
