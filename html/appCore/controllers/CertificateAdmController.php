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

defined('IN_FORMA') or exit('Direct access is forbidden');

class CertificateAdmController extends AdmController
{

    protected $json;

    public function init()
    {
        parent::init();
        require_once \FormaLms\lib\Forma::include(_base_ . '/lib/','lib.json.php');
        $this->json = new Services_JSON();
   
   
    }


    public function delete()
    {

        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/','lib.certificate.php');
        require_once \FormaLms\lib\Forma::include(_base_ . '/lib/', 'lib.upload.php');

        $certificate = new Certificate();

        $id_certificate = FormaLms\lib\Get::req('certificate_id', true, 0);
        $id_course = FormaLms\lib\Get::req('course_id', true, 0);
        $id_user = FormaLms\lib\Get::req('user_id', true, 0);
        $from = FormaLms\lib\Get::req('from', DOTY_MIXED, '');

        $certificate_info = [];
        $certificate_info = $certificate->getCertificateInfo($id_certificate);

        $c_infos = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
        $certificate_info = current($c_infos);

        FormaLms\lib\Get::sett('pathcourse');
        $path = '/appLms/certificate/';
        $deletion_result = true;
        if ($certificate_info[CERT_NAME] != '') {
            $deletion_result = sl_unlink($path . $certificate_info[ASSIGN_CERT_FILE]);
        }

        if ($deletion_result) {
            $deletion_result = $certificate->delCertificateForUserInCourse($id_certificate, $id_user, $id_course);
            if ($from == 'courselist') {
                $id_certificate = 0;
            }
            if ($deletion_result) {

                Events::trigger('lms.certificate_user.deleted', [
                    'id_certificate' => $certificate_info[CERT_ID],
                    'certificate_info' => $certificate_info,
                    'id_user' => $id_user,
                    'id_course' => $id_course
                ]);

                if(defined('IS_AJAX'))
                {
                    echo $this->json->encode(['result' => $deletion_result]);
                    exit;
                }

                Util::jump_to('index.php?r=alms/course/list_certificate&id_certificate=' . $id_certificate . '&id_course=' . $id_course . '&from=' . $from . '&deletion=1');
            } else { // to improve
                exit('ERROR ON CERTIFICATE DELETION');
            }
        } else { // to improve
            exit('ERROR ON CERTIFICATE DELETION');
        }
    }


    public function deleteMass()
    {

        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/','lib.certificate.php');
        require_once \FormaLms\lib\Forma::include(_base_ . '/lib/', 'lib.upload.php');

        $certificate = new Certificate();

        $id_certificates = FormaLms\lib\Get::req('certificate_id', DOTY_STRING, '');
        $id_course = FormaLms\lib\Get::req('course_id', true, 0);
        $id_users = FormaLms\lib\Get::req('user_id', DOTY_STRING, '');
    

        $certificate_info = [];

        foreach(explode(',', $id_certificates) as $id_certificate) {

            $certificate_info = $certificate->getCertificateInfo($id_certificate);

            foreach(explode(',',$id_users) as $id_user) {
                $c_infos = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
                $certificate_info = current($c_infos);
        
                FormaLms\lib\Get::sett('pathcourse');
                $path = '/appLms/certificate/';
                $deletion_result = true;
                if ($certificate_info[CERT_NAME] != '') {
                    $deletion_result = sl_unlink($path . $certificate_info[ASSIGN_CERT_FILE]);
                }
            }
        }
        

        if ($deletion_result && !empty($id_users) && !empty($id_certificates)) {
            $deletion_result = $certificate->delCertificateForUsersInCourse($id_certificates, $id_users, $id_course);
            if ($from == 'courselist') {
                $id_certificate = 0;
            }
            if ($deletion_result) {

                Events::trigger('lms.certificate_user.deleted', [
                    'id_certificate' => $certificate_info[CERT_ID],
                    'certificate_info' => $certificate_info,
                    'id_user' => $id_user,
                    'id_course' => $id_course
                ]);

                if(defined('IS_AJAX'))
                {
                    echo $this->json->encode(['result' => $deletion_result]);
                    exit;
                }

                Util::jump_to('index.php?r=alms/course/list_certificate&id_certificate=' . $id_certificate . '&id_course=' . $id_course . '&from=' . $from . '&deletion=1');
            } else { // to improve
                exit('ERROR ON CERTIFICATE DELETION');
            }
        } else { // to improve
            exit('ERROR ON CERTIFICATE DELETION');
        }
    }

    function downloadMass()
    {
        require_once _base_ . '/' . _folder_lms_ . '/lib/lib.certificate.php';
        require_once _base_ . '/lib/lib.download.php';

        $str_rows =  FormaLms\lib\Get::req('str_rows', DOTY_STRING, '');

        $files = [];
        $zipName = date('YmdHis') . '_certs.zip';

        $filePath = _files_ . '/tmp/' . $zipName;

        $zip = new PclZip($filePath);

        $list_cert = explode(',', $str_rows);

        foreach ($list_cert as $certRow) {
            list($id_user, $id_certificate, $id_course) = explode('-', $certRow);

            $certificate = new Certificate();

            $report_info = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);

            $info_report = current($report_info);

            $file = $info_report[ASSIGN_CERT_FILE];
            if ($file === null) {
                continue;
            }

            $sendname = $info_report[ASSIGN_CERT_SENDNAME];
            $copyResult = copy(_files_ . '/appLms/certificate/' . $file, _files_ . '/tmp/' . $sendname);
            if ($copyResult) {
                $files[] = _files_ . '/tmp/' . $sendname;
            }
        }
        $zip->create($files, '', _files_ . '/tmp/');
        foreach ($files as $fileToRemove) {
            unlink($fileToRemove);
        }
        sendFile(_files_ . '/tmp/', $zipName);
        unlink($filePath);
    }



}
