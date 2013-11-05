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

if(Docebo::user()->isAnonymous()) die("You can't access");

function transaction()
{
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.dialog.php');

	YuiLib::load();

	$lang =& DoceboLanguage::createInstance('transaction');
	$acl_man = Docebo::user()->getAclManager();
	$man_transaction = new Man_Transaction();

	cout(	getTitleArea($lang->def('_TRANSACTION'))
			.'<div class="std_block">');

	$tb = new Table(Get::sett('visuItem'), $lang->def('_TRANSACTION_TABLE'), $lang->def('_TRANSACTION_TABLE'));

	$tb->initNavBar('ini', 'button');
	$tb->setLink('index.php?modname=transaction&amp;op=transaction');
	$page = ($tb->getSelectedPage() - 1) * Get::sett('visuItem');

	$status_filter = Get::req('payment_status', DOTY_INT, '-2');
	$course_filter = Get::req('course_status', DOTY_INT, '-2');
	$tran_filter = Get::req('tran', DOTY_MIXED, '');

	$display = false;

	if($status_filter != -2)
		$display = true;

	if($course_filter != -2)
		$display = true;

	$transactions = $man_transaction->getTransaction($page, ($status_filter == '-2' ? false : $status_filter), ($course_filter == '-2' ? false : $course_filter), $tran_filter);
	$tot_transaction = $man_transaction->getTotTransaction(($status_filter == '-2' ? false : $status_filter), ($course_filter == '-2' ? false : $course_filter), $tran_filter);

	if(count($transactions) > 0 || $display)
	{
		$mod_img = '<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />';
		$del_img = '<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" />';

		$cont_h = array($lang->def('_USER'),
						$lang->def('_DATE'),
						$lang->def('_COURSE_PRIZE'),
						$lang->def('_PAYMENT_METHOD'),
						$lang->def('_PAYMENT_STATUS'),
						$lang->def('COURSE_STATUS'),
						$mod_img,
						$del_img);

		$type_h = array('', '', '', '', 'image', 'image', 'image', 'image');

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		foreach($transactions as $transaction_info)
		{
			if($transaction_info['firstname'] !== '' && $transaction_info['lastname'] !== '')
				$user = $transaction_info['firstname'].' '.$transaction_info['lastname'].' ('.$acl_man->relativeId($transaction_info['userid']).')';
			elseif($transaction_info['firstname'] !== '')
				$user = $transaction_info['firstname'].' ('.$acl_man->relativeId($transaction_info['userid']).')';
			elseif($transaction_info['lastname'] !== '')
				$user = $transaction_info['lastname'].' ('.$acl_man->relativeId($transaction_info['userid']).')';
			else
				$user = $acl_man->relativeId($transaction_info['userid']);

			switch($transaction_info['payment_status'])
			{
				case '-1':
					$payment_status = '<img src="'.getPathImage().'/standard/dot_red.gif" alt="'.$lang->def('_CANCELLED').'" title="'.$lang->def('_CANCELLED').'" />';
				break;
				case '0':
					$payment_status = '<img src="'.getPathImage().'/standard/dot_grey.gif" alt="'.$lang->def('_WAITING_PAYMENT').'" title="'.$lang->def('_WAITING_PAYMENT').'" />';
				break;
				case '1':
					$payment_status = '<img src="'.getPathImage().'/standard/dot_yellow.gif" alt="'.$lang->def('_PARTIAL_PAID').'" title="'.$lang->def('_PARTIAL_PAID').'" />';
				break;
				case '2':
					$payment_status = '<img src="'.getPathImage().'/standard/dot_green.gif" alt="'.$lang->def('_PAID').'" title="'.$lang->def('_PAID').'" />';
				break;
			}

			switch($transaction_info['course_status'])
			{
				case '-1':
					$course_status = '<img src="'.getPathImage().'/standard/dot_red.gif" alt="'.$lang->def('_CANCELLED').'" title="'.$lang->def('_CANCELLED').'" />';
				break;
				case '0':
					$course_status = '<img src="'.getPathImage().'/standard/dot_grey.gif" alt="'.$lang->def('_NO_COURSE_ACTIVATED').'" title="'.$lang->def('_NO_COURSE_ACTIVATED').'" />';
				break;
				case '1':
					$course_status = '<img src="'.getPathImage().'/standard/dot_yellow.gif" alt="'.$lang->def('_SOME_COURSE_ACTIVATED').'" title="'.$lang->def('_SOME_COURSE_ACTIVATED').'" />';
				break;
				case '2':
					$course_status = '<img src="'.getPathImage().'/standard/dot_green.gif" alt="'.$lang->def('_ALL_COURSE_ACTIVATED').'" title="'.$lang->def('_ALL_COURSE_ACTIVATED').'" />';
				break;
			}
			
			$tb->addBody(array(	$user,
								Format::date($transaction_info['date']),
								$transaction_info['price'],
								$lang->def('_'.strtoupper($transaction_info['method'])),
								$payment_status,
								$course_status,
								'<a href="index.php?modname=transaction&amp;op=mod&amp;id='.$transaction_info['id_transaction'].'">'.$mod_img.'</a>',
								'<a href="index.php?modname=transaction&amp;op=del&amp;id='.$transaction_info['id_transaction'].'">'.$del_img.'</a>'));
		}

		$array_payment_status = array(	'-2' => $lang->def('_ALL_STATUS'),
										'-1' => $lang->def('_CANCELLED'),
										'0' => $lang->def('_WAITING_PAYMENT'),
										'1' => $lang->def('_PARTIAL_PAID'),
										'2' => $lang->def('_PAID'));

		$array_course_status = array(	'-2' => $lang->def('_ALL_STATUS'),
										'-1' => $lang->def('_CANCELLED'),
										'0' => $lang->def('_NO_COURSE_ACTIVATED'),
										'1' => $lang->def('_SOME_COURSE_ACTIVATED'),
										'2' => $lang->def('_ALL_COURSE_ACTIVATED'));

		cout(	Form::openForm('transaction_filter', 'index.php?modname=transaction&amp;op=transaction')
				.$tb->getNavBar($page, $tot_transaction)
				.'<div class="quick_search_form">'
				.Form::getInputTextfield('search_t', 'tran', 'tran', $tran_filter, '', 255, '')
				.Form::getButton( "filter", "filter", $lang->def('_FILTER'), "search_b")
				.'<br />'
				.'<a class="advanced_search" href="javascript:;" onclick="( this.nextSibling.style.display != \'block\' ?  this.nextSibling.style.display = \'block\' : this.nextSibling.style.display = \'none\' );">'
				.$lang->def("_ADVANCED_SEARCH")
				.'</a>'
				.'<div class="overlay_menu" style="display:'.($display ? 'block' : 'none').'">'
				.Form::getDropdown($lang->def('_PAYMENT_STATUS_FILTER'), 'payment_status', 'payment_status', $array_payment_status, $status_filter)
				.Form::getDropdown($lang->def('_COURSE_STATUS_FILTER'), 'course_status', 'course_status', $array_course_status, $course_filter)
				.'</div>'
				.'</div>'
				.'<script type="text/javascript">'
				.'var payment_status = YAHOO.util.Dom.get(\'payment_status\');'
				.'var course_status = YAHOO.util.Dom.get(\'course_status\');'
				.'var form = YAHOO.util.Dom.get(\'transaction_filter\');'
				.'YAHOO.util.Event.on(payment_status, \'change\', function() { this.submit() } , form, true);'
				.'YAHOO.util.Event.on(course_status, \'change\', function() { this.submit() } , form, true);'
				.'</script>'
				.$tb->getTable()
				.$tb->getNavBar($page, $tot_transaction)
				.Form::closeForm());

		setupHrefDialogBox('a[href*=del]');
	} else {
		cout( Lang::t('_NO_CONTENT', 'transaction') );
	}

	cout('</div>');
}

