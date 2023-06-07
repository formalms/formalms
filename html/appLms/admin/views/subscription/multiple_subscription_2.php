<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

    $title = ['index.php?r=' . $link_course . '/show' => Lang::t('_COURSE', 'course'),
                Lang::t('_MULTIPLE_SUBSCRIPTION', 'course'), ];

    echo getTitleArea($title)
            . '<div class="std_block">'
            . Form::openForm('course_selection_form', 'index.php?r=' . $link . '/multiplesubscription')
            . Form::getHidden('id_cat', 'id_cat', $id_cat)
            . Form::getHidden('step', 'step', '2')
            . Form::getHidden('user_selection', 'user_selection', $user_selection)
            . $course_selector->loadCourseSelector(true)
            . Form::openButtonSpace()
            . Form::getButton('back', 'back', Lang::t('_PREV', 'course'), false, 'onclick="event.preventDefault();location.href=\'index.php?r=adm/userselector/show&instance=multiplecoursesubscription\'"')
            . Form::getButton('next', 'next', Lang::t('_NEXT', 'course'))
            . Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>';
