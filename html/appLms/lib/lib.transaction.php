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

class Man_Transaction
{
	var $table_transaction;
	var $table_transaction_info;
	var $table_user;
	var $table_courseuser;

	public function __construct()
	{
		$this->table_transaction = $GLOBALS['prefix_lms'].'_transaction';
		$this->table_transaction_info = $GLOBALS['prefix_lms'].'_transaction_info';
		$this->table_user = $GLOBALS['prefix_fw'].'_user';
		$this->table_courseuser = $GLOBALS['prefix_lms'].'_courseuser';
	}

	public function __destruct()
	{

	}

	public function buyCourse($array_course, $method)
	{
		$query_info =	"INSERT INTO ".$this->table_transaction_info
						." (id_transaction, id_course, id_date)"
						." VALUES ";

		$first = true;
		$tot_price = 0;

		foreach($array_course as $course_info)
		{
			if(isset($course_info['id_date']))
			{
				if($first)
				{
					$first = false;
					$query_info .= " ([transaction_id], ".$course_info['id_course'].", ".$course_info['id_date'].")";
				}
				else
					$query_info .= ", ([transaction_id], ".$course_info['id_course'].", ".$course_info['id_date'].")";

				$tot_price += $course_info['price'];
			}
			else
			{
				if($first)
				{
					$first = false;
					$query_info .= " ([transaction_id], ".$course_info['idCourse'].", 0)";
				}
				else
					$query_info .= ", ([transaction_id], ".$course_info['idCourse'].", 0)";

				$tot_price += $course_info['prize'];
			}
		}

		$query =	"INSERT INTO ".$this->table_transaction
					." (id_transaction, id_user, date, price, method)"
					." VALUES (NULL, ".getLogUserId().", '".date('Y-m-d H:i:s')."', '".$tot_price."', '".$method."')";

		if(sql_query($query))
		{
			list($id_t) = sql_fetch_row(sql_query("SELECT id_transaction FROM ".$this->table_transaction." WHERE id_user = ".getLogUserId()." ORDER BY `date` LIMIT 0,1"));

			$query_info = str_replace('[transaction_id]', $id_t, $query_info);

			return sql_query($query_info);
		}

		return false;
	}

	public function getTransactionInfo($id_transaction)
	{
		$query =	"SELECT *"
					." FROM ".$this->table_transaction
					." WHERE id_transaction = ".$id_transaction;

		$result = sql_query($query);

		while($row = sql_fetch_assoc($result))
			$res = $row;

		return $res;
	}

	public function getTransactionCourses($id_transaction)
	{
		$query =	"SELECT id_course, id_date"
					." FROM ".$this->table_transaction_info
					." WHERE id_transaction = ".$id_transaction;

		$result = sql_query($query);
		$res = array();

		while(list($id_course, $id_date) = sql_fetch_row($result))
			if($id_date == 0)
				$res[$id_course] = $id_course;
			else
				$res[$id_course]['dates'][$id_date] = $id_date;

		return $res;
	}

	public function controlActivation($id_transaction, $id_course, $id_date = 0)
	{
		$query =	"SELECT activated"
					." FROM ".$this->table_transaction_info
					." WHERE id_transaction = ".$id_transaction
					." AND id_course = ".$id_course
					." AND id_date = ".$id_date;

		list($control) = sql_fetch_row(sql_query($query));

		if($control > 0)
			return true;
		return false;
	}

	public function getTransaction($limit = 0, $payment_status = false, $course_status = false, $tran = '')
	{
		$query =	"SELECT t.*, u.userid, u.firstname, u.lastname"
					." FROM ".$this->table_transaction." AS t"
					." LEFT JOIN ".$this->table_user." AS u ON u.idst = t.id_user"
					." WHERE 1";

		if($payment_status !== false)
			$query .= " AND t.payment_status = ".$payment_status;

		if($course_status !== false)
			$query .= " AND t.course_status = ".$course_status;

		if($tran !== '')
			$query .=	" AND "
						." ("
						." u.userid LIKE '%".$tran."%'"
						." OR u.firstname LIKE '%".$tran."%'"
						." OR u.lastname LIKE '%".$tran."%'"
						." OR t.id_transaction = '".$tran."'"
						." )";

		$query .=	" ORDER BY `date`"
					." LIMIT ".$limit.", ".Get::sett('visuItem');

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[] = $row;

		return $res;
	}

	public function getTotTransaction($payment_status = false, $course_status = false, $tran = '')
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->table_transaction." AS t"
					." LEFT JOIN ".$this->table_user." AS u ON u.idst = t.id_user"
					." WHERE 1";

		if($payment_status !== false)
			$query .= " AND t.payment_status = ".$payment_status;

		if($course_status !== false)
			$query .= " AND t.payment_status = ".$course_status;

