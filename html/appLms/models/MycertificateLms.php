<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

class MycertificateLms extends Model {

    protected $certificate;
    
    public $id_user;

    public function __construct($id_user) {
        $this->certificate = new Certificate();
        $this->id_user = $id_user;
    }
    
    public function loadMyCertificates($pagination = false, $count = false) {
        $startIndex = Get::req('startIndex', DOTY_INT, 0);
        $results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
        $sort = Get::req('sort', DOTY_MIXED, 'year');
        $dir = Get::req('dir', DOTY_MIXED, 'desc');
        
        $filter = array('id_user' => $this->id_user);
        $myCertificates = $this->certificate->getAssignment($filter, $pagination, $count);

        if ($count) {
            return $myCertificates;
        }
                
        $data = array();
        foreach ($myCertificates AS $cert) {
            if($this->certificate->certificateAvailableForUser($cert['id_certificate'], $cert['id_course'], $this->id_user) ) {
                $preview = '<a class="ico-wt-sprite subs_view" href="?r=mycertificate/'
                    . 'preview&id_certificate=' . $cert['id_certificate'] . '&id_course=' . $cert['id_course'] . '" '
                    . ' title="' . Lang::t('_PREVIEW', 'certificate') . '"><span>' . Lang::t('_PREVIEW', 'certificate') . '</span></a>';
                $download = '<a class="ico-wt-sprite subs_pdf" href="?r=mycertificate/'
                    . 'download&id_certificate=' . $cert['id_certificate'] . '&id_course=' . $cert['id_course'] . '" '
                    . ' title="' . Lang::t('_DOWNLOAD', 'certificate') . '"><span>' . Lang::t('_DOWNLOAD', 'certificate') . '</span></a>';
                $generate = '<a class="ico-wt-sprite subs_pdf" href="?r=mycertificate/'
                    . 'download&id_certificate=' . $cert['id_certificate'] . '&id_course=' . $cert['id_course'] . '" '
                    . ' title="' . Lang::t('_GENERATE', 'certificate') . '"><span>' . Lang::t('_GENERATE', 'certificate') . '</span></a>';

                switch ($cert['available_for_status']) {
                    case 3:
                        $year = substr($cert['date_end'], 0, 4);
                        break;
                    case 2:
                        $year = substr($cert['date_begin'], 0, 4);
                        break;
                    case 1:
                        $year = substr($cert['date_inscr'], 0, 4);
                        break;
                    default:
                        $year = '-';
                }

                $row = array(
                    'year' => $year,
                    'code' => $cert['code'],
                    'course_name' => $cert['course_name'],
                    'cert_name' => $cert['cert_name'],
                    'date_complete' => $cert['date_complete'],
                    // 'preview' => isset($cert['on_date']) ? '' : $preview,
                    'download' => isset($cert['on_date']) ? $download : $generate,
                    'on_date' => $cert['on_date'],
                );

                $data[] = $row;
            }
        }

        if ($order = $_REQUEST['order']) {
            $sort_index = $order[0]['column'];

            $fields = array(
                'year',
                'code',
                'course_name',
                'cert_name',
                'date_complete',
                'preview',
                'download',
            );

            $sort = $fields[$sort_index];
            $dir = $order[0]['dir'];
        }

        usort($data, function($a, $b) use ($sort, $dir) {
            return $dir == 'desc' ? strcmp($b[$sort], $a[$sort]) : strcmp($a[$sort], $b[$sort]);
        });
        
        $data_to_display = array();
        for ($i = $startIndex; $i < ($startIndex + $results) && $i < count($data); $i++){
            $data_to_display[] = array_values($data[$i]);
        }
        
        return $data_to_display;
    }
    
    public function countMyCertificates() {        
        $filter = array('id_user' => $this->id_user);
        return $this->certificate->countAssignment($filter);
    }
    
    public function loadMyMetaCertificates() {
        $startIndex = Get::req('startIndex', DOTY_INT, 0);
        $results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
        
        $filter = array('id_user' => $this->id_user);
        $myMetaCertificates = $this->certificate->getMetaAssignment($filter);
                
        $data = array();
        foreach ($myMetaCertificates AS $meta) {
            $preview    = '<a class="ico-wt-sprite subs_view" href="?r=mycertificate/'
                        . 'preview&id_certificate='.$meta['id_certificate'].'&id_meta='.$meta['id_meta'].'" '
                        .' title="'.Lang::t('_PREVIEW', 'certificate').'"><span>'.Lang::t('_PREVIEW', 'certificate').'</span></a>';
            $download    = '<a class="ico-wt-sprite subs_pdf" href="?r=mycertificate/'
                        . 'download&id_certificate='.$meta['id_certificate'].'&id_meta='.$meta['id_meta'].'" '
                        .' title="'.Lang::t('_DOWNLOAD', 'certificate').'"><span>'.Lang::t('_DOWNLOAD', 'certificate').'</span></a>';
            $generate    = '<a class="ico-wt-sprite subs_pdf" href="?r=mycertificate/'
                        . 'download&id_certificate='.$meta['id_certificate'].'&id_meta='.$meta['id_meta'].'" '
                        .' title="'.Lang::t('_GENERATE', 'certificate').'"><span>'.Lang::t('_GENERATE', 'certificate').'</span></a>';
					
            $row = array(
                'cert_code'         => $meta['cert_code'], 
                'cert_name'         => $meta['cert_name'], 
                'courses'           => $meta['courses'],
                // 'preview'           => isset($meta['on_date']) ? '' : $preview,
                'download'          => isset($meta['on_date']) ? $download : $generate
            );
            
            $data[] = array_values($row);
        }
        
        $data_to_display = array();
        for ($i = $startIndex; $i < ($startIndex + $results) && $i < count($data); $i++){
            $data_to_display[] = $data[$i];
        }
        
        return $data_to_display;
    }
    
    public function countMyMetaCertificates() {        
        $filter = array('id_user' => $this->id_user);
        return $this->certificate->countMetaAssignment($filter);
    }
}

?>