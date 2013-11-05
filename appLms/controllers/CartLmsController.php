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

class CartLmsController extends LmsController {

	public $name = 'catalog';

	private $path_course = '';

	protected $_default_action = 'show';

	var $model;
	var $json;
	var $acl_man;

	public function isTabActive($tab_name)
	{
		return false;
	}

	public function init()
	{
		YuiLib::load('base,tabview');
		Lang::init('course');
		$this->path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';
		$this->model = new CartLms();

		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();

		$this->acl_man =& Docebo::user()->getAclManager();

		Util::get_css(Get::rel_path(_base_).'/appLms/views/cart/cart.css', true, true);
	}


	protected function checkCartTransaction($id_trans, $cart_id) {
		$req_code =md5($id_trans.$cart_id);
		$ses_code =md5($_SESSION['cart_transaction'].$_SESSION['cart_id']);

		$res =($req_code === $ses_code ? true : false);

		/* if (!$res) {
			echo $id_trans." -- ".$cart_id." .. ".$_SESSION['cart_transaction']." -- ".$_SESSION['cart_id'];
			die();
		} */

		return $res;
	}


	public function getPaypalUrl() {
		return "https://www.".(Get::sett('paypal_sandbox', 'off') === 'off' ? '' : 'sandbox.')."paypal.com/cgi-bin/webscr";
	}


	public function show()
	{
		$new =true;
		$total_price = $this->model->getTotalPrice();

		$id_trans = Get::req('id_transaction', DOTY_INT, 0);
		$cart_id = Get::gReq('cart', DOTY_MIXED, 0);

		require_once(_lms_.'/admin/models/TransactionAlms.php');
		$trman = new TransactionAlms();

		if (!empty($_GET['cancel'])) {
			$new =false;
		}

		if (!$new && !$this->checkCartTransaction($id_trans, $cart_id)) {
			UIFeedback::error(Lang::t('_INVALID_TRANSACTION', 'cart'));
		}
		else if(isset($_GET['cancel']) && $_GET['cancel'] == 1) {

			$trman->deleteTransaction($id_trans, true);

			UIFeedback::error(Lang::t('_TRANSACTION_ABORTED', 'cart'));
		}
		else if(isset($_GET['ok']) && $_GET['ok'] == 1) {

			UIFeedback::info(Lang::t('_COURSE_ACTIVATION_SUCCESS', 'cart'));
		}

		if(isset($_GET['error']) && $_GET['error'] == 1) {
			$new =false;
			UIFeedback::error(Lang::t('_TRANSACTION_CREATION_ERROR', 'cart'));
		}


		if ($new) {
			$_SESSION['cart_id']=time().substr(uniqid('cart'), 0, 20);
		}

		$this->render('show', array('total_price' => $total_price, 'paypal_url'=>$this->getPaypalUrl()));
	}