		if($tran !== '')
			$query .=	" AND "
						." ("
						." u.userid LIKE '%".$tran."%'"
						." OR u.firstname LIKE '%".$tran."%'"
						." OR u.lastname LIKE '%".$tran."%'"
						." OR t.id_transaction = '".$tran."'"
						." )";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function delTransaction($id_transaction)
	{
		$query =	"DELETE FROM ".$this->table_transaction_info
					." WHERE id_transaction = ".$id_transaction;

		if(sql_query($query))
		{
			$query =	"DELETE FROM ".$this->table_transaction
						." WHERE id_transaction = ".$id_transaction;

			if(sql_query($query))
				return true;
		}

		return false;
	}

	public function updateTransaction($id_transaction, $payment_status, $course_status, $note)
	{
		$query =	"UPDATE ".$this->table_transaction
					." SET payment_status = ".$payment_status.","
					." course_status = ".$course_status.","
					." note = '".$note."'"
					." WHERE id_transaction = ".$id_transaction;

		return sql_query($query);
	}

	public function activateCourses($id_transaction, $id_user, $activations)
	{
		require_once (_lms_.'/lib/lib.subscribe.php');
		//require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
		require_once (_lms_.'/lib/lib.date.php');
		require_once (_lms_.'/lib/lib.course.php');

		$subscribe_man = new CourseSubscribe_Management();
		$date_man = new DateManager();
		$acl_man =& Docebo::user()->getAclManager();

		$query =	"SELECT idCourse"
					." FROM ".$this->table_courseuser
					." WHERE idUser = ".$id_user;

		$result = sql_query($query);
		$courses = array();

		while(list($id_course) = sql_fetch_row($result))
			$courses[$id_course] = $id_course;

		$dates = $date_man->getUserDates($id_user);

		foreach($activations as $id_course => $details)
		{
			$docebo_course = new DoceboCourse($id_course);
			
			$level_idst =& $docebo_course->getCourseLevel($id_course);

			if(count($level_idst) == 0)
				$level_idst =& $docebo_course->createCourseLevel($id_course);

			if(is_array($details))
			{
				foreach($details['dates'] as $id_date)
				{
					if(array_search($id_course, $courses) !== false)
					{
						if(array_search($id_date, $dates) === false)
						{
							if(!$date_man->addUserToDate($id_date, $id_user, getLogUserId()))
								return false;
							else
							{
								$query_up =	"UPDATE ".$this->table_transaction_info
											." SET activated = 1"
											." WHERE id_transaction = ".$id_transaction
											." AND id_course = ".$id_course
											." AND id_date = ".$id_date;

								if(!sql_query($query_up))
									return false;
							}
						}
						else
						{
							$query_up =	"UPDATE ".$this->table_transaction_info
										." SET activated = 1"
										." WHERE id_transaction = ".$id_transaction
										." AND id_course = ".$id_course
										." AND id_date = ".$id_date;

							if(!sql_query($query_up))
								return false;
						}
					}
					else
					{
						$acl_man->addToGroup($level_idst[3], $id_user);

						$re = sql_query(	"INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
											(idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)
											VALUES ('".$id_user."', '".$id_course."', '0', '3', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."')");

						if($re)
						{
							$courses[$id_course] = $id_course;

							addUserToTimeTable($id_user, $id_course, 0);

							if(!$date_man->addUserToDate($id_date, $id_user, getLogUserId()))
								return false;
							else
							{
								$query_up =	"UPDATE ".$this->table_transaction_info
											." SET activated = 1"
											." WHERE id_transaction = ".$id_transaction
											." AND id_course = ".$id_course
											." AND id_date = ".$id_date;

								if(!sql_query($query_up))
									return false;
							}
						}
						else
							return false;
					}
				}
			}
			else
			{
				if(array_search($id_course, $courses) !== false)
				{
					$query_up =	"UPDATE ".$this->table_transaction_info
								." SET activated = 1"
								." WHERE id_transaction = ".$id_transaction
								." AND id_course = ".$id_course
								." AND id_date = 0";

					if(!sql_query($query_up))
						return false;
				}
				else
				{
					$acl_man->addToGroup($level_idst[3], $id_user);

					$re = sql_query(	"INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
										(idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr)
										VALUES ('".$id_user."', '".$id_course."', '0', '3', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."')");
					if($re)
					{
						$courses[$id_course] = $id_course;

						addUserToTimeTable($id_user, $id_course, 0);

						$query_up =	"UPDATE ".$this->table_transaction_info
									." SET activated = 1"
									." WHERE id_transaction = ".$id_transaction
									." AND id_course = ".$id_course
									." AND id_date = 0";

						if(!sql_query($query_up))
							return false;
					}
					else
						return false;
				}
			}
		}

		return true;
	}
}
?>