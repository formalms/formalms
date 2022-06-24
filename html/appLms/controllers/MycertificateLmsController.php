<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once Forma::inc(_lms_ . '/lib/lib.certificate.php');

class MycertificateLmsController extends LmsController
{
    public const mod_name = 'mycertificate';

    protected $id_user;

    /** @var Services_JSON */
    protected $json;

    /** @var MycertificateLms */
    protected $model;
    /** @var Certificate */
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

        //TODO: EVT_OBJECT (§)
        //$event = new \appLms\Events\Lms\MyCertificateTabLoading();
        //TODO: EVT_LAUNCH (&)
        //\appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\MyCertificateTabLoading::EVENT_NAME, $event);

        $metaCertificates = $this->model->getMyMetaCertificates();
        $totalMetaCertificates = count($metaCertificates);
        $this->render('show', ['metacertificates' => $this->json->encode($metaCertificates), 'totalMetaCertificates' => $totalMetaCertificates, 'id_user' => $this->id_user]);

        /*
                //NEW Event Method
                $tabs = array();
                $eventTabs = Events::trigger('lms.mycertificatetab.loading', [
                    'tabs' => $tabs,
                ]);
                $additionalTabs = $eventTabs['tabs'];
                $this->render('show', [
                    'totalMetaCertificates' => $totalMetaCertificates,
                    'additionalTabs' => $additionalTabs
                ]);
        */
    }

    public function getMyCertificates()
    {
        checkPerm('view', false, self::mod_name);

        $startIndex = FormaLms\lib\Get::req('start', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 10));
        $rowsPerPage = FormaLms\lib\Get::req('length', DOTY_INT, $results);
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'year');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $pagination = [
            'startIndex' => $startIndex,
            'rowsPerPage' => $rowsPerPage,
            'results' => $results,
            'sort' => $sort,
            'dir' => $dir,
        ];

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

        $result = [
            'recordsTotal' => $totalCertificates,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $rowsPerPage,
            'recordsFiltered' => $total_filtered,
            'data' => $certificates,
        ];

        echo $this->json->encode($result);
    }

    public function downloadCert()
    {
        checkPerm('view', false, self::mod_name);

        $id_certificate = importVar('id_certificate', true, 0);
        $id_course = importVar('id_course', true, 0);
        $idAssociation = importVar('idAssociation', true, 0);

        if ($this->certificate->certificateAvailableForUser($id_certificate, $id_course, $this->id_user)) {
            $subs = $this->certificate->getSubstitutionArray($this->id_user, $id_course, $idAssociation);
            $this->certificate->send_certificate($id_certificate, $this->id_user, $id_course, $subs);
        }
    }

    public function downloadMetaCert()
    {
        $id_certificate = importVar('id_certificate', true, 0);
        $id_association = importVar('id_association', true, 0);
        $id_user = $this->id_user;

        $subs = $this->certificate->getSubstitutionArray($id_user, $id_course, $id_association);
        $rs = $this->certificate->send_certificate($id_certificate, $id_user, 0, $subs, true, false, $id_association);
    }
}
