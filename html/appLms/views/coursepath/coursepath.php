<?php
	require_once(_lms_.'/lib/lib.stats.php');

	$first = true;
	foreach($user_coursepath as $id_path => $path_info)
	{
		echo '<div class="coursepath_container">'
				.'<h2>'.$path_info['path_name'].'</h2>'
				.'<div class="yui-ge">'
				.'<div class="yui-u first coursepath_description"><div class="textof">'
				.$path_info['path_descr']
				.'</div></div>'
				.'<div class="yui-u">'
					.'<div class="yui-ge percentage_cont">'
					.'<div class="yui-u first">'
					.Lang::t('_COMPLETED', 'standard').':<br /><br />'
					.renderCoursepathProgress($path_info['course_completed'], $path_info['coursepath_courses'])
					.'</div>'
					.'<div class="yui-u">'
					.'<br /><br /> <span class="coursepath_percentage">'.$path_info['percentage'].' %</span>'
					.'</div>'
					.'</div>'
				.'</div>'
				.'</div>'

				.'<div id="courses_link_'.$type.'_'.$id_path.'" class="coursepath_action">'
				.'<a class="no_decoration" href="javascript:;" onclick="expandCourses(\''.$id_path.'\',\''.$type.'\');">'
				.'<span class="expand_path_info">'.Lang::t('_EXPAND', 'coursepath').'</span> '
				.Get::img('course/expand.png', Lang::t('_EXPAND', 'course'))
				.'</a>'
				.'</div>';

		foreach($coursepath_courses[$id_path] as $id_course => $course_info)
		{
			//Control coursepath prerequisite
			$query =	"SELECT cc.prerequisites"
						." FROM %lms_coursepath_courses AS cc"
						." JOIN %lms_coursepath_user AS cu ON cc.id_path = cu.id_path"
						." WHERE cu.idUser = ".(int)Docebo::user()->getIdSt()
						." AND cc.id_item = ".(int)$id_course;

			$result = sql_query($query);

			$unlock = true;

			while(list($prerequisites) = sql_fetch_row($result))
				if($prerequisites !== '')
				{
					$num_prerequisites = count(explode(',', $prerequisites));

					$query =	"SELECT COUNT(*)"
								." FROM %lms_courseuser"
								." WHERE idCourse IN (".$prerequisites.") AND idUser = ".(int)Docebo::user()->getIdst()." "
								." AND status = "._CUS_END;

					list($control) = sql_fetch_row(sql_query($query));

					if($control < $num_prerequisites)
						$unlock = false;
				}

				if($course_info['status'] != _CUS_END && $unlock)
				{
					$query_control_info =	"SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.difficult, c.status AS course_status, c.course_edition, "
											."	c.max_num_subscribe, c.create_date, "
											."	c.direct_play, c.img_othermaterial, c.course_demo, c.use_logo_in_courselist, c.img_course, c.lang_code, "
											."	c.course_vote, "
											."	c.date_begin, c.date_end, c.valid_time, c.show_result, c.userStatusOp,"

											."	cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting"

											." FROM %lms_course AS c "
											." JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) "
											." WHERE c.idCourse = ".$id_course
											." AND idUser = ".(int)Docebo::user()->getIdst();

					$accesso_control_info = sql_fetch_assoc(sql_query($query_control_info));

					$course_access = Man_Course::canEnterCourse($accesso_control_info);

					echo	'<div class="coursepath_action"><span class="expand_path_info">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;'.Lang::t('_CURRENT_ACTIVITY', 'coursepath').' : </span>'
							.($course_access['can'] ? '<a class="std_link" href="index.php?modname=course&amp;op=aula&amp;idCourse='.$course_info['idCourse'].'">' : '')
							.$course_info['name']
							.($course_access['can'] ? '</a>' : '')
							.'</div>';
					break;
				}
		}

		reset($coursepath_courses[$id_path]);

		echo '<div id="courses_'.$id_path.'" style="display:none;clear:both;" class="coursepath_details">';

		echo '<table>';

		foreach($coursepath_courses[$id_path] as $id_course => $course_info)
		{
			//Control coursepath prerequisite
			$query =	"SELECT cc.prerequisites"
						." FROM %lms_coursepath_courses AS cc"
						." JOIN %lms_coursepath_user AS cu ON cc.id_path = cu.id_path"
						." WHERE cu.idUser = ".(int)Docebo::user()->getIdSt()
						." AND cc.id_item = ".(int)$id_course
						." AND cc.id_path = ".(int)$id_path;

			$result = sql_query($query);

			$unlock = true;



			while(list($prerequisites) = sql_fetch_row($result))
				if($prerequisites !== '')
				{
					$num_prerequisites = count(explode(',', $prerequisites));

					$query =	"SELECT COUNT(*)"
								." FROM %lms_courseuser"
								." WHERE idCourse IN (".$prerequisites.") AND idUser = ".(int)Docebo::user()->getIdst()." "
								." AND status = "._CUS_END;

					list($control) = sql_fetch_row(sql_query($query));

					if($control < $num_prerequisites)
						$unlock = false;
				}

			if($course_info['status'] == _CUS_END)
			{
				$ico_style = 'subs_actv';
				$ico_text = '_COURSE_COMPLETED';
			}
			elseif(!$unlock)
			{
				$ico_style = 'subs_locked';
				$ico_text = '_COURSE_LOCKED';
			}
			else
			{
				$ico_style = 'subs_noac';
				$ico_text = '_COURSE_ACTIVE';
			}

			$query_control_info =	"SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.difficult, c.status AS course_status, c.course_edition, "
									."	c.max_num_subscribe, c.create_date, "
									."	c.direct_play, c.img_othermaterial, c.course_demo, c.use_logo_in_courselist, c.img_course, c.lang_code, "
									."	c.course_vote, "
									."	c.date_begin, c.date_end, c.valid_time, c.show_result, c.userStatusOp,"

									."	cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting"

									." FROM %lms_course AS c "
									." JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) "
									." WHERE c.idCourse = ".$id_course;

			$accesso_control_info = sql_fetch_assoc(sql_query($query_control_info));
            

            $type_course = ($course_info['course_type']=='elearning'? Lang::t('_COURSE_TYPE_ELEARNING', 'course'): Lang::t('_CLASSROOM_COURSE', 'cart'));
			$course_access = Man_Course::canEnterCourse($accesso_control_info, $id_path);
			echo	'<tr>'
					.'<td><span class="ico-sprite '.$ico_style.'"><span>'.Lang::t($ico_text, 'coursepath').'</span></span></td>'
					.'<td class="course_type">'.$type_course.'</td>'
					.'<td>'.($course_access['can'] ? '<a class="std_link" href="index.php?modname=course&amp;op=aula&amp;idCourse='.$course_info['idCourse'].'">' : '').$course_info['name'].($course_access['can'] ? '</a>' : '').'</td>'
					.'</tr>';
		}

		echo	'</table>'
				.'</div>'
				.'<div class="nofloat"></div>'
				.'</div>';

		if ($first) {
			$first = false;
		} else {
			echo '<br />';
		}
	}
?>