	public function paypalNotifyTask() {
		$debug =false; $log ='';

		if (!isset($GLOBALS['orig_post'])) { return false; }

		if ($debug) { file_put_contents('paypal.txt', print_r($_POST, true).print_r($_GET, true), FILE_APPEND); }

		$url_parsed=parse_url($this->getPaypalUrl());

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		foreach($GLOBALS['orig_post'] as $key=>$value) { // not filtered POST values
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		unset($GLOBALS['orig_post']);

		// post back to PayPal system to validate
		// use of HTTP/1.1 protocol as requested by paypal since 13-10-2013
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Host: ".$url_parsed['host']."\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";
		$header .= "Connection: close\r\n\r\n";
		$fp = fsockopen('ssl://'.$url_parsed['host'], 443, $errno, $errstr, 30);

		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];

		if (!$fp) {
			// HTTP ERROR
		}
		else {
			fputs($fp, $header . $req);
			while(!feof($fp)) {
				$res = fgets($fp, 1024);
				if (strcmp($res, "VERIFIED") == 0) {

					require_once (_lms_.'/admin/modules/subscribe/subscribe.php');
					require_once(_lms_.'/admin/models/TransactionAlms.php');

					$trman = new TransactionAlms();
					$man_course		= new Man_Course();
					$transaction_info = $trman->getTransactionInfo($item_number);
					$log.=print_r($transaction_info, true)."\n\n";
					$id_user =$transaction_info['id_user'];

					$total_price =0;
					foreach ($transaction_info['product'] as $prod) {
						$total_price+=$prod['price'];
					}

					$ok =true;
					// check that receiver_email is your Primary PayPal email
					$ok =($receiver_email == Get::sett('paypal_mail', '') ? $ok : false);
					// check the payment_status is Completed
					$ok =($payment_status == 'Completed' ? $ok : false);
					// check that payment_amount/payment_currency are correct
					$ok =($payment_amount == $total_price ? $ok : false);
					$ok =($payment_currency == Get::sett('paypal_currency', 'EUR') ? $ok : false);


					if ($ok) { // process payment

						$to_activate =array();
						foreach ($transaction_info['product'] as $prod) { // activate subscription requests

							if ($prod['activated'] == 0) {

								$id_course =$prod['id_course'];
								$edition_id =$prod['id_edition'];
								$id_date =$prod['id_date'];

								$to_activate[$id_course.'_'.$id_date.'_'.$edition_id]='1';
							}
						}
						if (!empty($to_activate)) {
							$trman->saveTransaction($to_activate, $item_number, $id_user, true);
						}
					}
					else if ($debug) {
						$log.=($receiver_email == Get::sett('paypal_mail', '') ? '' : "wrong receiver_email (".$receiver_email." != ".Get::sett('paypal_mail', '').")\n");
						$log.=($payment_status == 'Completed' ? '' : "wrong payment status (".$payment_status.")\n");
						$log.=($payment_amount == $transaction_info['price'] ? '' : "wrong price (".$payment_amount." != ".$total_price.")\n");
						$log.=($payment_currency == Get::sett('paypal_currency', 'EUR') ? '' : "wrong payment currency (".$payment_currency." != ".Get::sett('paypal_currency', 'EUR').")\n");
					}
				}
				else if (strcmp($res, "INVALID") == 0) {
					// log for manual investigation
					// echo "INVALID: ".$res;
				}
			}
			fclose($fp);
		}

		if ($debug) { file_put_contents('paypal.txt', "\n".$log.$url_parsed['host']." ".$req."\n".$res."\n----------------\n\n", FILE_APPEND); }
		die();
	}


	public function getCartList()
	{
		$sort = Get::req('sort', DOTY_MIXED, 'name');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$course_list = $this->model->getCartList();

		$result = array(
			'totalRecords' => count($course_list),
			'startIndex' => 0,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => count($course_list),
			'results' => count($course_list),
			'records' => $course_list
		);

		echo $this->json->encode($result);
	}

	public function emptyCart()
	{
		$_SESSION['lms_cart'] = array();

		$result['success'] = true;

		echo $this->json->encode($result);
	}

	public function delSelectedElement()
	{
		$del_list = Get::req('elements', DOTY_MIXED, '');
		$del_list = explode(',', $del_list);

		$cart = $_SESSION['lms_cart'];

		foreach($del_list as $id)
		{
			$id_details = explode('_', $id);

			if($id_details[1] != 0)
			{
				unset($cart[$id_details[0]]['classroom'][$id_details[1]]);
				if(empty($cart[$id_details[0]]['classroom']))
					unset($cart[$id_details[0]]);
			}
			elseif($id_details[2] != 0)
			{
				unset($cart[$id_details[0]]['edition'][$id_details[2]]);
				if(empty($cart[$id_details[0]]['edition']))
					unset($cart[$id_details[0]]);
			}
			else
			{
				unset($cart[$id_details[0]]);
			}
		}

		$_SESSION['lms_cart'] = $cart;

		require_once(_lms_.'/lib/lib.cart.php');

		$result = array(	'success' => true,
						'cart_element' => ''.Learning_Cart::cartItemCount().'',
						'price' => $this->model->getTotalPrice());

		echo $this->json->encode($result);
	}

