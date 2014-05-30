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

class CartLms extends Model
{
	var $edition_man;
	var $course_man;
	var $classroom_man;

	var $cstatus;
	var $acl_man;

	public function  __construct()
	{
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.edition.php');
		require_once(_lms_.'/lib/lib.date.php');

		$this->course_man = new Man_Course();
		$this->edition_man = new EditionManager();
		$this->classroom_man = new DateManager();

		$this->acl_man =& Docebo::user()->getAclManager();
	}

	public function getCartList($old_key = false)
	{
		$sort = Get::req('sort', DOTY_MIXED, 'name');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$cart = $_SESSION['lms_cart'];
		$order = array();
		$cont = array();

		foreach($cart as $id_course => $extra)
		{
			if(is_array($extra))
			{
				if(isset($extra['classroom']))
				{
					foreach($extra['classroom'] as $id_date)
					{
						$course_info = $this->classroom_man->getDateInfo($id_date);

						switch($sort)
						{
							case 'code':
								$order[$id_course.'_'.$id_date.'_0'] = $course_info['code'];
							break;
							case 'price':
								$order[$id_course.'_'.$id_date.'_0'] = $course_info['price'];
							break;
							case 'date_begin':
								$order[$id_course.'_'.$id_date.'_0'] = $course_info['date_begin'];
							break;
							case 'date_end':
								$order[$id_course.'_'.$id_date.'_0'] = $course_info['date_end'];
							break;
							case 'type':
								$order[$id_course.'_'.$id_date.'_0'] = Lang::t('_CLASSROOM_COURSE', 'cart');
							break;
							case 'name':
							default:
								$order[$id_course.'_'.$id_date.'_0'] = $course_info['name'];
							break;
						}

						$cont[$id_course.'_'.$id_date.'_0']['id'] = $id_course.'_'.$id_date.'_0';
						$cont[$id_course.'_'.$id_date.'_0']['code'] = $course_info['code'];
						$cont[$id_course.'_'.$id_date.'_0']['name'] = $course_info['name'];
						$cont[$id_course.'_'.$id_date.'_0']['type'] = Lang::t('_CLASSROOM_COURSE', 'cart');
						$cont[$id_course.'_'.$id_date.'_0']['date_begin'] = Format::date($course_info['date_begin'], 'date');
						$cont[$id_course.'_'.$id_date.'_0']['date_end'] = Format::date($course_info['date_end'], 'date');
						$cont[$id_course.'_'.$id_date.'_0']['price'] = $course_info['price'];
					}
				}
				else
				{
					foreach($extra['edition'] as $id_edition)
					{
						$course_info = $this->edition_man->getEditionInfo($id_edition);

						switch($sort)
						{
							case 'code':
								$order[$id_course.'_0_'.$id_edition] = $course_info['code'];
							break;
							case 'price':
								$order[$id_course.'_0_'.$id_edition] = $course_info['price'];
							break;
							case 'date_begin':
								$order[$id_course.'_0_'.$id_edition] = $course_info['date_begin'];
							break;
							case 'date_end':
								$order[$id_course.'_0_'.$id_edition] = $course_info['date_end'];
							break;
							case 'type':
								$order[$id_course.'_0_'.$id_edition] = Lang::t('_LEARNING_COURSE', 'cart');
							break;
							case 'name':
							default:
								$order[$id_course.'_0_'.$id_edition] = $course_info['name'];
							break;
						}

						$cont[$id_course.'_0_'.$id_edition]['id'] = $id_course.'_0_'.$id_edition;
						$cont[$id_course.'_0_'.$id_edition]['code'] = $course_info['code'];
						$cont[$id_course.'_0_'.$id_edition]['name'] = $course_info['name'];
						$cont[$id_course.'_0_'.$id_edition]['type'] = Lang::t('_LEARNING_COURSE', 'cart');
						$cont[$id_course.'_0_'.$id_edition]['date_begin'] = Format::date($course_info['date_begin'], 'date');
						$cont[$id_course.'_0_'.$id_edition]['date_end'] = Format::date($course_info['date_end'], 'date');
						$cont[$id_course.'_0_'.$id_edition]['price'] = $course_info['price'];
					}
				}
			}
			else
			{
				$course_info = $this->course_man->getCourseInfo($id_course);

				switch($sort)
				{
					case 'code':
						$order[$id_course.'_0_0'] = $course_info['code'];
					break;
					case 'price':
						$order[$id_course.'_0_0'] = ($course_info['advance'] !== '' && $course_info['advance'] >= 0 ? $course_info['advance'] : $course_info['prize']);
					break;
					case 'date_begin':
						$order[$id_course.'_0_0'] = $course_info['date_begin'];
					break;
					case 'date_end':
						$order[$id_course.'_0_0'] = $course_info['date_end'];
					break;
					case 'type':
						$order[$id_course.'_0_0'] = Lang::t('_LEARNING_COURSE', 'cart');
					break;
					case 'name':
					default:
						$order[$id_course.'_0_0'] = $course_info['name'];
					break;
				}

				$cont[$id_course.'_0_0']['id'] = $id_course.'_0_0';
				$cont[$id_course.'_0_0']['code'] = $course_info['code'];
				$cont[$id_course.'_0_0']['name'] = $course_info['name'];
				$cont[$id_course.'_0_0']['type'] = Lang::t('_LEARNING_COURSE', 'cart');
				$cont[$id_course.'_0_0']['date_begin'] = Format::date($course_info['date_begin'], 'date');
				$cont[$id_course.'_0_0']['date_end'] = Format::date($course_info['date_end'], 'date');
				$cont[$id_course.'_0_0']['price'] = ($course_info['advance'] !== '' && $course_info['advance'] >= 0 ? $course_info['advance'] : $course_info['prize']);
			}
		}//End foreach

		if($dir === 'desc')
			arsort($order);
		else
			asort($order);

		$res = array();

		foreach($order as $key => $not_needed)
			if($old_key)
				$res[$key] = $cont[$key];
			else
				$res[] = $cont[$key];

		return $res;
	}

