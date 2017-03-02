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

function courseAutoregistration()
{
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$form = new Form();

	$out->add(getTitleArea(Lang::t('_AUTOREGISTRATION', 'course_autoregistration'))
				.'<div class="std_block">');

	$out->add(	$form->openForm('course_autoregistration', 'index.php?modname=course_autoregistration&amp;op=course_autoregistration')
				.$form->openElementSpace()
				.$form->getTextfield(Lang::t('_COURSE_AUTOREGISTRATION_CODE', 'course_autoregistration'), 'course_autoregistration_code', 'course_autoregistration_code', '255', '')
				.$form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('subscribe', 'subscribe', Lang::t('_SEND', 'course_autoregistration'))
				.$form->closeButtonSpace());

	$out->add('</div>');
}


function subscribe()
{
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');

	$form = new Form();
	if (isset($_POST['course_autoregistration_code']))
	{
		require_once(_lms_.'/lib/lib.course.php');

		$out =& $GLOBALS['page'];
		$out->setWorkingZone('content');

		$code = $_POST['course_autoregistration_code'];
		$code = strtoupper($code);
		$code = str_replace('-', '', $code);
		
		$registration_code_type = Get::sett('registration_code_type', '0');
		if($registration_code_type == 'tree_course') $code = substr($code, 10, 10);
		
		$course_registration_result = false;

		$man_course_user = new Man_CourseUser();

		$course_registration_result = $man_course_user->subscribeUserWithCode($code, getLogUserId());

		$out->add(getTitleArea(Lang::t('_AUTOREGISTRATION', 'course_autoregistration'))
				.'<div class="std_block">');
		if($course_registration_result > 0)
		{
			$out->add(str_replace('[course_added]', $course_registration_result, Lang::t('_OPERATION_SUCCESSFUL', 'course_autoregistration')));

			$out->add('<br/><a href="index.php?r='._lms_home_.'">'.Lang::t('_BACK_TO_COURSE', 'course_autoregistration').'</a>');
		}
		else
		{
			if($course_registration_result == -1)
			{
				$out->add(getErrorUi(Lang::t('_CODE_ALREDY_USED', 'course_autoregistration')));

				$out->add(	$form->openForm('course_autoregistration', 'index.php?modname=course_autoregistration&amp;op=course_autoregistration')
							.$form->openElementSpace()
							.$form->getTextfield(Lang::t('_COURSE_AUTOREGISTRATION_CODE', 'course_autoregistration'), 'course_autoregistration_code', 'course_autoregistration_code', '255', '')
							.$form->closeElementSpace()
							.$form->openButtonSpace()
							.$form->getButton('subscribe', 'subscribe', Lang::t('_SEND', 'course_autoregistration'))
							.$form->closeButtonSpace());
			}else if($course_registration_result == -2)
			{
				$out->add(getErrorUi(Lang::t('_SUBSCRIPTION_NOT_ALLOWED_YET', 'course_autoregistration')));

				$out->add(	$form->openForm('course_autoregistration', 'index.php?modname=course_autoregistration&amp;op=course_autoregistration')
							.$form->openElementSpace()
							.$form->getTextfield(Lang::t('_COURSE_AUTOREGISTRATION_CODE', 'course_autoregistration'), 'course_autoregistration_code', 'course_autoregistration_code', '255', '')
							.$form->closeElementSpace()
							.$form->openButtonSpace()
							.$form->getButton('subscribe', 'subscribe', Lang::t('_SEND', 'course_autoregistration'))
							.$form->closeButtonSpace());
			}
			else
			{
				require_once(_adm_.'/lib/lib.code.php');
				$code = $_POST['course_autoregistration_code'];

				$code_manager = new CodeManager();

				$valid_code = $code_manager->controlCodeValidity($code);

				if($valid_code == 1)
				{
					$array_course = $code_manager->getAvailableCourseAssociateWithCode($code);

					require_once(_lms_.'/lib/lib.course.php');
					$man_course = new Man_Course();

					$array_course_name = array();
					$counter = 0;

					require_once(_lms_.'/lib/lib.subscribe.php');
					$subscribe = new CourseSubscribe_Management();

					foreach ($array_course as $id_course)
					{
						$query_control =	"SELECT COUNT(*)"
											." FROM %lms_courseuser"
											." WHERE idCourse = ".$id_course
											." AND idUser = ".getLogUserId();

						list($control) = sql_fetch_row(sql_query($query_control));

						if($control == 0)
						{
							$subscribe->subscribeUser(getLogUserId(), $id_course, '3');

							$course_info = $man_course->getCourseInfo($id_course);

							$array_course_name[$counter] = $course_info['name'];

							$counter++;
						}
					}

					if($counter > 0)
					{
						$code_manager->setCodeUsed($code, getLogUserId());

						$courses = implode(', ', $array_course_name);

						if(count($array_course_name) > 1)
							$out->add(str_replace('[course_added]', $courses, Lang::t('_REGISTRATION_SUCCESSFUL_TO_M', 'course_autoregistration')));
						else
							$out->add(str_replace('[course_added]', $courses, Lang::t('_REGISTRATION_SUCCESSFUL_TO', 'course_autoregistration')));

						$out->add('<br/><a href="index.php?r='._lms_home_.'">'.Lang::t('_BACK_TO_COURSE', 'course_autoregistration').'</a>');
					}
					else
					{
						if (sizeof($array_course)==0) {
							$out->add(getErrorUi(Lang::t('_SUBSCRIPTION_NOT_ALLOWED_YET', 'course_autoregistration')));
						} else {
							$out->add(getErrorUi(Lang::t('_ALREADY_SUBSCRIBED', 'course_autoregistration')));
						}

						$out->add(	$form->openForm('course_autoregistration', 'index.php?modname=course_autoregistration&amp;op=course_autoregistration')
									.$form->openElementSpace()
									.$form->getTextfield(Lang::t('_COURSE_AUTOREGISTRATION_CODE', 'course_autoregistration'), 'course_autoregistration_code', 'course_autoregistration_code', '255', '')
									.$form->closeElementSpace()
									.$form->openButtonSpace()
									.$form->getButton('subscribe', 'subscribe', Lang::t('_SEND', 'course_autoregistration'))
									.$form->closeButtonSpace());
					}


				}
				else
				{
					if($valid_code == 0)
						$out->add(getErrorUi(Lang::t('_CODE_ALREDY_USED', 'course_autoregistration')));
					else
						$out->add(getErrorUi(Lang::t('_CODE_NOT_VALID', 'course_autoregistration')));

					$out->add(	$form->openForm('course_autoregistration', 'index.php?modname=course_autoregistration&amp;op=course_autoregistration')
								.$form->openElementSpace()
								.$form->getTextfield(Lang::t('_COURSE_AUTOREGISTRATION_CODE', 'course_autoregistration'), 'course_autoregistration_code', 'course_autoregistration_code', '255', '')
								.$form->closeElementSpace()
								.$form->openButtonSpace()
								.$form->getButton('subscribe', 'subscribe', Lang::t('_SEND', 'course_autoregistration'))
								.$form->closeButtonSpace());
				}
			}
		}

		$out->add('</div>');
	}
}

function courseAutoregistrationDispatch($op)
{
	if (isset($_POST['subscribe']))
		$op = 'subscribe';
	switch ($op)
	{
		case 'course_autoregistration':
			courseAutoregistration();
		break;
		case 'subscribe':
			subscribe();
		break;

		default:
			courseAutoregistration();
		break;
	}
}
?>
