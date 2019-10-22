<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

class MycertificateLmsController extends LmsController
{

    const mod_name = 'mycertificate';

    protected $id_user;

    protected $json;
    protected $model;
    protected $certificate;

    public function init()
    {
        $this->id_user = Docebo::user()->getIdSt();
        $this->json = new Services_JSON();
        $this->model = new MycertificateLms($this->id_user);
        $this->certificate = new Certificate();
    }

    public function show()
    {
        checkPerm('view', false, self::mod_name);

        $this->render('show');
    }

    public function getMyCertificates()
    {
        checkPerm('view', false, self::mod_name);

        $startIndex = Get::req('start', DOTY_INT, 0);
        $results = Get::req('results', DOTY_INT, Get::sett('visuItem', 10));
        $rowsPerPage = Get::req('length', DOTY_INT, $results);
        $sort = Get::req('sort', DOTY_MIXED, 'year');
        $dir = Get::req('dir', DOTY_STRING, "asc");

        $pagination = array(
            'startIndex' => $startIndex,
            'rowsPerPage' => $rowsPerPage,
            'results' => $results,
            'sort' => $sort,
            'dir' => $dir
        );

        if ($search = $_REQUEST['search']) {
            $pagination['search'] = $search['value'];
        } else {
            $pagination['search'] = null;
        }
        
        //Return total of all assignment / certs. available for the user filtered only by user id
        $totalCertificates = $this->model->countMyCertificates();
        
        // returns all the certs. datas with the pagination option and count to false (I.E. )
        // return the rows for the rendering in the view
        $certificates = $this->model->loadMyCertificates($pagination, false);
        
        // return the number of certs released and not
        $total_filtered = $this->model->loadMyCertificates($pagination, true);

        $result = array(
            'recordsTotal' => $totalCertificates,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $rowsPerPage,
            'recordsFiltered' => $total_filtered,
            'data' => $certificates,
        );

        echo $this->json->encode($result);
    }

    public function getMyMetaCertificates() {
        
        checkPerm('view', false, self::mod_name);

        $startIndex = Get::req('start', DOTY_INT, 0);
        $results = Get::req('results', DOTY_INT, Get::sett('visuItem', 10));
        $rowsPerPage = Get::req('length', DOTY_INT, $results);

        $pagination = array(
            'startIndex' => $startIndex,
            'rowsPerPage' => $rowsPerPage,
            'results' => $results,
        );

      /*  if ($search = $_REQUEST['search']) {
            $pagination['search'] = $search['value'];
        } else {
            $pagination['search'] = null;
        }
        */

        // query for the relation between user and certificates associated to it
        // all the assoc. with the user, for all the user get id cert.
        $totalMetaCertificates = $this->model->countMyMetaCertificates();
        
      //  $metaCertificates = $this->model->loadMyMetaCertificates($pagination);
        $metaCertificates = $this->getAggrCertsForView($pagination);

       // $metaCertificatesFiltered = $this->model->loadMyMetaCertificates($pagination, true);

        $result = array(
            'recordsTotal' => $totalMetaCertificates,
            'startIndex' => $startIndex,
            'rowsPerPage' => $rowsPerPage,
          //  'recordsFiltered' => $metaCertificatesFiltered,
            'data' => $metaCertificates,
        );

        echo $this->json->encode($result);
    }


    /**
     * Expect array of array
     *  each inner array contains all the rows of the table.
     *
     * @param $pagination
     *
     * @return array
     */
    function getAggrCertsForView($pagination){

        if($pagination) {
            $paginated_assignment = array();

            $offset = $pagination["startIndex"];
            $limit = $offset + $pagination["rowsPerPage"];
            $limit = ($limit <= $this->model->countMyMetaCertificates() ? $limit : $this->model->countMyMetaCertificates());
            for($i = $offset; $i < $limit; $i++) {

                $meta = $this->model->getAggregatedCerts()[$i];

                $preview    = '<a class="ico-wt-sprite subs_view"'
                    . ' href="?r=mycertificate/preview'
                    . '&id_certificate='.$meta['id_certificate']
                    . '&id_meta='.$meta['id_meta'].'" '
                    . ' title="'.Lang::t('_PREVIEW', 'certificate').'"><span>'.Lang::t('_PREVIEW', 'certificate').'</span></a>';
                $download   = '<a class="ico-wt-sprite subs_pdf"'
                    . ' href="?r=mycertificate/download'
                    . '&id_certificate='.$meta['id_certificate'].'&id_meta='.$meta['id_meta'].'" '
                    .' title="'.Lang::t('_DOWNLOAD', 'certificate').'"><span>'.Lang::t('_DOWNLOAD', 'certificate').'</span></a>';
                $generate    = '<a class="ico-wt-sprite subs_pdf" href="?r=mycertificate/'
                    . 'release_cert'
                    .'&id_certificate='.$meta['id_certificate']
                    .'&aggCert=1'
                    .'&id_meta='.$meta['id_meta'].'" '
                    .' title="'.Lang::t('_GENERATE', 'certificate').'"><span>'.Lang::t('_GENERATE', 'certificate').'</span></a>';

                $row = array(
                    $meta['cert_code'],
                    $meta['cert_name'],
                    $meta['courses'],
                    // 'preview'           => isset($meta['on_date']) ? '' : $preview,
                    ($meta['isReleased']) ? $download : $generate
                );

                $paginated_assignment[] = $row;
                // $paginated_assignment[] = $this->model->getAggregatedCerts[$i];

            }

            return $paginated_assignment;

        }


 /*       $data_to_display = array();
        for ($i = $pagination['startIndex']; $i < ($pagination['startIndex'] + $pagination['results']) && $i < count($data); $i++)
            $data_to_display[] = $data[$i];




        return $data_to_display;*/
    }



    public function preview()
    {
        checkPerm('view', false, self::mod_name);

        $id_certificate = importVar('id_certificate', true, 0);
        $id_course = importVar('id_course', true, 0);
        $idAssociation = Get::req('idAssociation', DOTY_INT, 0);

        $subs = $this->certificate->getSubstitutionArray($this->id_user, $id_course, $idAssociation);
        $this->certificate->send_facsimile_certificate($id_certificate, $this->id_user, $id_course, $subs);
    }
    
    function release_cert() {
            checkPerm('view');

            require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

            
            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_association = Get::req('id_association', DOTY_INT, 0);

            $id_course =  Get::req('id_course', true, 0);
            $id_user = (int) $this->id_user;


            
            $subs = $this->certificate->getSubstitutionArray($id_user, $id_course, $id_association);
            $this->certificate->send_certificate($id_certificate, $id_user, $id_course, $subs);
        }

    public function download()
    {
        checkPerm('view', false, self::mod_name);

        $id_certificate = importVar('id_certificate', true, 0);
        $id_course = importVar('id_course', true, 0);
        $idAssociation = Get::req('idAssociation', DOTY_INT, 0);

        if($this->certificate->certificateAvailableForUser($id_certificate, $id_course, $this->id_user) ) {
            $subs = $this->certificate->getSubstitutionArray($this->id_user, $id_course, $idAssociation);
            $this->certificate->send_certificate($id_certificate, $this->id_user, $id_course, $subs);
        }
    }



}