	public function getTotalPrice()
	{
		$cart = $_SESSION['lms_cart'];
		$total_price = 0;

		foreach($cart as $id_course => $extra)
		{
			if(is_array($extra))
			{
				if(isset($extra['classroom']))
				{
					foreach($extra['classroom'] as $id_date)
					{
						list($price) = sql_fetch_row(sql_query("SELECT price FROM %lms_course_date WHERE id_date = ".(int)$id_date));

						$total_price += $price;
					}
				}
				else
				{
					foreach($extra['edition'] as $id_edition)
					{
						list($price) = sql_fetch_row(sql_query("SELECT price FROM %lms_course_editions WHERE id_edition = ".(int)$id_edition));

						$total_price += $price;
					}
				}
			}
			else
			{
				list($price, $advance) = sql_fetch_row(sql_query("SELECT prize, advance FROM %lms_course WHERE idCourse = ".(int)$id_course));
				if($advance !== '' && $advance >= 0)
					$total_price += $advance;
				else
					$total_price += $price;
			}
		}//End foreach

		return $total_price;
	}

	public function createTransaction()
	{
		$query =	"INSERT INTO %adm_transaction (id_trans, id_user, location, date_creation)"
					." VALUES (NULL, '".Docebo::user()->getIdSt()."', 'lms', '".date('Y-m-d H:i:s')."')";

		$res = sql_query($query);

		if($res)
			return sql_insert_id();
		else
			return false;
	}

	public function addTransactionCourse($id_trans, $id_course, $id_date, $id_edition, $course_info)
	{
		$query =	"INSERT INTO %adm_transaction_info (id_trans, id_course, id_date, id_edition, code, name, price)"
					." VALUES ('".$id_trans."', '".$id_course."', '".$id_date."', '".$id_edition."', '".$course_info['code']."', '".str_replace ("'","''",$course_info['name'])."', '".$course_info['price']."')";

		return sql_query($query);
	}
}

?>