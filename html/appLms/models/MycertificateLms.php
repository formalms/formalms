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
    
  /*  public function loadMyMetaCertificates() {


      require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

      $associationsUser = $this->aggCertLib->getIdsAssociationUser( $this->id_user);

     // $arrAssoc = array_unique( );
      $arrAssoc =  array();
      foreach ($associationsUser[COURSE] as $value){
          $arrAssoc[] = $value;
      }
        foreach ($associationsUser[COURSE_PATH] as $value){
            $arrAssoc[] = $value;
        }


      $arrIdsCert = array_unique($this->aggCertLib->getIdCertificate($arrAssoc));
        
       foreach($arrIdsCert as $id_cert) {
           
           $showAggrCert = true;
           
           $arrIdsAssoc = $this->aggCertLib->getIdAssociationsWithType($id_cert);
           
           foreach($arrIdsAssoc as $association){
           
               $courseIdsArr = array();

               $arrLinks = $this->aggCertLib->getAssociationLink($association["id"], $association["type"], $this->id_user);
               
               foreach($arrLinks as $idLink){
                 
                      if($association["type"] == COURSE_PATH){

                            require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
                            $cp_m = new CoursePath_Manager();
                            
                                $courseIdsFromPath = array_map('intval', $cp_m->getPathCourses($idLink));

                            foreach($courseIdsFromPath as $coursefrompath)
                                $courseIdsArr[] = $coursefrompath;



                     } else $courseIdsArr[] = $idLink;  
               }
               
               if($this->aggCertLib->getCountCoursesCompleted($courseIdsArr, $this->id_user) != count($courseIdsArr)) {
                    $showAggrCert = false;
                    break 1;
               } 
           }
           
           if ($showAggrCert) 
                    // User has completed all the courses in the assoc.
                    $arrCertUser[] = $id_cert;
                
       }
           $arrCertUser = array_unique($arrCertUser);

           $arrAggregatedCerts = array();
           
           $k = 0;
           foreach($this->aggCertLib->getMetadata($arrCertUser) as $cert){
            
                $arrAggregatedCerts[$k]["id_certificate"] = $cert["id_certificate"];
                $arrAggregatedCerts[$k]["code"] = $cert["code"];
                $arrAggregatedCerts[$k]["name"] = $cert["name"];
                $arrAggregatedCerts[$k]["released"] = $this->aggCertLib->hasUserAggCertsReleased($this->id_user, $cert["id_certificate"]);
               
               $k += 1;
           }
    
        return $arrAggregatedCerts;

    }  */
    
    public function loadMyMetaCertificates(){
        
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