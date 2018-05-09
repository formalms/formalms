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

class HomecatalogueLms extends Model
{
	var $edition_man;
	var $course_man;
	var $classroom_man;

	var $cstatus;
	var $acl_man;

    /* category handling */
    var $children;
    var $the_tree;
    var $tree_deep;       
    
	public function  __construct()
	{
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.edition.php');
		require_once(_lms_.'/lib/lib.date.php');

		$this->course_man = new Man_Course();
		$this->edition_man = new EditionManager();
		$this->classroom_man = new DateManager();

		$this->cstatus = array(	CST_PREPARATION => '_CST_PREPARATION',
								CST_AVAILABLE 	=> '_CST_AVAILABLE',
								CST_EFFECTIVE 	=> '_CST_CONFIRMED',
								CST_CONCLUDED 	=> '_CST_CONCLUDED',
								CST_CANCELLED 	=> '_CST_CANCELLED');

		$this->acl_man =& Docebo::user()->getAclManager();
	}

	public function getTotalCourseNumber($type = '')
	{
		require_once(_lms_.'/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
		$filter = '';

		switch($type)
		{
			case 'elearning':
				$filter = " AND course_type = '".$type."'";
			break;
			case 'classroom':
				$filter = " AND course_type = '".$type."'";
			break;
			case 'edition':
				$filter = " AND course_edition = 1";
			break;
			case 'new':
				$filter = " AND create_date >= '".date('Y-m-d', mktime(0, 0, 0, date('m'), ((int)date('d') - 7), date('Y')))."'";
			break;
			case 'catalogue':
				$id_catalogue = Get::req('id_cata', DOTY_INT, '0');

				$catalogue_course =& $cat_man->getCatalogueCourse($id_catalogue);
				$filter = " AND idCourse IN (".implode(',', $catalogue_course).")";
			break;
			default:
			break;
		}

		$filter .= " AND show_rules = 0";

		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$query =	"SELECT COUNT(*)"
					." FROM %lms_course"
					." WHERE status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")"
					." AND course_type <> 'assessment'"
					." AND ("
					." date_end = '0000-00-00'"
					." OR date_end > '".date('Y-m-d')."'"
					." )"
					.$filter
					.($id_cat > 0 ? " AND idCategory = ".(int)$id_cat : '')
					." ORDER BY name";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

    
    
public function getCourseList($type, $page = 1 )
    {

        require_once(_lms_.'/lib/lib.catalogue.php');
        $cat_man = new Catalogue_Manager();

      //  $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
        $filter = '';


        switch($type)
        {
            case 'elearning' :
            case '1' :
                $filter = " AND course_type = 'elearning'";
            break;
            case 'classroom':
            case '2':
                $filter = " AND course_type = 'classroom'";
            break;
           
            default:
            break;
        }

        $filter .= " AND show_rules = 0";

        $login_link = '<a href="index.php">'.Lang::t('_LOG_IN', 'login').'</a>';
        $signin_link = '<a href="index.php?modname=login&op=register">'.Lang::t('_SIGN_IN', 'login').'</a>';

        require_once(\Forma::inc(_base_ . '/lib/lib.usermanager.php'));
        $option = new UserManagerOption();
        $register_type = $option->getOption('register_type');

        $limit = ($page - 1) * Get::sett('visuItem');
        $id_cat = Get::req('id_cat', DOTY_INT, 0);

    
        
        $query =    "SELECT *"
                    ." FROM %lms_course"
                    ." WHERE status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")"
                    ." AND course_type <> 'assessment'"
                    ." AND ("
                    ." date_end = '0000-00-00'"
                    ." OR date_end > '".date('Y-m-d')."'"
                    ." )"
                    .$filter
                    .($id_cat > 0 ? " AND idCategory = ".(int)$id_cat : '')
                    ." ORDER BY name"
                   ;
                   
                   
                 //  die($query);
                   
                    
        $result = sql_query($query);

        
        //return 
        return $result; 
        
      
    }    
    
    
	public function getCourseList_ORIGINALE($type = '', $page = 1)
	{
		require_once(_lms_.'/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
		$filter = '';

		switch($type)
		{
			case 'elearning':
				$filter = " AND course_type = '".$type."'";
			break;
			case 'classroom':
				$filter = " AND course_type = '".$type."'";
			break;
			case 'edition':
				$filter = " AND course_edition = 1";
			break;
			case 'new':
				$filter = " AND create_date >= '".date('Y-m-d', mktime(0, 0, 0, date('m'), ((int)date('d') - 7), date('Y')))."'";
			break;
			case 'catalogue':
				$id_catalogue = Get::req('id_cata', DOTY_INT, '0');

				$catalogue_course =& $cat_man->getCatalogueCourse($id_catalogue);
				$filter = " AND idCourse IN (".implode(',', $catalogue_course).")";
			break;
			default:
			break;
		}

		$filter .= " AND show_rules = 0";

		$login_link = '<a href="index.php">'.Lang::t('_LOG_IN', 'login').'</a>';
		$signin_link = '<a href="' . Get::rel_path("base") . "/index.php?r=" . _register_ . '">'.Lang::t('_SIGN_IN', 'login').'</a>';

        require_once(\Forma::inc(_base_ . '/lib/lib.usermanager.php'));
		$option = new UserManagerOption();
		$register_type = $option->getOption('register_type');

		$limit = ($page - 1) * Get::sett('visuItem');
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$query =	"SELECT *"
					." FROM %lms_course"
					." WHERE status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")"
					." AND course_type <> 'assessment'"
					." AND ("
					." date_end = '0000-00-00'"
					." OR date_end > '".date('Y-m-d')."'"
					." )"
					.$filter
					.($id_cat > 0 ? " AND idCategory = ".(int)$id_cat : '')
					." ORDER BY name"
					." LIMIT ".$limit.", ".Get::sett('visuItem');

		$result = sql_query($query);

		$html = '';
		$path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';

		while($row = sql_fetch_assoc($result))
		{
			$action = '';

			if($row['course_type'] === 'classroom')
			{
				$additional_info = '';

				$classrooms = $this->classroom_man->getCourseDate($row['idCourse'], false);
				if(count($classrooms) > 0)
				{
					$action =	'<div class="catalog_action" style="top:5px;" id="action_'.$row['idCourse'].'">'
								.'<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'0\')" title="'.Lang::t('_SHOW_EDITIONS', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_SHOW_EDITIONS', 'catalogue').'</p></a>'
								.'</div>';
				}
			}
			elseif($row['course_edition'] == 1)
			{
				$additional_info = '';

				$editions = $this->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);

				if(count($editions) > 0)
				{
					$action =	'<div class="catalog_action" style="top:5px;" id="action_'.$row['idCourse'].'">'
								.'<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'0\')" title="'.Lang::t('_SHOW_EDITIONS', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_SHOW_EDITIONS', 'catalogue').'</p></a>'
								.'</div>';
				}
			}
			else
			{
				// standard elearning course without editions
				$query =	"SELECT COUNT(*)"
							." FROM %lms_courseuser"
							." WHERE idCourse = '".$row['idCourse']."'";

				list($enrolled) = sql_fetch_row(sql_query($query));

				$row['enrolled'] = $enrolled;
				$row['create_date'] = Format::date($row['create_date'], 'date');
				$additional_info =	'<p class="course_support_info">'.Lang::t('_COURSE_INTRO', 'course', array(
										'[course_type]'		=> $row['course_type'],
										'[create_date]'		=> $row['create_date'],
										'[enrolled]'		=> $row['enrolled'],
										'[course_status]'	=> Lang::t($this->cstatus[$row['status']], 'course')))
									.'</p>';

				$action =	'<div class="catalog_action" style="top:5px;" id="action_'.$row['idCourse'].'">'
							.'<p class="can_subscribe">'
                            // add self_optin
							.str_replace(array('[login]', '[signin]'), array($login_link, $signin_link), ($register_type === 'self' || $register_type === 'self_optin' || $register_type === 'moderate' ? Lang::t('_REGISTER_FOR_COURSE', 'login') : Lang::t('_REGISTER_FOR_COURSE_NO_REG', 'login')))
							.'</p>'
							.'</div>';
			}

			$html .=	'<div class="dash-course">'
					.($row['use_logo_in_courselist'] && $row['img_course'] ? '<div class="logo_container"><img class="clogo" src="'.$path_course.$row['img_course'].'" alt="'.Util::purge($row['name']).'" /></div>' : '')
					.($row['use_logo_in_courselist'] && !$row['img_course'] ? '<div class="logo_container"><img class="clogo cnologo" src="'.Get::tmpl_path().'images/course/course_nologo.png'.'" alt="'.Util::purge($row['name']).'" /></div>' : '')
					.'<div class="info_container">'
					.'<h2>'
					.($row['lang_code'] != 'none' ? Get::img('language/'.strtolower($row['lang_code']).'.png', $row['lang_code']) : '' )
					.$row['name']
					.'</h2>'
					.$additional_info
					.'<p class="course_support_info">'.$row['description'].'</p>'
					.'<p class="course_support_info">'
					.($row['course_demo'] ? '<a href="index.php?r=homecatalogue/downloadDemoMaterial&amp;course_id='.$row['idCourse'].'" class="ico-wt-sprite subs_download"><span>'.Lang::t('_COURSE_DEMO', 'course').'</span></a>' : '')
					.'</p>'
					.'<p class="course_support_info">'
					.($row['code'] ? '<i style="font-size:.88em">['.$row['code'].']</i>' : '')
					.'</p>'
					.$action
					.'<div class="nofloat"></div>'
					.'</div>'
					.'</div>';
		}

		if(sql_num_rows($result) <= 0)
			$html = '<p>'.Lang::t('_NO_CONTENT', 'standard').'</p>';

		return $html;
	}

	public function courseSelectionInfo($id_course)
	{
		$query =	"SELECT course_type, selling"
					." FROM %lms_course"
					." WHERE idCourse = ".(int)$id_course;

		list($type, $selling) = sql_fetch_row(sql_query($query));
		$res['success'] = true;

		$res['title'] = Lang::t('_EDITION_LIST', 'catalogue');

		$res['body'] = '';

		$login_link = '<a href="index.php">'.Lang::t('_LOG_IN', 'login').'</a>';
		$signin_link = '<a href="' . Get::rel_path("base") . "/index.php?r=" . _register_ . '">'.Lang::t('_SIGN_IN', 'login').'</a>';

        require_once(\Forma::inc(_base_ . '/lib/lib.usermanager.php'));
		$option = new UserManagerOption();
		$register_type = $option->getOption('register_type');

		$action =	'<p class="can_subscribe">'
                    // add self_optin
					.str_replace(array('[login]', '[signin]'), array($login_link, $signin_link), ($register_type === 'self' || $register_type === 'self_optin' || $register_type === 'moderate' ? Lang::t('_REGISTER_FOR_COURSE', 'login') : Lang::t('_REGISTER_FOR_COURSE_NO_REG', 'login')))
					.'</p>';

		if($type === 'classroom')
		{
			$classrooms = $this->classroom_man->getCourseDate($id_course, false);

			foreach($classrooms as $classroom_info)
			{
				$res['body'] .=	'<div class="edition_container">'
								.Lang::t('_NAME', 'catalogue').': '.$classroom_info['name'].'<br/>'
								.($classroom_info['code'] !== '' ? Lang::t('_CODE', 'catalogue').': '.$classroom_info['code'].'<br/>' : '')
								.($classroom_info['date_begin'] !== '0000-00-00' ? Lang::t('_DATE_BEGIN', 'course').': '.Format::date($classroom_info['date_begin'], 'date').'<br/>' : '')
								.($classroom_info['date_end'] !== '0000-00-00' ? Lang::t('_DATE_END', 'course').': '.Format::date($classroom_info['date_end'], 'date').'<br/>' : '')
								.($selling == 1 ? Lang::t('_PRICE').' : '.$classroom_info['price'].' '.Get::sett('currency_symbol', '&euro;') : '')
								.'<div class="edition_subscribe">'
								.'</div>'
								.'</div>';
			}

			$res['footer'] = '<a href="javascript:;" onclick="hideDialog();"><p class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</p></a>';
		}
		else
		{
			$editions = $this->edition_man->getEditionAvailableWithInfo(Docebo::user()->getIdSt(), $id_course);

			foreach($editions as $edition_info)
			{
				$res['body'] .=	'<div class="edition_container">'
								.Lang::t('_NAME', 'catalogue').': '.$edition_info['name'].'<br/>'
								.($edition_info['code'] !== '' ? Lang::t('_CODE', 'catalogue').': '.$edition_info['code'].'<br/>' : '')
								.($edition_info['date_begin'] !== '0000-00-00' ? Lang::t('_DATE_BEGIN', 'course').': '.Format::date($edition_info['date_begin'], 'date').'<br/>' : '')
								.($edition_info['date_end'] !== '0000-00-00' ? Lang::t('_DATE_END', 'course').': '.Format::date($edition_info['date_end'], 'date').'<br/>' : '')
								.($selling == 1 ? Lang::t('_PRICE').' : '.$edition_info['price'].' '.Get::sett('currency_symbol', '&euro;') : '')
								.'<div class="edition_subscribe">'
								.'</div>'
								.'</div>';
			}

			$res['footer'] = '<div class="edition_cancel"><a href="javascript:;" onclick="hideDialog();"><span class="close_dialog">'.Lang::t('_CLOSE', 'catalogue').'</span></a></div>';
		}

		$res['body'] .= '<br/><br/>'.$action;

		return $res;
	}

 
 
    public function getMajorCategory($std_link, $only_son = false)
    {
        $query =    "SELECT idCategory, path, lev"
                    ." FROM %lms_category"
                    ." WHERE lev = 1"
                    ." ORDER BY path";

        $result = sql_query($query);
        $res = array();

        while(list($id_cat, $path, $level) = sql_fetch_row($result))
        {
            $name = end(explode('/', $path));
            $res[$id_cat] = $name;
        }

        return $res;
    } 
 
     
        public function GetGlobalJsonTree(){
            $global_tree = [];
            $top_category = $this->getMajorCategory();
            foreach ($top_category as $a_top_cat_key=>$val) {
                $this->tree_deep = 0;
                $this->the_tree = [];
                $this->children = $this->getMinorCategoryTree($a_top_cat_key);
                $this->GetChildTree(array_keys($this->children));
                if (count($this->the_tree)>0) {
                    $global_tree[] = array('text'=>$this->the_tree[0]['text'], 'nodes'=>$this->the_tree[0]['nodes'], "href" => "index.php?r=catalog/allCourse&id_cat=".$a_top_cat_key, "id_cat" => $a_top_cat_key);
                } else {
                    $global_tree[] = array('text'=>$val, "href" => "index.php?r=catalog/allCourse&id_cat=".$a_top_cat_key, "id_cat" => $a_top_cat_key);
                }    
            }
            return  $global_tree;              
            
              
        }

        public function getMinorCategoryTree($id_cat){
            $query_i =    "SELECT iLeft, iRight, idCategory"
            ." FROM %lms_category"
            ." WHERE idCategory = ".(int)$id_cat;
            list($i_left, $i_right) = sql_fetch_row(sql_query($query_i));

            $query =    "SELECT idCategory, path, idParent, lev"
            ." FROM %lms_category"
            ." WHERE iLeft >= ".(int)$i_left
            ." AND iRight <= ".$i_right
            ." ORDER BY lev";
            $result = sql_query($query);
            $res = array();

            while(list($id_cat, $path, $id_parent, $lev) = sql_fetch_row($result)){
                $name = end(explode('/', $path));

                $res[$id_cat]['name'] = $name;
                $res[$id_cat]['id_cat'] = $id_cat;
                if($id_parent != 0) {
                    $res[$id_parent]['son'][$id_cat] = $name;
                }    
            }
            return $res;            

        }        

        private function GetChildTree($array_k){
            $leaves = [];       
            foreach ($array_k as $single_key) {
                if (is_array($this->children[$single_key]['son'])) {
                    $this->tree_deep++;
                    $b = $this->GetChildTree(array_keys($this->children[$single_key]['son']));
                    
                    $leaves[] = array('text'=>$this->children[$single_key]['name'], 'nodes'=>$b, "href" => "index.php?r=homecatalogue/allCourse&id_cat=".$this->children[$single_key]['id_cat'],"id_cat" => $this->children[$single_key]['id_cat'] );
                    if  ($this->tree_deep==0){
                        $this->the_tree[] = array('text'=>$leaves[0]['text'], 'nodes'=>$leaves[0]['nodes'], "href" => "index.php?r=homecatalogue/allCourse&id_cat=".$this->children[$single_key]['id_cat'],"id_cat" => $this->children[$single_key]['id_cat'] );
                        $this->tree_deep = 0;                   
                    }
                } else {
                    $leaves[] = array('text'=>$this->children[$single_key]['name'],  "href" => "index.php?r=homecatalogue/allCourse&id_cat=".$this->children[$single_key]['id_cat'],"id_cat" => $this->children[$single_key]['id_cat']);
                }
                if (array_key_exists($single_key, $this->children)) {
                    unset($this->children[$single_key]);   
                }
            }
            
            
            $this->tree_deep--;
            return $leaves;
        }
    
    
    
}
?>