function delTransaction()
{
	$man_transaction = new Man_Transaction();

	if(Get::req('confirm', DOTY_INT, false))
	{
		$id_tran = Get::req('id', DOTY_INT, 0);

		if($man_transaction->delTransaction($id_tran))
			Util::jump_to('index.php?modname=transaction&op=transaction&res=ok');
		Util::jump_to('index.php?modname=transaction&op=transaction&res=err_del');
	}
}

function modTransaction()
{
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.dialog.php');
	require_once(_lms_.'/lib/lib.course.php');
	require_once(_lms_.'/lib/lib.date.php');

	$lang =& DoceboLanguage::createInstance('transaction');
	$acl_man = Docebo::user()->getAclManager();

	$man_transaction = new Man_Transaction();
	$course_man = new Man_Course();
	$date_man = new DateManager();

	$id_transaction = Get::req('id', DOTY_INT, 0);

	$transaction_info = $man_transaction->getTransactionInfo($id_transaction);

	if(isset($_POST['update']))
	{
		$payment_status = Get::req('payment_status', DOTY_INT, 0);
		$course_status = Get::req('course_status', DOTY_INT, 0);
		$note = Get::req('note', DOTY_MIXED, '');
		
		if($man_transaction->updateTransaction($id_transaction, $payment_status, $course_status, $note))
		{
			if(isset($_POST['confirm']))
			{
				$activations = array();

				foreach($_POST['confirm'] as $id => $n)
				{
					list($id_course, $id_date) = explode('_', $id);

					if($id_date != 0)
						$activations[$id_course]['dates'][$id_date] = $id_date;
					else
						$activations[$id_course] = $id_course;
				}

				if($man_transaction->activateCourses($id_transaction, $transaction_info['id_user'], $activations))
					Util::jump_to('index.php?modname=transaction&op=transaction&res=ok');
			}
			else
				Util::jump_to('index.php?modname=transaction&op=transaction&res=ok');
		}
		Util::jump_to('index.php?modname=transaction&op=transaction&res=err_up');
	}

	$array_title = array(	'index.php?modname=transaction&amp;op=transaction' => $lang->def('_TRANSACTION'),
							$lang->def('_MOD_TRANSACTION'));
	//Status info & note
	$array_payment_status = array(	'-1' => $lang->def('_CANCELLED'),
									'0' => $lang->def('_WAITING_PAYMENT'),
									'1' => $lang->def('_PARTIAL_PAID'),
									'2' => $lang->def('_PAID'));

	$array_course_status = array(	'-1' => $lang->def('_CANCELLED'),
									'0' => $lang->def('_NO_COURSE_ACTIVATED'),
									'1' => $lang->def('_SOME_COURSE_ACTIVATED'),
									'2' => $lang->def('_ALL_COURSE_ACTIVATED'));
	
	cout(	getTitleArea($array_title)
			.'<div class="std_block">'
			.Form::openForm('transaction_info', 'index.php?modname=transaction&amp;op=mod&amp;id='.$id_transaction)
			.Form::openElementSpace()
			.Form::getDropdown($lang->def('_PAYMENT_STATUS_FILTER'), 'payment_status', 'payment_status', $array_payment_status, $transaction_info['payment_status'])
			.Form::getDropdown($lang->def('_COURSE_STATUS_FILTER'), 'course_status', 'course_status', $array_course_status, $transaction_info['course_status'])
			.Form::getSimpleTextarea($lang->def('_NOTES'), 'note', 'note', $transaction_info['note'])
			.Form::closeElementSpace());
	//User info
	$user_info = $acl_man->getUser($transaction_info['id_user'], false);

	$tb_user = new Table(0, $lang->def('_USER_INFO'), $lang->def('_USER_INFO'));

	$cont_h = array($lang->def('_USERNAME'),
					$lang->def('_FIRSTNAME'),
					$lang->def('_LASTNAME'),
					$lang->def('_EMAIL'));

	$type_h = array('', '', '', '');

	$tb_user->setColsStyle($type_h);
	$tb_user->addHead($cont_h);
	$tb_user->addBody(array($acl_man->relativeId($user_info[ACL_INFO_USERID]),
							$user_info[ACL_INFO_FIRSTNAME],
							$user_info[ACL_INFO_LASTNAME],
							$user_info[ACL_INFO_EMAIL]));

	cout(	'<br />'
			.$tb_user->getTable());
	//Payment info if we need it

	//Product info
	$tb_product = new Table(0, $lang->def('_PRODUCT_INFO'), $lang->def('_PRODUCT_INFO'));

	$cont_h = array($lang->def('_CODE'),
					$lang->def('_NAME'),
					$lang->def('_DATE_BEGIN'),
					$lang->def('_DATE_END'),
					$lang->def('_COURSE_PRIZE'),
					$lang->def('_CONFIRM_COURSE'));

	$type_h = array('', '', '', '', '', '');

	$tb_product->setColsStyle($type_h);
	$tb_product->addHead($cont_h);

	$transaction_course = $man_transaction->getTransactionCourses($id_transaction);
	
	foreach($transaction_course as $id_course => $details)
	{
		if(is_array($details))
		{
			foreach($details['dates'] as $id_date)
			{
				$date_info = $date_man->getDateInfo($id_date);

				$checked = false;
				$other = '';

				if($man_transaction->controlActivation($id_transaction, $id_course, $id_date))
				{
					$checked = true;
					$other = 'disabled="disabled"';
				}
				

				$tb_product->addBody(array(	$date_info['code'],
											$date_info['name'],
											Format::date($date_info['date_begin']),
											Format::date($date_info['date_end']),
											$date_info['price'],
											Form::getInputCheckbox($id_course.'_'.$id_date, 'confirm['.$id_course.'_'.$id_date.']', 1, $checked, $other)));
			}
		}
		else
		{
			$course_info = $course_man->getCourseInfo($id_course);

			$checked = false;
			$other = '';

			if($man_transaction->controlActivation($id_transaction, $id_course))
			{
				$checked = true;
				$other = 'disabled="disabled"';
			}

			$tb_product->addBody(array(	$course_info['code'],
										$course_info['name'],
										($course_info['date_begin'] !== '0000-00-00' ? Format::date($course_info['date_begin'], 'date').($course_info['hour_begin'] !== '-1' ? $course_info['hour_begin'] : '') : ''),
										($course_info['date_end'] !== '0000-00-00' ? Format::date($course_info['date_end'], 'date').($course_info['hour_end'] !== '-1' ? $course_info['hour_end'] : '') : ''),
										($course_info['prize'] == '' ? '0' : $course_info['prize']),
										Form::getInputCheckbox($id_course.'_0', 'confirm['.$id_course.'_0]', 1, $checked, $other)));
		}
	}
	cout(	'<br />'
			.$tb_product->getTable()
			.Form::openButtonSpace()
			.Form::getButton('update', 'update', $lang->def('_UPDATE'))
			.Form::getButton('back_mod', 'back_mod', $lang->def('_BACK'))
			.Form::closeButtonSpace()
			.Form::closeForm());

	cout('</div>');
}

function transactionDispatch($op)
{
	checkPerm('view');
	require_once(_lms_.'/lib/lib.transaction.php');

	if(isset($_POST['back_mod']))
	Util::jump_to('index.php?modname=transaction&amp;op=transaction');

	switch($op)
	{
		case 'mod':
			modTransaction();
		break;
		case 'del':
			delTransaction();
		break;
		default:
		case 'transaction':
			transaction();
		break;
	}
}
?>