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

    protected $aggrCertsArr;

    public function __construct($id_user) {
        $this->id_user = (int) $id_user;
        $this->certificate = new Certificate();

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
                $download = '<a class="ico-wt-sprite subs_pdf" id="pdf_download" href="?r=mycertificate/'
                    . 'downloadCert&id_certificate=' . $cert['id_certificate'] . '&id_course=' . $cert['id_course'] . '" '
                    . ' title="' . (isset($cert['on_date'])?Lang::t('_DOWNLOAD', 'certificate'):Lang::t('_GENERATE', 'certificate')). '"><span>' 
                    . (isset($cert['on_date'])?Lang::t('_DOWNLOAD', 'certificate'):Lang::t('_GENERATE', 'certificate')) . '</span></a>';
                switch ($cert['available_for_status']) {
                    case 3:
                        $year = substr($cert['date_end'], 0, 10);
                        break;
                    case 2:
                        $year = substr($cert['date_begin'], 0, 10);
                        break;
                    case 1:
                        $year = substr($cert['date_inscr'], 0, 10);
                        break;
                    default:
                        $year = '-';
                }

                $row = array(
                    'year' => $year,
                    'code' => $cert['code'],
                    'course_name' => $cert['course_name'],
                    'cert_name' => $cert['cert_name'],
                    'on_date' => $cert['on_date'],
                    'download' => $download                    
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


    /**
    * In this funct. we need to select all the aggr. certs that has been released or not.
    * The cert. has been released -> there's an entry in the aggr. certs. assignment with the user and id cert.
    * 
    * From the user, get all assoc. -> from all assoc, get ids of cert. distinct
    * from the id cert., get all courses and see if they are completed
    * 
    * 
    * Return an array of all certs available
    */
    
    public function getMyMetaCertificates() {
        
        $q = "SELECT %lms_certificate.id_certificate, %lms_aggregated_cert_assign.idAssociation, %lms_certificate.code,  %lms_certificate.name, 
              DATE_FORMAT(%lms_aggregated_cert_assign.on_date, '%Y/%m/%d') as 'on_date', %lms_aggregated_cert_assign.cert_file, '' as 'courses_name', %lms_coursepath.path_name 
              FROM %lms_certificate, %lms_aggregated_cert_assign, %lms_aggregated_cert_coursepath, %lms_coursepath
              WHERE %lms_certificate.id_certificate=%lms_aggregated_cert_assign.idCertificate
              AND %lms_aggregated_cert_assign.idAssociation=%lms_aggregated_cert_coursepath.idAssociation
              AND %lms_aggregated_cert_coursepath.idCoursePath = %lms_coursepath.id_path
              AND %lms_aggregated_cert_coursepath.idUser=".intval($this->id_user);
              
       $rs = sql_query($q);       
       $prev_idcert = 0;
       $ii = 0;
       while ($row = sql_fetch_assoc($rs)) {
       
           if ($prev_idcert != $row['id_certificate']) {
                $arrAggregatedCerts[$ii] = $row;
                $ii++;     
           } else {        
                $arrAggregatedCerts[$ii-1]['path_name'] = $arrAggregatedCerts[$ii-1]['path_name']." | ".$row['path_name'];
           }     
           $prev_idcert = $row['id_certificate'];
       }                         

           
       $q = "SELECT %lms_certificate.id_certificate, %lms_aggregated_cert_assign.idAssociation, %lms_certificate.code,  %lms_certificate.name, 
              DATE_FORMAT(%lms_aggregated_cert_assign.on_date, '%Y/%m/%d') as 'on_date', %lms_aggregated_cert_assign.cert_file, %lms_course.name as 'courses_name', '' as 'path_name'
              FROM %lms_certificate, %lms_aggregated_cert_assign, %lms_aggregated_cert_course, %lms_course
              WHERE %lms_certificate.id_certificate=%lms_aggregated_cert_assign.idCertificate
              AND %lms_aggregated_cert_assign.idAssociation=%lms_aggregated_cert_course.idAssociation
              AND %lms_aggregated_cert_course.idCourse = %lms_course.idCourse
              AND %lms_aggregated_cert_course.idUser=".intval($this->id_user);
       $rs = sql_query($q);       
       $prev_idcert = 0; 
       while ($row = sql_fetch_assoc($rs)) {
            if ($prev_idcert != $row['id_certificate']) {
                $arrAggregatedCerts[$ii] = $row;
                $ii++;     
            } else {
                $arrAggregatedCerts[$ii-1]['path_name'] = $arrAggregatedCerts[$ii-1]['courses_name']." | ".$row['courses_name'];
            }    
           
       }
       return $arrAggregatedCerts;

    } 
    

    // TODO: passare nella aggregated_certificate
    function countAggrCertsToRelease() {
        $r = sql_fetch_row(
                sql_query('SELECT count(*) as tot from %lms_aggregated_cert_assign where idUser ='.Docebo::user()->getIdSt(). ' AND cert_file is null')
                          );
        return $r[0];
    }
    
    function countMyMetaCertificates(){
        $r = sql_fetch_row(sql_query('SELECT count(*) as tot from %lms_aggregated_cert_assign where idUser ='.Docebo::user()->getIdSt()));
        return $r[0];
    }
    
    function countMyMetaCertsReleased(){
        $r = sql_fetch_row(
                sql_query('SELECT count(*) as tot from %lms_aggregated_cert_assign where idUser ='.Docebo::user()->getIdSt(). ' AND cert_file is not null')
                          );
        return $r[0];
    }

}

?>