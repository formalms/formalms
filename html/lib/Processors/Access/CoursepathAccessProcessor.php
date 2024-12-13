<?php

namespace FormaLms\lib\Processors\Access;

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

class CoursepathAccessProcessor extends AccessProcessor
{

    public const NAME = 'coursepath';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        $url = 'index.php?r=alms/subscription/show_coursepath&id_path=' . (int) $resourceId;
        $flag = $this->accessModel->setAccessList($resourceId, $selection);
        //2 - check if there are any editions or classrooms
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.course.php');
        require_once _lms_ . '/lib/lib.coursepath.php';
        $path_man = new \CoursePath_Manager();
        $course_man = new \Man_Course();
        $courses = $path_man->getAllCourses([$resourceId]);
        $classroom = $course_man->getAllCourses(false, 'classroom', $courses);
        $edition = $course_man->getAllCourses(false, 'edition', $courses);
        if(!$flag){
            $this->setRedirect($url);
        } else {
            //3 - if yes, then make a second step in order to choose editions and classrooms
            if (!empty($classroom) || !empty($edition)) {
                $classroom_list = [];
                if (!empty($classroom)) {
                    require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.date.php');
                    $date_man = new \DateManager();

                    foreach ($classroom as $id_course => $info) {
                        $classrooms = $date_man->getCourseDate($id_course, true);

                        $classrooms_for_dropdown = [];
                        $classrooms_for_dropdown[0] = \Lang::t('_NO_CLASSROOM_SUBSCRIPTION', 'coursepath');

                        foreach ($classrooms as $classroom_info) {
                            $classrooms_for_dropdown[$classroom_info['id_date']] = $classroom_info['code'] . ' - ' . $classroom_info['name']
                                . ' - ' . \Format::date($classroom_info['date_begin'], 'date') . ' - ' . \Format::date($classroom_info['date_end'], 'date');
                        }

                        $classroom_list[] = [
                            'id_course' => $id_course,
                            'label' => $info['name'],
                            'list' => $classrooms_for_dropdown,
                        ];
                        //cout(Form::getDropdown(Lang::t('_EDITION_SELECTION', 'coursepath').' : '.$info['code'].' - '.$info['name'], 'classroom_'.$id_course, 'classroom_'.$id_course, $edition_for_dropdown));
                    }
                }

                    $edition_list = [];
                    if (!empty($edition)) {
                        require_once _lms_ . '/lib/lib.edition.php';
                        $edition_man = new \EditionManager();

                        foreach ($edition as $id_course => $info) {
                            $editions = $edition_man->getEditionsInfoByCourses($id_course);

                            $editions_for_dropdown = [];
                            $editions_for_dropdown[0] = \Lang::t('_NONE', 'coursepath');

                            foreach ($editions[$id_course] as $edition_info) {
                                $editions_for_dropdown[$edition_info['id_edition']] = $edition_info['code'] . ' - ' . $edition_info['name']
                                    . ' - ' . \Format::date($edition_info['date_begin'], 'date') . ' - ' . \Format::date($edition_info['date_end'], 'date');
                            }

                            $edition_list[] = [
                                'id_course' => $id_course,
                                'label' => $info['name'],
                                'list' => $edition_for_dropdown,
                            ];
                            //cout(Form::getDropdown(Lang::t('_EDITION_SELECTION', 'coursepath').' : '.$info['code'].' - '.$info['name'], 'edition_'.$id_course, 'edition_'.$id_course, $edition_for_dropdown));
                        }
                    }

                    $this->setReturnType('render');
                    $this->setReturnView('choose_editions_coursepath');
                    $this->setSubFolderView('subscription');
                    $this->setAdditionalPaths([_lms_.'/admin/views']);
           

                    $this->setParams([
                        'id_path' => $resourceId,
                        'courses_list' => $courses,
                        'editions_list' => $edition_list,
                        'classrooms_list' => $classroom_list,
                        'users_to_add' => $this->accessModel->getAccessUsers()['to_add'],
                        'users_to_del' => $this->accessModel->getAccessUsers()['to_del'],
                        'path_name' => $this->accessModel->getCoursepathNameForSubscription($resourceId),
                    ]);
            } else {
                $this->setRedirect($url . '&res=' . $this->accessModel->getResponseForAccessor());
       
            }
        }   
            
        return $this;
        
    }
}