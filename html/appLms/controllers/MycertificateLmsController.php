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
        $totalMetaCertificates = $this->model->countMyMetaCertificates();

        $this->render('show', ['totalMetaCertificates' => $totalMetaCertificates]);
    }

    public function getMyCertificates()
    {
        checkPerm('view', false, self::mod_name);

        $startIndex = Get::req('startIndex', DOTY_INT, 0);
        $results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
        $sort = Get::req('sort', DOTY_MIXED, 'year');
        $dir = Get::req('dir', DOTY_MIXED, 'desc');

        $totalCertificates = $this->model->countMyCertificates();
        $certificates = $this->model->loadMyCertificates();

        $result = array(
            'totalRecords' => $totalCertificates,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($certificates),
            'records' => $certificates
        );

        echo $this->json->encode($result);
    }

    public function getMyMetaCertificates()
    {
        checkPerm('view', false, self::mod_name);

        $startIndex = Get::req('startIndex', DOTY_INT, 0);
        $results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));

        $totalMetaCertificates = $this->model->countMyMetaCertificates();
        $metaCertificates = $this->model->loadMyMetaCertificates();

        $result = array(
            'totalRecords' => $totalMetaCertificates,
            'startIndex' => $startIndex,
            'rowsPerPage' => $results,
            'results' => count($metaCertificates),
            'records' => $metaCertificates
        );

        echo $this->json->encode($result);
    }

    public function preview()
    {
        checkPerm('view', false, self::mod_name);

        $id_certificate = importVar('id_certificate', true, 0);
        $id_course = importVar('id_course', true, 0);
        $id_meta = Get::req('idmeta', DOTY_INT, 0);

        $subs = $this->certificate->getSubstitutionArray($this->id_user, $id_course, $id_meta);
        $this->certificate->send_facsimile_certificate($id_certificate, $this->id_user, $id_course, $subs);
    }

    public function download()
    {
        checkPerm('view', false, self::mod_name);

        $id_certificate = importVar('id_certificate', true, 0);
        $id_course = importVar('id_course', true, 0);
        $id_meta = Get::req('id_meta', DOTY_INT, 0);

        if($this->certificate->certificateAvailableForUser($id_certificate, $id_course, $this->id_user) ) {
            $subs = $this->certificate->getSubstitutionArray($this->id_user, $id_course, $id_meta);
            $this->certificate->send_certificate($id_certificate, $this->id_user, $id_course, $subs);
        }
    }
}

?>