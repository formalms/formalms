<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

Class TransactionAlms extends Model {

	protected $acl_man;

	public function __construct()
	{
		$this->acl_man =& Docebo::user()->getAclManager();
	}

	public function getPerm()
	{
		return array(	'view' => 'standard/view.png',
						'mod' => 'standard/edit.png',
						'del' => 'standard/rem.png'
		);
	}

	public function getTotalTransaction($filter = false)
	{
		$query =	"SELECT COUNT(*)"
					." FROM %adm_transaction"
					." WHERE 1";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getTransaction($start_index, $results, $sort, $dir, $filter = false)
	{
		$query =	"SELECT t.id_trans, t.date_creation, t.date_activated, t.paid, SUM(ti.price) as price, u.userid, u.firstname, u.lastname"
					." FROM %adm_transaction as t"
					." JOIN %adm_transaction_info as ti ON t.id_trans = ti.id_trans"
					." JOIN %adm_user as u ON t.id_user = u.idst"
					." WHERE 1";

		$query .=	" GROUP BY t.id_trans";

		switch($sort)
		{
			case 'userid':
				$query .= " ORDER BY u.userid ".$dir;
			break;
			case 'firstname':
				$query .= " ORDER BY u.firstname ".$dir;
			break;
			case 'lastname':
				$query .= " ORDER BY u.lastname ".$dir;
			break;
			case 'date_creation':
				$query .= " ORDER BY t.date_creation ".$dir;
			break;
			case 'date_activated':
				$query .= " ORDER BY t.date_activated ".$dir;
			break;
		}

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
		{
			$row['userid'] = $this->acl_man->relativeId($row['userid']);
			$row['date_creation'] = Format::date($row['date_creation'], 'datetime');
			$row['date_activated'] = Format::date($row['date_activated'], 'datetime');
			$row['paid'] = ($row['paid'] == 1 ? Get::img('standard/status_active.png', Lang::t('_ACTIVATED', 'transaction')) : Get::img('standard/status_deactive.png', Lang::t('_NOT_ACTIVATED', 'transaction')));
			$row['edit'] = '<a href="index.php?r=alms/transaction/mod&amp;id_trans='.$row['id_trans'].'" title="'.Lang::t('_MOD', 'transaction').'">'.Get::img('standard/edit.png', Lang::t('_MOD', 'transaction')).'</a>';

			$res[] = $row;
		}

		return $res;
	}

	public function getTransactionInfo($id_trans)
	{
		$query =	"SELECT *"
					." FROM %adm_transaction"
					." WHERE id_trans = ".(int)$id_trans;

		$trans = sql_fetch_assoc(sql_query($query));

		$query =	"SELECT *"
					." FROM %adm_transaction_info"
					." WHERE id_trans = ".(int)$id_trans
					." ORDER BY code, name";

		$res = sql_query($query);
		$trans['product'] = array();
		$price = 0;

		while($row = sql_fetch_assoc($res))
		{
			$trans['product'][] = $row;
			$price += $row['price'];
		}

		$trans['price'] = $price;

		return $trans;
	}

	public function controlActivation($id_trans, $set_not_paid = false)
	{
		$query =	"SELECT COUNT(*), SUM(activated)"
					." FROM %adm_transaction_info"
					." WHERE id_trans = ".(int)$id_trans
					." GROUP BY id_trans";

		list($num_course, $course_activated) = sql_fetch_row(sql_query($query));

		if($num_course == $course_activated && !$set_not_paid)
		{
			$query =	"UPDATE %adm_transaction"
						." SET paid = '1',"
						." date_activated = '".date('Y-m-d H:i:s')."'"
						." WHERE id_trans = ".(int)$id_trans;

			return sql_query($query);
		}

		if($set_not_paid)
		{
			$query =	"UPDATE %adm_transaction"
						." SET paid = '0',"
						." date_activated = ''"
						." WHERE id_trans = ".(int)$id_trans;

			sql_query($query);

			$query =	"UPDATE %adm_transaction_info"
						." SET activated = '0'"
						." WHERE id_trans = ".(int)$id_trans;

			sql_query($query);

			$query =	"SELECT *"
						." FROM %adm_transaction_info"
						." WHERE id_trans = ".(int)$id_trans
						." ORDER BY code, name";

			$res = sql_query($query);

			while($row = sql_fetch_assoc($res))
			{
				$query =	"UPDATE %lms_course_user"
							." SET waiting = 1,"
							." status = -2"
							." WHERE idCourse = ".(int)$row['id_course']
							." AND idUser = ".(int)$row['id_user'];

				sql_query($query);
			}
		}

		return true;
	}

	public function saveTransaction($product, $id_trans, $id_user, $paid=false)
	{
		if(!is_array($product) || $id_user == 0)
			return false;

		if(count($product) == 0)
			return true;

		$res = true;

		require_once(_lms_.'/lib/lib.course.php');

		foreach($product as $key => $i)
		{
			$course_data = explode('_', $key);

			$query =	"UPDATE %lms_courseuser"
						." SET status = "._CUS_SUBSCRIBED.","
						." waiting = 0"
						." WHERE idCourse = ".(int)$course_data[0]
						." AND idUser = ".(int)$id_user;

			if(sql_query($query))
			{
				$query =	"UPDATE %adm_transaction_info"
							." SET activated = '1'"
							." WHERE id_trans = ".(int)$id_trans
							." AND id_course = ".(int)$course_data[0]
							." AND id_date = ".(int)$course_data[1]
							." AND id_edition = ".(int)$course_data[2];

				$res = sql_query($query);
			}
			else
				$res = false;

			if(!$res)
				return false;
		}

		if ($res && $paid) {
			$query ="UPDATE %adm_transaction"
							." SET paid = '1'"
							." WHERE id_trans = ".(int)$id_trans;
			$q =sql_query($query);
		}

		return true;
	}


	/**
	 * deleteTransaction
	 * @param <int> $id_trans
	 * @param <boolean> $rem_user_subscription
	 * @return boolean
	 */
	public function deleteTransaction($id_trans, $rem_user_subscription=false) {
		$res =false;

		$transaction_info = $this->getTransactionInfo($id_trans);
		$id_user =$transaction_info['id_user'];

		if ($rem_user_subscription) {
			foreach ($transaction_info['product'] as $prod) { // remove subscription request

				//require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
				$man_course		= new Man_Course();

				if ($prod['activated'] == 0) {

					$id_course =$prod['id_course'];
					$edition_id =$prod['id_edition'];

					$docebo_course = new DoceboCourse($id_course);
					$group_levels 	= $docebo_course->getCourseLevel($id_course);
					$waiting_users	= $man_course->getWaitingSubscribed($id_course);

					$level 		= $waiting_users['users_info'][$id_user]['level'];
					$s = new SubscriptionAlmsController;
					$result 	= $s->removeSubscription($id_course, $id_user, $group_levels[$level], $edition_id);
				}
			}
		}


		$qtxt ="DELETE FROM %adm_transaction_info WHERE id_trans = ".(int)$id_trans;
		$q1 =sql_query($qtxt);

		$qtxt ="DELETE FROM %adm_transaction WHERE id_trans = ".(int)$id_trans;
		$q2 =sql_query($qtxt);


		if ($q1 && $q2) {	$res =true;	}

		return $res;
	}


}

?>