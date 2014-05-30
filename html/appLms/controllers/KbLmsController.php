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

class KbLmsController extends LmsController {

	public $name = 'kb';
	protected $_default_action = 'show';
	protected $json;

	public function init() {
		require_once(_base_ . '/lib/lib.json.php');
		$this->json = new Services_JSON();
	}

	public function show() {

		Util::get_js(Get::rel_path('lms') . '/views/kb/kb.js', true, true);
		Util::get_js(Get::rel_path('base') . '/addons/yui/stylesheet/stylesheet-min.js', true, true);

		$filter_text = Get::req('filter_text', DOTY_STRING, "");

		require_once(_lms_ . '/lib/lib.kbres.php');
		$kbres = new KbRes();
		$kb_model = new KbAlms();
		$initial_folders = $kbres->getKbFolders(0, 1); // 0 = root

		$tag_count = $kbres->getTagUseCount();

		// --- set tag cloud data ------------
		$min = false;
		$max = false;
		$tot = 0;

		$tag_cloud = array();
		foreach ($tag_count as $tag_id => $tag_info) {
			$tot+=$tag_info['use_count'];

			$min = ($min > $tag_info['use_count'] ? $tag_info['use_count'] : $min);
			$max = ($max < $tag_info['use_count'] ? $tag_info['use_count'] : $max);
		}

		$min_class_size = 1;
		$max_class_size = 7;
		foreach ($tag_count as $tag_id => $tag_info) {
			$uc = $tag_info['use_count'];
			$range = $max - $min + 1;
			$pos = $range / $uc;
			$class_size = round($max_class_size / $pos);
			$tag_cloud[$tag_id] = array(
				'tag_name' => $tag_info['tag_name'],
				'class_size' => $class_size,
			);
		}

		$course_filter_arr[-1] = Lang::t('_FILTER', 'kb');
		$course_filter_arr+=$kb_model->getCoursesVisibleToUser();


		$this->render('show', array(
			'filter_text' => $filter_text,
			'initial_folders' => $initial_folders,
			'tag_cloud' => $tag_cloud,
			'course_filter_arr' => $course_filter_arr,
			'url_select_folder' => 'ajax.server.php?r=kb/selFolder&folder_id=',
		));
	}

	public function selFolder() {

		$folder_id = Get::req('folder_id', DOTY_INT, 0);

		require_once(_lms_ . '/lib/lib.kbres.php');
		$kbres = new KbRes();
		$folder_arr = $kbres->getKbFolders($folder_id, 2); // 0 = root
		$parents = $kbres->getFolderParents($folder_id);

		$fbox = '';
		$bc = '';

		$p_tot = count($parents);
		$i = $p_tot;
		foreach ($parents as $p_id => $p_name) {
			if ($i < $p_tot) {
				$bc = '&rsquo; <a href="#" id="folder_' . $p_id . '">' .
						$p_name . '</a>' . $bc;
			} else {
				$bc = '&rsquo; ' . $p_name . $bc;
			}
			$i--;
		}

		$bc = '<a href="index.php?r=kb/show" id="folder_0">' .
				Lang::t('_ALL_CATEGORIES', 'kb') . '</a>' . $bc;

		foreach ($folder_arr['folders'] as $folder) {
			$sub = '';
			$li = '<li>';
			$li.='<div>'
				.'<a href="#" id="folder_' . $folder['id'] . '">'
				. $folder['name'] . '</a> '
				.'<span class="kb_folder_tot">(' . $folder['r_count'] . ')</span>'
				.'</div>';
			if (isset($folder['folders']) && !empty($folder['folders'])) {
				foreach ($folder['folders'] as $sub_folder) {
					$sub.='<li><a href="#" id="folder_' . $sub_folder['id'] . '">' . $sub_folder['name'] . '</a> '
						.'<span class="kb_folder_tot">(' . $sub_folder['r_count'] . ')</span></a></li>';
				}
			}
			$li.= ( !empty($sub) ? '<ul class="subfolders">' . $sub . '</ul>' : '');
			$li.='</li>' . "\n";
			$fbox.=$li;
		}

		$res['folder_box'] = $fbox;
		$res['breadcrumbs'] = $bc;

		echo $this->json->encode($res);
	}

	public function getlist() {
		$kb_model = new KbAlms();
		$folder_id = Get::req('folder_id', DOTY_INT, 0);
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'title');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$course_filter = Get::req('course_filter', DOTY_INT, -1);

		// --- Search and filters: -------------------------------------------------

		$sf =$kb_model->getSearchFilter(false, $filter_text, $course_filter);

		// --- Reading resources: --------------------------------------------------

		$res_arr = $kb_model->getResources($folder_id, $start_index, $results, $sort,
			$dir, $sf['where'], $sf['search'], true, true, $sf['show_what']);
		//die(str_replace('%lms', 'learning', $res_arr['qtxt']));
		//print_r($res_arr['matches']); die();
		$array_comm = $res_arr["data"];

		$tags = $kb_model->getAllTagsForResources($res_arr["id_arr"]);

		$list = array();
		$parent_id = array();
		foreach ($array_comm as $key => $value) {
			$id = $array_comm[$key]['res_id'];
			$r_env = $array_comm[$key]['r_env'];

			if (!empty($array_comm[$key]['r_env_parent_id'])) {
				$parent_id[$r_env][$key] = $array_comm[$key]['r_env_parent_id'];
			} else {
				$array_comm[$key]['r_env_parent'] = '';
			}

			$array_comm[$key]['tags'] = (isset($tags[$id]) ? implode(', ', $tags[$id]) : '');
            
            $img_type = $array_comm[$key]['r_type'];
            switch ($img_type) {
                case 'scorm':
                    $img_type = 'scormorg';
                    break;
                case 'file':
                    $img_type = 'item';
                    break;                
                default:
                    break;
            }
            $image = '<img src="'.getPathImage().'lobject/'.$img_type.'.png'.'" '
						.'width="16px" alt="'.$img_type.'" '
						.'title="'.$img_type.'" />';
            $array_comm[$key]['r_type'] = $image;            
		}

		$kb_model->getParentInfo($parent_id, $array_comm, array('course_lo', 'communication', 'games'));

		$result = array(
			'totalRecords' => $res_arr['count'],
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($array_comm),
			'records' => $array_comm
		);

		echo $this->json->encode($result);
	}

	public function play() {
		require_once(_lms_ . '/lib/lib.kbres.php');
		$kbres = new KbRes();
		$kb_model = new KbAlms();

		$from_adm = Get::req('from_adm', DOTY_INT, 0);
		$back_url = ($from_adm ? Get::rel_path('adm') . '/index.php?r=alms/kb/show' : 'index.php?r=kb/show');

		$res_id = Get::req('id', DOTY_INT, 0);
		
		if ($kb_model->checkResourcePerm($res_id)) {
			$kbres->playResource($res_id, $back_url);
		}
		else {
			echo "You can't access";
		}
	}

}