	public function makeOrder()
	{
		$wire = Get::req('wire', DOTY_INT, 0);

		$cart = $_SESSION['lms_cart'];
		require_once(_lms_.'/lib/lib.cart.php');

		if(Learning_Cart::cartItemCount() > 0)
			$id_trans = $this->model->createTransaction();
		else
			$id_trans = false;

		if($id_trans === false)
		{
			$result = array(	'success' => false,
							'message' => UIFeedback::error(Lang::t('_ERROR_CREATE_TRANS', 'catalogue'), true));
		}
		else
		{
			$course_info = $this->model->getCartList(true);
			$total_price = 0;

			foreach($cart as $id_course => $extra)
			{
				$docebo_course = new DoceboCourse($id_course);

				require_once(_lms_.'/admin/models/SubscriptionAlms.php');

				$level_idst =& $docebo_course->getCourseLevel($id_course);

				if(count($level_idst) == 0 || $level_idst[1] == '')
					$level_idst =& $docebo_course->createCourseLevel($id_course);

				$waiting = 1;

				$this->acl_man->addToGroup($level_idst[3], Docebo::user()->getIdSt());

				if(isset($extra['classroom']))
				{
					foreach($extra['classroom'] as $id_date)
					{
						$model = new SubscriptionAlms($id_course, 0, $id_date);
						if(!$model->subscribeUser(Docebo::user()->getIdSt(), 3, $waiting))
							$this->acl_man->removeFromGroup($level_idst[3], Docebo::user()->getIdSt());
						elseif($this->model->addTransactionCourse($id_trans, $id_course, $id_date, 0, $course_info[$id_course.'_'.$id_date.'_0']))
						{
							unset($_SESSION['lms_cart'][$id_course]['classroom'][$id_date]);
							$query =	"UPDATE %lms_courseuser"
										." SET status = '-2'"
										." WHERE idUser = ".Docebo::user()->getIdSt()
										." AND idCourse = ".$id_course;

							sql_query($query);

							$total_price += $course_info[$id_course.'_'.$id_date.'_0']['price'];
						}
					}
				}
				elseif(isset($extra['edition']))
				{
					foreach($extra['edition'] as $id_edition)
					{
						$model = new SubscriptionAlms($id_course, $id_edition, 0);
						if(!$model->subscribeUser(Docebo::user()->getIdSt(), 3, $waiting))
							$this->acl_man->removeFromGroup($level_idst[3], Docebo::user()->getIdSt());
						elseif($this->model->addTransactionCourse($id_trans, $id_course, 0, $id_edition, $course_info[$id_course.'_0_'.$id_edition]))
						{
							unset($_SESSION['lms_cart'][$id_course]['edition'][$id_edition]);
							$query =	"UPDATE %lms_courseuser"
										." SET status = '-2'"
										." WHERE idUser = ".Docebo::user()->getIdSt()
										." AND idCourse = ".$id_course;

							sql_query($query);

							$total_price += $course_info[$id_course.'_0_'.$id_edition]['price'];
						}
					}
				}
				else
				{
					$model = new SubscriptionAlms($id_course, 0, 0);
					if(!$model->subscribeUser(Docebo::user()->getIdSt(), 3, $waiting))
						$this->acl_man->removeFromGroup($level_idst[3], Docebo::user()->getIdSt());
					elseif($this->model->addTransactionCourse($id_trans, $id_course, 0, 0, $course_info[$id_course.'_0_0']))
					{
						unset($_SESSION['lms_cart'][$id_course]);
						$query =	"UPDATE %lms_courseuser"
									." SET status = '-2'"
									." WHERE idUser = ".Docebo::user()->getIdSt()
									." AND idCourse = ".$id_course;

						sql_query($query);

						$total_price += $course_info[$id_course.'_0_0']['price'];
					}
				}
			}

			require_once(_lms_.'/lib/lib.cart.php');
			if(Learning_Cart::cartItemCount() == 0)
				$_SESSION['lms_cart'] = array();

			$_SESSION['cart_transaction']=$id_trans;
			$result = array(	'success' => true,
								'message' => UIFeedback::info(Lang::t('_TRANS_CREATED', 'catalogue'), true),
								'id_transaction' => $id_trans,
								'total_price' => $total_price,
								'link' => Get::sett('url')._folder_lms_.'/index.php?r=cart/show&id_transaction='.$id_trans.'&cart='.$_SESSION['cart_id'],
			);
		}

		if($wire)
		{
			if($result['success'])
				Util::jump_to('index.php?r=cart/wireInfo&id_transaction='.$id_trans);
			Util::jump_to('index.php?r=cart/show&error=1');
		}
		else
			echo $this->json->encode($result);
	}

	public function wireInfo()
	{
		require_once(_lms_.'/admin/models/TransactionAlms.php');
		$model = new TransactionAlms();

		$id_trans = Get::req('id_transaction', DOTY_INT, 0);

		$transaction_info = $model->getTransactionInfo($id_trans);

		$total_price = 0;

		foreach($transaction_info['product'] as $product_info)
			$total_price += $product_info['price'];

		if($id_trans != 0)
			UIFeedback::info(Lang::t('_TRANS_CREATED', 'cart'));

		$this->render('wire', array('transaction_info' => $transaction_info, 'total_price' => $total_price));
	}
}
