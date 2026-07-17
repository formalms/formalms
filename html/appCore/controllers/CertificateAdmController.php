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
    public function init()
    {
        // Accept framework-level certificate permissions OR LMS-level course permissions.
        // Both deleteMass and downloadMass are called from the LMS course certificate list
        // (CourseAlmsController::list_certificate), so users with LMS course admin permissions
        // should be allowed access.
        if (!checkPerm('view', true, $this->_mvc_name, 'framework')
            && !checkPerm('view', true, 'course', 'lms')) {
            exit("You can't access");
        }
    }



    public function deleteMass()
    {

        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/','lib.certificate.php');
        require_once \FormaLms\lib\Forma::include(_base_ . '/lib/', 'lib.upload.php');

        $certificate = new Certificate();

        $ids = FormaLms\lib\Get::req('id', DOTY_NUMLIST, '');
        $idsExploded = array_filter(
            explode(',', $ids));
    
        if(count($idsExploded)) {
            $assignedCertificates = $certificate->fetchAssignedCertificates($idsExploded);
            foreach($assignedCertificates  as $assignedCertificate) {
                FormaLms\lib\Get::sett('pathcourse');
                $path = '/appLms/certificate/';
                $deletion_result = true;
                if (!empty($assignedCertificate['cert_file'])) {
                    $deletion_result = sl_unlink($path . $assignedCertificate['cert_file']);
                }
            }

            $deletion_result = $certificate->deleteAssignedCertificates(implode(',', $idsExploded));

            if ($deletion_result) {

                Events::trigger('lms.certificate_user.deleted', [
               
                    'certificate_info' => $assignedCertificates,
                ]);

                if(defined('IS_AJAX'))
                {
                    echo \FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode(['result' => $deletion_result],\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON);
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
            [$id_user, $id_certificate, $id_course] = explode('-', $certRow);

            $certificate = new Certificate();

            $report_info = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);

            $info_report = current($report_info);

            $file = $info_report[ASSIGN_CERT_FILE];
            if ($file === null) {
                continue;
            }

            $sendname = $info_report[ASSIGN_CERT_SENDNAME];
            $sendname = Certificate::sanitizeFilename($sendname);
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
